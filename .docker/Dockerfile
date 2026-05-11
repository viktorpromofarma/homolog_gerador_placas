# Usando a imagem base do Alpine
FROM alpine:3.20

# Definindo os parâmetros de usuário e UID
ARG USER=vinicius
ARG UID=1000

# Instalação de dependências essenciais
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

# Instalação do Composer
RUN curl -sS https://getcomposer.org/installer | php83 -- --install-dir=/usr/local/bin --filename=composer --no-interaction

# Instalação do driver ODBC da Microsoft + extensões PHP sqlsrv compiladas da fonte
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
    # Descobrir onde estão os headers do msodbcsql17 instalado
    ODBC_DIR=$(find /opt/microsoft -name "msodbcsql.h" 2>/dev/null | head -1 | xargs dirname || echo "/opt/microsoft/msodbcsql17/include") && \
    ODBC_BASE=$(echo "$ODBC_DIR" | sed 's|/include.*||') && \
    echo "Usando ODBC dir: $ODBC_BASE" && \
    # Clonar o repositório msphpsql
    git clone --depth=1 --branch v5.12.0 https://github.com/microsoft/msphpsql.git /tmp/msphpsql && \
    # Compilar sqlsrv
    cd /tmp/msphpsql/source/sqlsrv && \
    phpize83 && \
    ./configure \
        --with-php-config=/usr/bin/php-config83 \
        --with-msodbcsql17-dir="$ODBC_BASE" && \
    make -j$(nproc) && make install && \
    # Compilar pdo_sqlsrv
    cd /tmp/msphpsql/source/pdo_sqlsrv && \
    phpize83 && \
    ./configure \
        --with-php-config=/usr/bin/php-config83 \
        --with-msodbcsql17-dir="$ODBC_BASE" && \
    make -j$(nproc) && make install && \
    # Registrar as extensões
    INI_DIR=$(php83 --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||") && \
    echo "extension=sqlsrv.so"     > "$INI_DIR/10_sqlsrv.ini" && \
    echo "extension=pdo_sqlsrv.so" > "$INI_DIR/20_pdo_sqlsrv.ini" && \
    # Limpeza
    rm -rf /var/cache/apk/* /tmp/*

# Criação do usuário e diretórios necessários
RUN addgroup -g ${UID} -S ${UID} \
    && adduser -u ${UID} -S ${USER} -G ${UID} \
    && mkdir -p /var/log/ /var/run/ /var/www/ \
    && chown -R ${USER}:www-data /var/log/ \
    && chown -R ${USER}:www-data /var/run/ \
    && chown -R ${USER}:www-data /var/www/ \
    && chmod -R 775 /var/log/ /var/run/ /var/www/

# Copiar os arquivos de configuração
COPY ./supervisor/supervisord.conf /etc/supervisord.conf
COPY ./supervisor/conf.d/*.conf /etc/supervisor/conf.d/
COPY ./php/php-fpm.conf /etc/php83/php-fpm.conf
COPY ./php/php.ini /etc/php83/php.ini

# Iniciar o Supervisor
CMD ["supervisord", "-c", "/etc/supervisord.conf"]

# Rodar o container com o usuário criado
USER ${USER}

# Definindo o diretório de trabalho
WORKDIR /var/www/html