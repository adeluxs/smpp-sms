from pydantic import BaseModel, field_validator
import re


class MessageValidator:
    GSM7_CHARS = set(
        "@£$¥èéùìòç\nØø\rÅåΔ_ΦφΓγΛλΩωΠπΣσΤτΥυΖζÆæØøÅå¤¤"
        "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
        "0123456789 .,+()-*/='\":?!#$%&*;<=>@¿¡"
    )

    @classmethod
    def validate_destination(cls, destination: str) -> bool:
        if not re.match(r'^\+?[1-9]\d{1,14}$', destination):
            return False
        return len(destination) <= 20

    @classmethod
    def detect_encoding(cls, content: str) -> str:
        for char in content:
            if char not in cls.GSM7_CHARS:
                return "UCS2"
        return "GSM7"

    @classmethod
    def validate_content(cls, content: str) -> bool:
        return 1 <= len(content) <= 65535

    @classmethod
    def calculate_segments(cls, content: str, encoding: str) -> int:
        char_limit = 153 if encoding == "UCS2" else 153
        return max(1, (len(content) + char_limit - 1) // char_limit if len(content) > 160 else 1)


class SubmitMessage(BaseModel):
    message_id: str
    tenant_id: str
    source: str | None = None
    destination: str
    content: str
    encoding: str = "GSM7"
    segments: int = 1
    priority: int = 100