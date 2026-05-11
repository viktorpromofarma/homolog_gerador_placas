FROM alpine:3.22 AS builder

RUN apk add --no-cache \
    curl \
    gnupg \
    unixodbc-dev \
    autoconf \
    make \
    g++ \
    ca-certificates \
    php83 \
    php83-dev \
    php83-pear \
    php83-openssl

WORKDIR /tmp

RUN curl -O -k https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.10.6.1-1_amd64.apk \
 && apk add --allow-untrusted msodbcsql17_17.10.6.1-1_amd64.apk

RUN pecl channel-update pecl.php.net \
 && pecl install sqlsrv pdo_sqlsrv

FROM alpine:3.22 

ARG USER=developer
ARG UID=1000

RUN apk update && apk add --no-cache \
    curl \
    ca-certificates \
    zip \
    unzip \
    sqlite \
    nodejs \
    npm \
    php83 \
    php83-fpm \
    php83-curl \
    php83-mbstring \
    php83-bcmath \
    php83-openssl \
    php83-pdo \
    php83-pdo_sqlite \
    php83-tokenizer \
    php83-fileinfo \
    php83-zip \
    php83-intl \
    php83-xml \
    php83-ctype \
    php83-phar \
    php83-pcntl \
    php83-session \
    php83-dom \
    php83-xmlreader \
    php83-xmlwriter \
    php83-simplexml \
    php83-iconv \
    php83-redis \
    php83-opcache \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/share/doc

RUN curl -O -k https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.10.6.1-1_amd64.apk \
 && apk add --allow-untrusted msodbcsql17_17.10.6.1-1_amd64.apk

COPY --from=builder /usr/lib/php83/modules/sqlsrv.so /usr/lib/php83/modules/
COPY --from=builder /usr/lib/php83/modules/pdo_sqlsrv.so /usr/lib/php83/modules/

RUN echo "extension=sqlsrv.so" > /etc/php83/conf.d/20_sqlsrv.ini \
 && echo "extension=pdo_sqlsrv.so" > /etc/php83/conf.d/10_pdo_sqlsrv.ini

RUN curl -sS https://getcomposer.org/installer | php \
    -- --install-dir=/usr/local/bin --filename=composer

RUN addgroup -g ${UID} -S ${USER} \
 && adduser -u ${UID} -S ${USER} -G ${USER} \
 && mkdir -p /var/log/ /var/run/ /var/www/ \
 && chown -R ${USER}:${USER} /var/log/ /var/run/ /var/www/ \
 && chmod -R 775 /var/log/ /var/run /var/www 

COPY ./php/php-fpm.conf /etc/php83/php-fpm.conf
COPY ./php/php.ini /etc/php83/php.ini

WORKDIR /var/www/html

CMD ["php-fpm83", "--nodaemonize", "--fpm-config", "/etc/php83/php-fpm.conf"]