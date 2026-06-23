import aioredis
from .config import settings

class IdempotencyGuard:
    def __init__(self):
        self._redis = None

    async def _get_redis(self):
        if self._redis is None:
            self._redis = aioredis.from_url(
                f"redis://{settings.redis_host}:{settings.redis_port}"
            )
        return self._redis

    async def check_and_set(self, message_id: str) -> bool:
        redis = await self._get_redis()
        key = f"smpp:idempotency:{message_id}"
        exists = await redis.exists(key)
        if not exists:
            await redis.setex(key, 86400, "1")
            return True
        return False

    async def get(self, message_id: str) -> bool:
        redis = await self._get_redis()
        key = f"smpp:idempotency:{message_id}"
        return await redis.exists(key) > 0