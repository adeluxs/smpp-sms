import aio_pika
import structlog

from .metrics import QUEUE_DEPTH, BIND_ATTEMPTS, SUBMIT_LATENCY


class MetricsCollector:
    def __init__(self, connection: aio_pika.RobustConnection | None = None):
        self._connection = connection

    async def update_queue_depth(self, queue_name: str):
        pass

    async def record_bind_attempt(self, success: bool):
        BIND_ATTEMPTS.labels(result='success' if success else 'failure').inc()

    def record_submit_latency(self, seconds: float):
        SUBMIT_LATENCY.observe(seconds)