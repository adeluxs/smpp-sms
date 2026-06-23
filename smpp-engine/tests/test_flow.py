import asyncio
import json
import aio_pika

async def test_message_flow():
    connection = await aio_pika.connect_robust("amqp://smpp:smpp_password@localhost:5672/smpp")
    channel = await connection.channel()

    await channel.default_exchange.publish(
        aio_pika.Message(
            body=json.dumps({
                "message_id": "test-123",
                "tenant_id": "00000000-0000-0000-0000-000000000001",
                "source": "TEST",
                "destination": "+15551234567",
                "content": "Hello from SMPP test",
                "encoding": "GSM7",
            }).encode(),
            delivery_mode=aio_pika.DeliveryMode.PERSISTENT,
        ),
        routing_key="smpp.submit"
    )

    print("✓ Test message published to smpp.submit queue")
    await connection.close()

if __name__ == "__main__":
    asyncio.run(test_message_flow())