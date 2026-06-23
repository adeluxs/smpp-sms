import pytest
import asyncio
from smpp_engine.rate_limiter import TokenBucketRateLimiter


@pytest.mark.asyncio
async def test_rate_limiter_allows_initial():
    limiter = TokenBucketRateLimiter()
    allowed = await limiter.is_allowed("test:key", max_tokens=10, refill_per_second=1)
    assert allowed is True


@pytest.mark.asyncio
async def test_rate_limiter_blocks_over_limit():
    limiter = TokenBucketRateLimiter()
    for _ in range(15):
        await limiter.is_allowed("test:key2", max_tokens=10, refill_per_second=1)

    allowed = await limiter.is_allowed("test:key2", max_tokens=10, refill_per_second=1)
    assert allowed is False


@pytest.mark.asyncio
async def test_ip_allowlist_check():
    from smpp_engine.session_manager import SessionManager
    sm = SessionManager()

    assert await sm.check_ip_allowlist("test", "192.168.1.1") is True


@pytest.mark.asyncio
async def test_bind_attempt_limiting():
    limiter = TokenBucketRateLimiter()

    for _ in range(15):
        await limiter.is_allowed("bind:test", max_tokens=10, refill_per_second=1)

    allowed = await limiter.is_allowed("bind:test", max_tokens=10, refill_per_second=1)
    assert allowed is False