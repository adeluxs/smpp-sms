import aiohttp
import structlog
from typing import Optional, Literal

logger = structlog.get_logger()
ProviderType = Literal["smpp", "http"]


class HttpProvider:
    def __init__(self, provider_data: dict):
        self.id = provider_data["id"]
        self.name = provider_data["name"]
        self.api_url = provider_data["api_url"]
        self.api_key = provider_data["api_key"]
        self._session: Optional[aiohttp.ClientSession] = None

    async def connect(self):
        self._session = aiohttp.ClientSession()
        logger.info("HTTP provider ready", provider_id=self.id)

    async def send(self, destination: str, content: str, encoding: str = "GSM7") -> str:
        if not self._session:
            raise RuntimeError("Provider not connected")

        headers = {"Authorization": f"Bearer {self.api_key}"}
        payload = {
            "to": destination,
            "message": content,
            "from": encoding,
        }

        async with self._session.post(self.api_url, json=payload, headers=headers) as resp:
            if resp.status == 200:
                data = await resp.json()
                return data.get("message_id", data.get("id"))
            else:
                error = await resp.text()
                logger.error("HTTP provider error", status=resp.status, error=error)
                raise Exception(f"Provider returned {resp.status}")

    async def close(self):
        if self._session:
            await self._session.close()


class HybridProviderConnector:
    def __init__(self):
        self._http_providers: dict[str, HttpProvider] = {}

    async def register_http(self, provider_data: dict):
        provider = HttpProvider(provider_data)
        self._http_providers[provider.id] = provider

    async def close_all(self):
        for provider in self._http_providers.values():
            await provider.close()