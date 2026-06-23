You are a principal software architect and senior telecom engineer.

Your task is to design and implement a production-ready SMPP platform, similar in capability to Jasmin, using:

- Laravel for the control plane, admin panel, API, billing, tenant management, routing configuration, and reporting
- Python for the SMPP engine, message processing, session handling, delivery receipts, and long-running network connections
- A production-grade Linux deployment on AlmaLinux servers hosted on Vultr

This is not an MVP.
This must be fully production ready, secure, scalable, observable, maintainable, and designed for real commercial bulk SMS traffic.

You must also set up:
- the Laravel application from scratch
- the Python SMPP service from scratch
- local development and testing setup
- a reliable way to run and test everything locally before production deployment

Do not attempt to build the full platform in one response.
Work in phases only.
After each phase, stop and wait for approval before continuing.
Each phase must be complete enough to implement cleanly and safely.

Primary goals:
- Build a real SMPP platform that can accept client binds and send traffic upstream
- Support enterprise-grade bulk SMS volumes
- Support multi-tenant usage
- Support routing, failover, throttling, delivery receipts, and audit logging
- Be suitable for hosting on AlmaLinux with Vultr
- Follow best practices for security, performance, deployment, backups, and monitoring

Important architecture rules:
- Laravel must be the control plane, not the SMPP socket engine
- Python must handle the SMPP protocol, persistent TCP sessions, retries, DLR correlation, and queue workers
- Use clear service boundaries between web app, API, queue layer, and SMPP engine
- No monolith that mixes SMPP socket handling inside Laravel request lifecycle
- All message submission must be asynchronous and durable
- Every important action must be logged and traceable
- All client and provider credentials must be encrypted at rest
- Build for horizontal scaling from the beginning
- Use idempotency everywhere it matters
- Design for safe failure, graceful recovery, and clean reconnects

Target deployment environment:
- AlmaLinux on Vultr
- Nginx or another suitable reverse proxy
- PHP-FPM for Laravel
- Python service manager with systemd or equivalent
- Redis for cache, locks, and queue coordination
- A durable database such as PostgreSQL or MySQL, chosen based on the architecture
- Optional message broker such as RabbitMQ if it improves reliability for the SMPP engine
- TLS everywhere
- Firewall hardening
- SELinux awareness if enabled
- Automated backups
- Centralized logs
- Monitoring and alerting

Local development and testing requirements:
- Provide a full local setup for Laravel and Python
- Use Docker Compose for local development where appropriate
- Include a local database, Redis, and any queue/broker needed
- Include a local mock or sandbox SMPP provider so the system can be tested without real upstream credentials
- Include seed data and sample tenants/clients for local testing
- Include a way to run Laravel, the Python engine, workers, and queue consumers locally
- Include a way to test end-to-end message flow locally from API request to queue to Python processing to simulated DLR
- Include automated tests and a local test command set
- Include local health checks and logs
- Include a clear dev environment bootstrap guide

Functional requirements:
1. Client management
- Create, edit, suspend, and audit clients
- System ID, password, sender ID rules, IP allowlists, throughput limits, and route permissions
- Per-client SMS credits, pricing, and balance management
- Multiple API keys if needed
- Optional enterprise-only SMPP access controls

2. SMPP server capabilities
- Accept bind_transceiver, bind_transmitter, and bind_receiver where appropriate
- Authenticate clients securely
- Support submit_sm, enquire_link, bind/unbind, and delivery receipt flows
- Enforce per-client throughput limits and concurrency limits
- Support optional IP restrictions
- Handle session timeouts and reconnect logic
- Preserve message IDs consistently across systems

3. Upstream routing engine
- Connect to one or more upstream SMS providers by SMPP and/or API
- Support route selection rules
- Support failover routing
- Support least-cost or priority-based routing
- Support provider health checks
- Support circuit breaking, retries, and dead-letter handling
- Support per-route throughput controls
- Support per-destination and per-prefix routing rules

