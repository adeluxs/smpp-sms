import structlog
import asyncio
from enum import Enum
from datetime import datetime, timedelta

logger = structlog.get_logger()

class RetryPolicy:
    def __init__(self, max_attempts: int = 3):
        self.max_attempts = max_attempts

    async def should_retry(self, attempt: int, error: Exception) -> bool:
        return attempt < self.max_attempts

    def get_delay(self, attempt: int) -> float:
        return min(60, 2 ** attempt)


class DeadLetterHandler:
    async def handle(self, message_id: str, reason: str, data: dict):
        logger.error("Message moved to dead-letter queue",
            message_id=message_id,
            reason=reason
        )

    async def requeue_after_delay(self, message_id: str, delay: int):
        logger.info("Requeue scheduled", message_id=message_id, delay=delay)