FROM alpine:3.20

ARG USER=vinicius
ARG UID=1000

RUN apk update && apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    sqlite \
    nodejs \
    npm \
    supervisor \
    php83 \
    php83-dev \
    php83-fpm \
    php83-curl \
    php83-mbstring \
    php83-bcmath \
    php83-openssl \
    php83-pdo \
    php83-pdo_sqlite \
    php83-pdo_mysql \
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
    php83-pear \
    gnupg \
    unixodbc-dev \
    autoconf \
    make \
    g++ \
    ca-certificates \
    libstdc++ \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/share/doc

RUN curl -sS https://getcomposer.org/installer | php83 -- \
    --install-dir=/usr/local/bin \
    --filename=composer \
    --no-interaction

RUN update-ca-certificates && \
    cd /tmp && \
    curl -k -SLO https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.10.6.1-1_amd64.apk && \
    curl -k -SLO https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/mssql-tools_17.10.1.1-1_amd64.apk && \
    curl -k -SLO https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.10.6.1-1_amd64.sig && \
    curl -k -SLO https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/mssql-tools_17.10.1.1-1_amd64.sig && \
    curl -k https://packages.microsoft.com/keys/microsoft.asc | gpg --import - && \
    gpg --verify msodbcsql17_17.10.6.1-1_amd64.sig msodbcsql17_17.10.6.1-1_amd64.apk && \
    gpg --verify mssql-tools_17.10.1.1-1_amd64.sig mssql-tools_17.10.1.1-1_amd64.apk && \
    apk add --allow-untrusted msodbcsql17_17.10.6.1-1_amd64.apk && \
    apk add --allow-untrusted mssql-tools_17.10.1.1-1_amd64.apk && \
    ODBC_INCLUDE=$(find /opt/microsoft -name "msodbcsql.h" 2>/dev/null | head -1 | xargs dirname) && \
    ODBC_LIB=$(find /opt/microsoft -name "libmsodbcsql*" 2>/dev/null | head -1 | xargs dirname) && \
    export CPPFLAGS="-I$ODBC_INCLUDE" && \
    export LDFLAGS="-L$ODBC_LIB -Wl,-rpath,$ODBC_LIB" && \
    git clone --depth=1 https://github.com/microsoft/msphpsql.git /tmp/msphpsql && \
    cd /tmp/msphpsql/source/sqlsrv && \
    phpize83 && \
    ./configure --with-php-config=/usr/bin/php-config83 && \
    make -j$(nproc) && make install && \
    cd /tmp/msphpsql/source/pdo_sqlsrv && \
    phpize83 && \
    ./configure --with-php-config=/usr/bin/php-config83 && \
    make -j$(nproc) && make install && \
    INI_DIR=$(php83 --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||") && \
    echo "extension=sqlsrv.so"     > "$INI_DIR/10_sqlsrv.ini" && \
    echo "extension=pdo_sqlsrv.so" > "$INI_DIR/20_pdo_sqlsrv.ini" && \
    rm -rf /var/cache/apk/* /tmp/*

RUN addgroup -g ${UID} -S ${UID} \
    && adduser -u ${UID} -S ${USER} -G ${UID} \
    && mkdir -p /var/log/ /var/run/ /var/www/ \
    && chown -R ${USER}:www-data /var/log/ \
    && chown -R ${USER}:www-data /var/run/ \
    && chown -R ${USER}:www-data /var/www/ \
    && chmod -R 775 /var/log/ /var/run/ /var/www/

COPY ./supervisor/supervisord.conf /etc/supervisord.conf
COPY ./supervisor/conf.d/*.conf /etc/supervisor/conf.d/
COPY ./php/php-fpm.conf /etc/php83/php-fpm.conf
COPY ./php/php.ini /etc/php83/php.ini

CMD ["supervisord", "-c", "/etc/supervisord.conf"]

USER ${USER}

WORKDIR /var/www/html