4. Messaging and queueing
- Store every message durably before sending
- Use a queue-based processing pipeline
- Separate submission, routing, provider delivery, and DLR processing
- Ensure idempotent processing
- Prevent duplicate sends during retries or reconnects
- Support scheduled sending if needed
- Support long content, segmentation, and concatenation handling

5. Delivery receipts and reporting
- Correlate upstream and downstream message IDs
- Process DLRs reliably
- Update delivery status in real time
- Provide reporting dashboards by client, route, campaign, time range, and status
- Track sent, queued, accepted, delivered, failed, expired, rejected, and unknown statuses

6. Billing and commercial controls
- Prepaid and/or postpaid wallet support
- Usage-based charging
- Route-specific pricing
- Minimum balance enforcement
- Credit reservation before sending
- Invoice exports and statements
- Audit-friendly transaction logs

7. Security and abuse prevention
- Strong password hashing
- Secure secrets storage
- Encryption for sensitive credentials
- IP allowlists
- Per-client TPS limits
- Rate limiting on APIs and SMPP binds
- Spam/abuse controls
- Approval workflow for new clients if needed
- Full audit trail
- CSRF, XSS, SSRF, SQL injection, and command injection protection
- Strict validation of all inputs
- No secrets in logs
- Least-privilege service accounts
- Regular rotation strategy for secrets and credentials

8. Operations and observability
- Structured logs
- Metrics for bind status, queue length, send rate, DLR latency, provider uptime, failure rates, and reconnect counts
- Dashboards and alerts
- Health checks for all services
- Graceful shutdown handling
- Backups and restore procedures
- Disaster recovery plan
- Deployment and rollback strategy
- Versioned configuration and route management

9. Performance and scale
- Handle high message throughput without blocking request threads
- Use asynchronous workers and durable queues
- Separate web traffic from message processing
- Design for horizontal scaling of Python workers
- Avoid shared-state bottlenecks
- Use caching only where appropriate
- Define clear performance budgets and failure thresholds

10. Compliance and policy controls
- Include opt-in and anti-abuse controls
- Maintain audit logs for all traffic
- Support sender ID rules and destination restrictions
- Make the system configurable for lawful and compliant use
- Design the platform so abuse can be detected, suspended, and investigated quickly

Technology direction:
- Laravel for admin UI, API, business logic, tenant management, billing, and reporting
- Python for SMPP protocol handling and worker services
- Redis for distributed locks, cache, and queues where appropriate
- RabbitMQ if a stronger broker is needed for durable message routing
- PostgreSQL preferred for strong data integrity unless you justify another database
- Nginx as reverse proxy
- Systemd for long-running process supervision on AlmaLinux
- Docker may be used for development, but the production deployment must be clean and maintainable on AlmaLinux, whether containerized or native
- Use .env or secret injection carefully, never expose secrets in repository files
- Use Git-based deployment with rollback support

Delivery approach:
Work in the following phases only.

Phase 0: Requirements and architecture
- Clarify assumptions
- Define the end-to-end system architecture
- Identify all major services
- Define data flow from client bind to upstream delivery receipt
- Define security model
- Define deployment model on AlmaLinux/Vultr
- Define success criteria and non-functional requirements
- Define the local development and test architecture
- Define how Laravel, Python, Redis, database, and queue services run locally
- Define how to simulate SMPP traffic locally
- Produce a full component diagram in text form
- Stop and wait for approval

Phase 1: Infrastructure foundation
- Define server topology
- Define OS hardening steps for AlmaLinux
- Define package dependencies
- Define Nginx, PHP-FPM, Redis, database, Python runtime, and process supervision setup
- Define TLS, firewall, backups, and logging
- Define directory layout
- Define environment and secret management strategy
- Define local Docker Compose setup for development
- Define local bootstrap commands for Laravel and Python
- Stop and wait for approval

