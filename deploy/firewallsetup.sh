# Firewall Configuration Script for AlmaLinux
# Run as root: bash firewallsetup.sh

#!/bin/bash
set -e

echo "Configuring firewall..."

# Default policies
firewall-cmd --permanent --add-service=ssh
firewall-cmd --permanent --add-service=https

# SMPP port
firewall-cmd --permanent --add-port=2775/tcp

# Remove unnecessary services
firewall-cmd --permanent --remove-service=dhcpv6-client || true

# Apply
firewall-cmd --reload

echo "Firewall configured. Public ports:"
firewall-cmd --list-ports