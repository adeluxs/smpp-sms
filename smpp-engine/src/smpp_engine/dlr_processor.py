import structlog
import aio_pika
import json
from typing import Optional
from enum import Enum
import httpx

from .config import settings
from .metrics import MESSAGES_DLR

logger = structlog.get_logger()


class DLRStatus(Enum):
    DELIVERED = "delivered"
    FAILED = "failed"
    EXPIRED = "expired"
    UNDELIVERED = "undelivered"


class DLRProcessor:
    def __init__(self):
        self.connection: Optional[aio_pika.RobustConnection] = None
        self.channel: Optional[aio_pika.RobustChannel] = None

    async def start(self):
        self.connection = await aio_pika.connect_robust(
            host=settings.rabbitmq_host,
            port=settings.rabbitmq_port,
            login=settings.rabbitmq_user,
            password=settings.rabbitmq_password,
            virtualhost=settings.rabbitmq_vhost,
        )
        self.channel = await self.connection.channel()

        queue = await self.channel.declare_queue("smpp.dlr", durable=True)
        await queue.bind("smpp.dlr.exchange")
        await queue.consume(self._on_dlr, no_ack=False)

        logger.info("DLR processor started")

    async def _on_dlr(self, message: aio_pika.IncomingMessage):
        async with message.process():
            try:
                data = json.loads(message.body.decode())
                await self._process_dlr(data)
                MESSAGES_DLR.inc()
            except Exception as e:
                logger.error("Failed to process DLR", error=str(e))

    async def _process_dlr(self, data: dict):
        provider_message_id = data.get("provider_message_id")
        status = DLRStatus(data.get("status", "undelivered"))

        await self._update_message_status(provider_message_id, status)

        if callback_url := data.get("callback_url"):
            await self._notify_client(callback_url, data)

        logger.info("DLR processed",
            message_id=data.get("message_id"),
            status=status.value
        )

    async def _update_message_status(self, provider_message_id: str, status: DLRStatus):
        async with httpx.AsyncClient() as client:
            await client.post(
                f"http://laravel/internal/api/v1/dlr/update",
                json={
                    "provider_message_id": provider_message_id,
                    "status": status.value,
                },
                timeout=5.0
            )

    async def _notify_client(self, callback_url: str, data: dict):
        async with httpx.AsyncClient() as client:
            try:
                await client.post(callback_url, json=data, timeout=5.0)
            except Exception as e:
                logger.warning("Callback failed", url=callback_url, error=str(e))

    async def shutdown(self):
        if self.connection:
            await self.connection.close()