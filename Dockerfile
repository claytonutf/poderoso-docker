# Use Ubuntu 22.04 LTS como base
FROM ubuntu:22.04

# Evita prompts interativos durante instalação
ENV DEBIAN_FRONTEND=noninteractive

# Atualiza repositórios e instala dependências
RUN apt-get update && apt-get install -y \
    software-properties-common \
    curl \
    wget \
    unzip \
    git \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update

# Instala PHP 8.2 e extensões necessárias
RUN apt-get install -y \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-gd \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-common \
    apache2 \
    libapache2-mod-php8.2

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilita módulos do Apache
RUN a2enmod php8.2 rewrite

# Configura diretório de trabalho
WORKDIR /var/www/html

# Copia configuração personalizada do PHP (opcional)
COPY php.ini /etc/php/8.2/apache2/php.ini

# Configura permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expõe porta 80
EXPOSE 80

# Comando para iniciar Apache em foreground
CMD ["apache2ctl", "-D", "FOREGROUND"]