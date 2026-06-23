import asyncio
import json
import aio_pika
from smpplv.server import SmppServer

RABBITMQ_URL = "amqp://smpp:smpp_password@rabbitmq:5672/smpp"

async def main():
    connection = await aio_pika.connect_robust(RABBITMQ_URL)
    channel = await connection.channel()
    exchange = channel.get_exchange("smpp.dlr.exchange")

    server = SmppServer(lambda s: handle_session(s, exchange))
    await server.listen("0.0.0.0", 2775)
    print("Mock SMPP provider listening on 2775")
    try:
        await asyncio.Future()
    except asyncio.CancelledError:
        await server.close()
        await connection.close()

async def handle_session(session, exchange):
    async for request in session:
        if request.command == "bind_transceiver":
            session.send_bind_transceiver_resp()
        elif request.command == "submit_sm":
            message_id = f"mock_{session.system_id}_{int(asyncio.get_event_loop().time())}"
            session.send_submit_sm_resp(message_id=message_id)

            await asyncio.sleep(0.1)

            await exchange.publish(
                aio_pika.Message(
                    body=json.dumps({
                        "message_id": request.source,
                        "provider_message_id": message_id,
                        "status": "delivered",
                        "received_at": "2024-01-01T00:00:00Z"
                    }).encode()
                ),
                routing_key="smpp.dlr"
            )

if __name__ == "__main__":
    asyncio.run(main())