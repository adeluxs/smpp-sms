#!/usr/bin/env php
<?php

echo "SMPP Platform Bootstrap Script\n";
echo "==============================\n\n";

echo "1. Starting Docker infrastructure...\n";
passthru('docker-compose up -d');

echo "\n2. Waiting for services...\n";
sleep(10);

echo "\n3. Running database migrations...\n";
passthru('docker-compose exec -T laravel php artisan migrate --force 2>/dev/null || echo "Migrations will run on first container start"');

echo "\n4. Checking services...\n";
echo "   Redis: ";
passthru('docker-compose exec -T redis redis-cli ping 2>/dev/null || echo "not ready"');
echo "   RabbitMQ: ";
passthru('docker-compose exec -T rabbitmq rabbitmqctl status 2>/dev/null || echo "not ready"');

echo "\n5. Service endpoints:\n";
echo "   Laravel:      http://localhost:8080\n";
echo "   RabbitMQ UI:  http://localhost:15672 (smpp/smpp_password)\n";
echo "   Prometheus:   http://localhost:9090\n";
echo "   Grafana:      http://localhost:3000 (admin/admin)\n";
echo "   SMPP Engine:  localhost:2775\n";
echo "   SMPP Mock:    localhost:2776\n";

echo "\nBootstrap complete.\n";