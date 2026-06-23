import asyncio
import aio_pika
import structlog
from typing import Optional

from .config import settings
from .upstream_provider import ProviderManager

logger = structlog.get_logger()


class ProviderConnector:
    def __init__(self):
        self._connection: Optional[aio_pika.RobustConnection] = None
        self._channel: Optional[aio_pika.RobustChannel] = None
        self._provider_manager = ProviderManager()

    async def connect(self):
        self._connection = await aio_pika.connect_robust(
            host=settings.rabbitmq_host,
            port=settings.rabbitmq_port,
            login=settings.rabbitmq_user,
            password=settings.rabbitmq_password,
            virtualhost=settings.rabbitmq_vhost,
        )
        self._channel = await self._connection.channel()

        await self._channel.declare_queue("smpp.submit", durable=True)
        await self._channel.declare_queue("smpp.dlr", durable=True)
        await self._channel.declare_exchange("smpp.provider", aio_pika.ExchangeType.TOPIC, durable=True)

        logger.info("Provider connector initialized")

    async def send_message(self, message_data: dict) -> str:
        provider = await self._select_provider(message_data)
        upstream = await self._provider_manager.get_provider(provider)

        if upstream and upstream._connected:
            message_id = await upstream.send(
                message_data["destination"],
                message_data["content"],
                message_data.get("encoding", "GSM7")
            )
            return message_id

        return await self._send_via_queue(message_data, provider)

    async def _select_provider(self, message_data: dict) -> str:
        destination = message_data.get("destination", "")
        prefix = destination[:3] if destination else "default"

        if self._channel:
            route = await self._lookup_route(prefix)
            return route["provider_id"] if route else "default"

        return message_data.get("route_id", "default")

    async def _lookup_route(self, prefix: str) -> Optional[dict]:
        exchange = self._channel.get_exchange("smpp.provider")
        # Route lookup would query database in production
        return None

    async def _send_via_queue(self, message_data: dict, provider_id: str) -> str:
        exchange = self._channel.get_exchange("smpp.provider")
        await exchange.publish(
            aio_pika.Message(
                body=message_data["content"].encode(),
                headers={
                    "message_id": message_data["message_id"],
                    "destination": message_data["destination"],
                    "provider_id": provider_id,
                    "encoding": message_data.get("encoding", "GSM7"),
                }
            ),
            routing_key=f"provider.{provider_id}"
        )
        return message_data["message_id"]

    async def close(self):
        if self._connection:
            await self._connection.close()
        await self._provider_manager.close_all()