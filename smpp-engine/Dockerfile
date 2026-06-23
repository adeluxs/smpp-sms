FROM python:3.11-alpine

RUN apk add --no-cache gcc musl-dev linux-headers

WORKDIR /app

COPY smpp-engine/pyproject.toml ./
RUN pip install --no-cache-dir -e .

COPY smpp-engine/ ./

RUN addgroup -g 1001 -S smpp && \
    adduser -u 1001 -S smpp -G smpp

USER smpp

CMD ["python", "-m", "smpp_engine"]