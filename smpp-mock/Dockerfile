FROM python:3.11-alpine

WORKDIR /app

COPY smpp-mock/pyproject.toml ./
RUN pip install --no-cache-dir -e .

COPY smpp-mock/src/ ./src

CMD ["python", "-m", "smpp_mock"]