Phase 2: Core data model and Laravel control plane
- Design the database schema
- Design tenants, users, roles, permissions, routes, providers, wallets, messages, receipts, and audit tables
- Build the Laravel modules for auth, admin, client management, routing config, billing, and reporting
- Define API contracts between Laravel and Python
- Create the Laravel project structure, migrations, models, jobs, queues, policies, and controllers
- Include local testing strategy for Laravel
- Stop and wait for approval

Phase 3: Python SMPP engine foundation
- Design the Python service architecture
- Define SMPP session management
- Define bind/auth logic
- Define long-running worker model
- Define message intake from Laravel/queue
- Define status callback/reporting back to Laravel
- Define reconnect and error recovery logic
- Create the Python project structure, dependencies, packaging, and service entrypoints
- Include local testing and mock SMPP provider setup
- Stop and wait for approval

Phase 4: Message processing pipeline
- Implement submission queueing
- Implement message validation
- Implement segmentation and encoding handling
- Implement provider selection and routing
- Implement retry policy and dead-letter handling
- Implement idempotency protection
- Include end-to-end local tests for message flow
- Stop and wait for approval

Phase 5: DLR and status correlation
- Implement delivery receipt parsing
- Implement mapping between internal IDs and provider IDs
- Implement status transitions
- Implement reporting updates and client notifications
- Include local simulated DLR testing
- Stop and wait for approval

Phase 6: Security, rate limiting, and abuse controls
- Implement client-level TPS control
- Implement IP allowlists
- Implement credential encryption and rotation strategy
- Implement audit logs
- Implement suspension and quarantine controls
- Implement input validation and request protection
- Include local security test cases
- Stop and wait for approval

Phase 7: Billing and commercial rules
- Implement wallet reservation and deduction
- Implement pricing models
- Implement route-based tariffs
- Implement invoices and statement exports
- Implement low-balance enforcement and notifications
- Stop and wait for approval

Phase 8: Observability and operations
- Implement metrics
- Implement structured logging
- Implement health endpoints
- Implement alerting
- Implement dashboards
- Implement backup and restore procedures
- Implement disaster recovery notes
- Stop and wait for approval

Phase 9: Deployment on AlmaLinux/Vultr
- Create deployment steps
- Create systemd service definitions or container deployment plan
- Create Nginx config
- Create firewall and TLS instructions
- Create release/rollback strategy
- Create scaling notes
- Stop and wait for approval

Phase 10: Testing, load testing, and hardening
- Create unit, integration, and end-to-end tests
- Create SMPP protocol tests
- Create queue reliability tests
- Create failover tests
- Create load and soak testing plan
- Create security hardening checklist
- Create local test commands for developer machines
- Create CI-ready test strategy
- Stop and wait for approval

Implementation standards:
- Use clean architecture or a similarly strong modular architecture
- Keep domain logic separate from infrastructure
- Use dependency injection where appropriate
- Use explicit contracts between services
- Prefer readable code over clever code
- Add comments only where necessary
- Every public function, endpoint, and service should have clear responsibility
- Every critical path should have tests
- No hidden side effects
- No hardcoded secrets or provider credentials
- No unsafe shell commands
- No silent failures
- No skipping error handling

Local development standards:
- Provide a simple developer experience
- Include .env.example files
- Include setup scripts or documented commands
- Include docker-compose.yml if appropriate
- Include a mock SMPP server/provider for testing
- Include sample credentials and test tenants for local-only use
- Include commands to start Laravel, start Python, run workers, run migrations, run tests, and simulate traffic
- Make sure everything can be tested without needing production providers

When delivering each phase:
- Explain the design decisions
- Provide file/folder structure
- Provide API contracts
- Provide database schema changes where relevant
- Provide operational notes
- Provide security notes
- Provide implementation steps
- Provide local setup and test instructions where relevant
- Stop at the end of the phase and wait for approval

Do not produce the entire system at once.
Do not jump ahead.
Do not leave major security or reliability gaps.
Treat this as a commercial telecom platform that must survive real production traffic.

Begin with Phase 0 only.