import json
import structlog
import aio_pika
from typing import Optional

from .config import settings
from .metrics import QUEUE_DEPTH, MESSAGES_SENT
from .validator import MessageValidator, SubmitMessage
from .provider_connector import ProviderConnector
from .idempotency import IdempotencyGuard

logger = structlog.get_logger()


class MessageHandler:
    def __init__(self):
        self.connection: Optional[aio_pika.RobustConnection] = None
        self.channel: Optional[aio_pika.RobustChannel] = None
        self.provider_connector = ProviderConnector()
        self.idempotency = IdempotencyGuard()

    async def start_consumer(self):
        self.connection = await aio_pika.connect_robust(
            host=settings.rabbitmq_host,
            port=settings.rabbitmq_port,
            login=settings.rabbitmq_user,
            password=settings.rabbitmq_password,
            virtualhost=settings.rabbitmq_vhost,
        )
        self.channel = await self.connection.channel()

        queue = await self.channel.declare_queue("smpp.submit", durable=True)
        await queue.bind("smpp.submit.exchange")

        await queue.consume(self._on_message, no_ack=False)
        logger.info("MessageHandler consumer started")

    async def _on_message(self, message: aio_pika.IncomingMessage):
        async with message.process():
            try:
                data = json.loads(message.body.decode())
                await self._process_message(data)
                MESSAGES_SENT.inc()
            except Exception as e:
                logger.error("Failed to process message", error=str(e))

    async def _process_message(self, data: dict):
        if not await self.idempotency.check_and_set(data["message_id"]):
            logger.warning("Duplicate message ignored", message_id=data["message_id"])
            return

        validated = self._validate(data)
        if not validated:
            return

        segments = MessageValidator.calculate_segments(validated.content, validated.encoding)

        await self.provider_connector.send_message({
            **data,
            "encoding": validated.encoding,
            "segments": segments,
        })

        logger.info("Message sent to provider", message_id=data["message_id"])

    def _validate(self, data: dict) -> bool:
        if not MessageValidator.validate_destination(data.get("destination", "")):
            logger.warning("Invalid destination", dest=data.get("destination"))
            return False

        if not MessageValidator.validate_content(data.get("content", "")):
            logger.warning("Invalid content length")
            return False

        return True

    async def shutdown(self):
        if self.connection:
            await self.connection.close()
        await self.provider_connector.close()
        
        # Publish dead letter
        async def publish_dlq(self, message_id: str, reason: str):
            exchange = self.channel.get_exchange("dlq.exchange")
            await exchange.publish(
                aio_pika.Message(
                    body=json.dumps({"message_id": message_id, "reason": reason}).encode()
                ),
                routing_key="dlq.failed"
            )