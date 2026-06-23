import structlog
import time
import ipaddress
from typing import Optional
from smpplv import CommandStatus
from smpplv.server import SmppSession

from .client_repository import ClientRepository
from .metrics import ACTIVE_SESSIONS
from .rate_limiter import TokenBucketRateLimiter

logger = structlog.get_logger()


class SessionManager:
    def __init__(self):
        self.clients = ClientRepository()
        self.rate_limiter = TokenBucketRateLimiter()
        self._sessions: dict[str, SmppSession] = {}

    async def handle_session(self, session: SmppSession, client_ip: str):
        system_id = session.system_id

        if not await self.authenticate(session):
            session.send_bind_transceiver_resp(CommandStatus.ESME_RINVSRCADR)
            logger.warning("Authentication failed", system_id=system_id)
            return

        if not await self.check_ip_allowlist(system_id, client_ip):
            session.send_bind_transceiver_resp(CommandStatus.ESME_RINVSRCADR)
            logger.warning("IP not allowed", system_id=system_id, ip=client_ip)
            return

        if not await self.check_bind_attempts(system_id):
            session.send_bind_transceiver_resp(CommandStatus.ESME_RTHROTTLED)
            logger.warning("Bind rate limited", system_id=system_id)
            return

        self._sessions[system_id] = session
        ACTIVE_SESSIONS.inc()

        logger.info("SMPP session bound", system_id=system_id, ip=client_ip)

        try:
            await self._handle_message_loop(session)
        finally:
            self._sessions.pop(system_id, None)
            ACTIVE_SESSIONS.dec()
            logger.info("SMPP session closed", system_id=system_id)

    async def authenticate(self, session: SmppSession) -> bool:
        system_id = session.system_id
        password = session.password

        client = await self.clients.find(system_id)
        if not client:
            return False

        if client.status != "active":
            return False

        return await self.clients.verify_password(system_id, password)

    async def check_ip_allowlist(self, system_id: str, client_ip: str) -> bool:
        client = await self.clients.find(system_id)
        if not client or not client.get("ip_allowlist"):
            return True

        for allowed_ip in client["ip_allowlist"]:
            try:
                network = ipaddress.ip_network(allowed_ip, strict=False)
                if ipaddress.ip_address(client_ip) in network:
                    return True
            except ValueError:
                if allowed_ip == client_ip:
                    return True
        return False

    async def check_bind_attempts(self, system_id: str) -> bool:
        key = f"smpp:bind:{system_id}"
        return await self.rate_limiter.is_allowed(key, max_tokens=10, refill_per_second=1)

    async def _handle_message_loop(self, session: SmppSession):
        async for request in session:
            if request.command == "submit_sm":
                await self._handle_submit_sm(session, request)
            elif request.command == "enquire_link":
                session.send_enquire_link_resp()

    async def _handle_submit_sm(self, session: SmppSession, request):
        system_id = session.system_id
        if not await self.rate_limiter.is_allowed(
            f"smpp:submit:{system_id}",
            max_tokens=100,
            refill_per_second=10
        ):
            session.send_submit_sm_resp(CommandStatus.ESME_RTHROTTLED)
            return

        logger.info("Message submitted", system_id=system_id)