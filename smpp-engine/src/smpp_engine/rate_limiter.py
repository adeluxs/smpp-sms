import time
import aioredis
from .config import settings


class TokenBucketRateLimiter:
    def __init__(self):
        self._redis = None

    async def _get_redis(self):
        if self._redis is None:
            self._redis = aioredis.from_url(
                f"redis://{settings.redis_host}:{settings.redis_port}"
            )
        return self._redis

    async def is_allowed(self, key: str, max_tokens: int, refill_per_second: float) -> bool:
        redis = await self._get_redis()
        now = int(time.time())
        window_key = f"{key}:{now % 10}"

        lua_script = """
        local current = redis.call('GET', KEYS[1])
        if current == false then
            redis.call('SETEX', KEYS[1], ARGV[3], ARGV[2])
            return ARGV[2]
        end
        if tonumber(current) < tonumber(ARGV[1]) then
            redis.call('INCR', KEYS[1])
            return 1
        end
        return 0
        """

        result = await redis.eval(lua_script, keys=[window_key], args=[max_tokens, 1, 10])
        return bool(result)

    async def consume(self, key: str, tokens: int = 1) -> bool:
        redis = await self._get_redis()
        lua_script = """
        local current = redis.call('GET', KEYS[1])
        if tonumber(current or 0) >= tonumber(ARGV[1]) then
            redis.call('DECRBY', KEYS[1], ARGV[1])
            return 1
        end
        return 0
        """
        result = await redis.eval(lua_script, keys=[key], args=[tokens])
        return bool(result)