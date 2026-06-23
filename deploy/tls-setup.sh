# TLS Certificate Setup for Production
# Run as root: bash tls-setup.sh sms.example.com

#!/bin/bash
set -e

DOMAIN="${1:-sms.example.com}"

dnf install -y certbot python3-certbot-nginx

certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m admin@"$DOMAIN"

# Auto-renewal
systemctl enable --now certbot-renew.timer

# Test renewal
certbot renew --dry-run

echo "TLS configured for $DOMAIN"