import asyncio
import structlog
from typing import Optional

from smpplv.server import SmppServer, SmppSession

from .session_manager import SessionManager
from .message_handler import MessageHandler

logger = structlog.get_logger()


class SMPPServer:
    def __init__(self):
        self.server: Optional[SmppServer] = None
        self.session_manager = SessionManager()
        self.message_handler = MessageHandler()
        self._running = False

    async def start(self):
        self.server = SmppServer(self.session_manager.handle_session)
        await self.server.listen("0.0.0.0", 2775)
        self._running = True
        logger.info("SMPP server listening on 2775")

        await self.message_handler.start_consumer()

    async def shutdown(self):
        self._running = False
        if self.server:
            await self.server.close()
        await self.message_handler.shutdown()
        logger.info("SMPP server shutdown complete")