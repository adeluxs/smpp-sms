-- Initial database setup for SMPP Platform
-- This file is executed on container startup

-- Tenants table
CREATE TABLE IF NOT EXISTS tenants (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- SMPP clients (smpp_credentials table)
CREATE TABLE IF NOT EXISTS smpp_clients (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID NOT NULL REFERENCES tenants(id),
    system_id VARCHAR(100) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    sender_id VARCHAR(11),
    ip_allowlist INET[],
    throughput_limit INTEGER DEFAULT 100,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- API keys
CREATE TABLE IF NOT EXISTS api_keys (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id UUID NOT NULL REFERENCES tenants(id),
    key_hash TEXT NOT NULL,
    name VARCHAR(255),
    scopes JSONB,
    expires_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Seed test tenant
INSERT INTO tenants (name, status) 
VALUES ('Test Tenant', 'active')
ON CONFLICT DO NOTHING;

-- Seed test SMPP client (password: test_password)
-- Hash: sha256 of 'test_password'
INSERT INTO smpp_clients (tenant_id, system_id, password_hash, sender_id, throughput_limit, status)
SELECT id, 'test_client', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d892300', 'TestSender', 100, 'active'
FROM tenants WHERE name = 'Test Tenant'
ON CONFLICT DO NOTHING;