import asyncio
import structlog
from typing import Optional

from smpplv.client import SmppClient

from .config import settings
from .metrics import PROVIDER_RECONNECTS

logger = structlog.get_logger()


class UpstreamProvider:
    def __init__(self, provider_data: dict):
        self.id = provider_data["id"]
        self.host = provider_data["host"]
        self.port = provider_data["port"]
        self.system_id = provider_data["system_id"]
        self.password = provider_data["password"]
        self._client: Optional[SmppClient] = None
        self._connected = False
        self._reconnect_task: Optional[asyncio.Task] = None

    async def connect(self):
        try:
            self._client = SmppClient()
            await self._client.connect(self.host, self.port)
            await self._client.bind_transmitter(
                system_id=self.system_id,
                password=self.password
            )
            self._connected = True
            logger.info("Upstream provider connected", provider_id=self.id)
        except Exception as e:
            self._connected = False
            PROVIDER_RECONNECTS.inc()
            logger.error("Provider connection failed", provider_id=self.id, error=str(e))

    async def send(self, destination: str, content: str, encoding: str = "GSM7") -> str:
        if not self._connected or not self._client:
            raise RuntimeError("Provider not connected")

        try:
            response = await self._client.send_message(
                destination=destination,
                content=content,
                encoding=encoding
            )
            return response.message_id
        except Exception as e:
            self._connected = False
            logger.error("Send failed", provider_id=self.id, error=str(e))
            raise

    async def close(self):
        if self._client:
            await self._client.close()
            self._connected = False


class ProviderManager:
    def __init__(self):
        self._providers: dict[str, UpstreamProvider] = {}

    async def get_provider(self, provider_id: str) -> Optional[UpstreamProvider]:
        if provider_id not in self._providers:
            from .client_repository import ClientRepository
            repo = ClientRepository()
            p = await repo.get_provider(provider_id)
            if p:
                self._providers[provider_id] = UpstreamProvider(p)
                await self._providers[provider_id].connect()

        return self._providers.get(provider_id)

    async def close_all(self):
        for provider in self._providers.values():
            await provider.close()