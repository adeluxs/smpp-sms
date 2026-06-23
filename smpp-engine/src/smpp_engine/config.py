from pydantic_settings import BaseSettings
from pydantic import Field


class Settings(BaseSettings):
    rabbitmq_host: str = Field(default="localhost", alias="RABBITMQ_HOST")
    rabbitmq_port: int = Field(default=5672, alias="RABBITMQ_PORT")
    rabbitmq_user: str = Field(default="smpp", alias="RABBITMQ_USER")
    rabbitmq_password: str = Field(default="", alias="RABBITMQ_PASSWORD")
    rabbitmq_vhost: str = Field(default="/smpp", alias="RABBITMQ_VHOST")

    redis_host: str = Field(default="localhost", alias="REDIS_HOST")
    redis_port: int = Field(default=6379, alias="REDIS_PORT")

    postgresql_host: str = Field(default="localhost", alias="POSTGRESQL_HOST")
    postgresql_port: int = Field(default=5432, alias="POSTGRESQL_PORT")
    postgresql_db: str = Field(default="smpp_platform", alias="POSTGRESQL_DB")
    postgresql_user: str = Field(default="smpp", alias="POSTGRESQL_USER")
    postgresql_password: str = Field(default="", alias="POSTGRESQL_PASSWORD")

    smpp_bind_host: str = Field(default="0.0.0.0", alias="SMpp_BIND_HOST")
    smpp_bind_port: int = Field(default=2775, alias="SMpp_BIND_PORT")
    
    secret_key: str = Field(default="", alias="SECRET_KEY")

    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"


settings = Settings()