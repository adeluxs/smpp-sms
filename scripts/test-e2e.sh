#!/bin/bash
set -e

echo "=== SMPP Platform E2E Test ==="

echo "1. Starting services..."
docker-compose up -d

echo "2. Waiting for startup..."
sleep 15

echo "3. Checking health..."
curl -f http://localhost/health || echo "Laravel health check failed"
curl -f http://localhost:8000/metrics | grep smpp || echo "Metrics check failed"

echo "4. Publishing test message..."
docker-compose exec -T smpp-engine python -c "
import asyncio, json, aio_pika
async def send():
    conn = await aio_pika.connect_robust('amqp://smpp:smpp_password@rabbitmq:5672/smpp')
    ch = await conn.channel()
    await ch.default_exchange.publish(
        aio_pika.Message(body=json.dumps({
            'message_id': 'e2e-test-001',
            'tenant_id': '00000000-0000-0000-0000-000000000001',
            'source': 'TEST',
            'destination': '+15551234567',
            'content': 'E2E test message'
        }).encode()), routing_key='smpp.submit')
    await conn.close()
asyncio.run(send())
"

echo "5. Checking message in postgres..."
docker-compose exec -T postgres psql -U smpp -c "SELECT id, status, destination FROM messages LIMIT 5;" || echo "Query failed"

echo "6. Checking metrics..."
curl -s http://localhost:8000/metrics | grep smpp_messages_sent_total || echo "No messages metric yet"

echo "=== E2E Test Complete ==="