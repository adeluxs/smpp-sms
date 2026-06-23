from prometheus_client import Counter, Gauge, Histogram

MESSAGES_SENT = Counter('smpp_messages_sent_total', 'Total messages sent')
MESSAGES_DLR = Counter('smpp_dlr_received_total', 'Total DLRs received')
ACTIVE_SESSIONS = Gauge('smpp_active_sessions', 'Active SMPP sessions')
QUEUE_DEPTH = Gauge('smpp_queue_depth', 'Current queue depth')
PROVIDER_RECONNECTS = Counter('smpp_provider_reconnects_total', 'Provider reconnection attempts')
BIND_ATTEMPTS = Counter('smpp_bind_attempts_total', 'Bind attempts', ['result'])
SUBMIT_LATENCY = Histogram('smpp_submit_latency_seconds', 'Submit processing time')
DLR_LATENCY = Histogram('smpp_dlr_latency_seconds', 'DLR processing latency')