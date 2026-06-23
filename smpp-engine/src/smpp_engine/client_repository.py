import aioredis
import asyncpg
from typing import Optional

from .config import settings


class ClientRepository:
    def __init__(self):
        self._redis: Optional[aioredis.Redis] = None
        self._pool: Optional[asyncpg.Pool] = None

    async def _get_redis(self) -> aioredis.Redis:
        if self._redis is None:
            self._redis = aioredis.from_url(
                f"redis://{settings.redis_host}:{settings.redis_port}"
            )
        return self._redis

    async def _get_pool(self) -> asyncpg.Pool:
        if self._pool is None:
            self._pool = await asyncpg.create_pool(
                host=settings.postgresql_host,
                port=settings.postgresql_port,
                database=settings.postgresql_db,
                user=settings.postgresql_user,
                password=settings.postgresql_password,
                min_size=5,
                max_size=20,
            )
        return self._pool

    async def find(self, system_id: str) -> Optional[dict]:
        pool = await self._get_pool()
        async with pool.acquire() as conn:
            row = await conn.fetchrow(
                "SELECT * FROM smpp_clients WHERE system_id = $1 AND status = 'active'",
                system_id
            )
            return dict(row) if row else None

    async def verify_password(self, system_id: str, password: str) -> bool:
        pool = await self._get_pool()
        async with pool.acquire() as conn:
            row = await conn.fetchrow(
                "SELECT password_hash FROM smpp_clients WHERE system_id = $1",
                system_id
            )
            if not row:
                return False
            import hashlib
            return row["password_hash"] == hashlib.sha256(password.encode()).hexdigest()

    async def check_rate_limit(self, key: str) -> bool:
        redis = await self._get_redis()
        current = await redis.incr(key)
        if current == 1:
            await redis.expire(key, 1)
        return current <= 100

    async def get_provider(self, provider_id: str) -> Optional[dict]:
        pool = await self._get_pool()
        async with pool.acquire() as conn:
            row = await conn.fetchrow(
                "SELECT id, system_id, password_encrypted, host, port FROM providers WHERE id = $1 AND status = 'active'",
                provider_id
            )
            if row:
                from cryptography.fernet import Fernet
                f = Fernet(settings.secret_key.encode())
                row["password"] = f.decrypt(row["password_encrypted"].encode()).decode()
            return dict(row) if row else None