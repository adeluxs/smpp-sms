import pytest
import asyncio
from unittest.mock import AsyncMock, MagicMock

from smpp_engine.session_manager import SessionManager
from smpp_engine.validator import MessageValidator


class TestSessionManager:
    @pytest.fixture
    def session_manager(self):
        sm = SessionManager()
        sm.clients = MagicMock()
        return sm

    @pytest.mark.asyncio
    async def test_authenticate_success(self, session_manager):
        session_manager.clients.find = AsyncMock(return_value={"status": "active"})
        session_manager.clients.verify_password = AsyncMock(return_value=True)

        session = MagicMock()
        session.system_id = "test"
        session.password = "pass"

        result = await session_manager.authenticate(session)
        assert result is True

    @pytest.mark.asyncio
    async def test_authenticate_inactive(self, session_manager):
        session_manager.clients.find = AsyncMock(return_value={"status": "suspended"})

        session = MagicMock()
        session.system_id = "test"

        result = await session_manager.authenticate(session)
        assert result is False


class TestMessageValidator:
    def test_destination_valid(self):
        assert MessageValidator.validate_destination("+15551234567") is True
        assert MessageValidator.validate_destination("15551234567") is True

    def test_destination_invalid(self):
        assert MessageValidator.validate_destination("abc") is False
        assert MessageValidator.validate_destination("") is False

    def test_encoding_gsm7(self):
        content = "Hello World 123"
        assert MessageValidator.detect_encoding(content) == "GSM7"

    def test_encoding_ucs2(self):
        content = "こんにちは"
        assert MessageValidator.detect_encoding(content) == "UCS2"

    def test_segments_single(self):
        assert MessageValidator.calculate_segments("Hi", "GSM7") == 1

    def test_segments_multi(self):
        long_msg = "A" * 200
        assert MessageValidator.calculate_segments(long_msg, "GSM7") == 2