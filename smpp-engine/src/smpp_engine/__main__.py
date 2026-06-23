import asyncio
import signal
import structlog

from .server import SMPPServer
from .config import settings
from .dlr_processor import DLRProcessor
from .observability import MetricsCollector

logger = structlog.get_logger()


async def main():
    logger.info("Starting SMPP Engine", port=settings.bind_port)

    metrics = MetricsCollector()
    await metrics.start()

    dlr_processor = DLRProcessor()
    await dlr_processor.start()

    server = SMPPServer()
    await server.start()

    loop = asyncio.get_running_loop()
    stop_event = asyncio.Event()

    def signal_handler():
        logger.info("Shutdown signal received")
        stop_event.set()

    for sig in (signal.SIGTERM, signal.SIGINT):
        loop.add_signal_handler(sig, signal_handler)

    try:
        await stop_event.wait()
    finally:
        await server.shutdown()
        await dlr_processor.shutdown()

if __name__ == "__main__":
    structlog.configure(
        processors=[
            structlog.processors.TimeStamper(fmt="iso"),
            structlog.processors.add_log_level,
            structlog.processors.JSONRenderer()
        ]
    )
    asyncio.run(main())