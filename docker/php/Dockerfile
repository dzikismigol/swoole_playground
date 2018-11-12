FROM php:7.2.12-cli-stretch

RUN pecl install swoole && \
    pecl clear-cache && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        unzip \
        git && \
    rm -rf /var/lib/apt/lists/*
