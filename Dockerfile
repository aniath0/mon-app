FROM ubuntu:22.04

# Évite les questions pendant l'installation
ENV DEBIAN_FRONTEND=noninteractive

# Installation Apache, PHP et extensions
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    php-cli \
    libapache2-mod-php \
    php-mysql \
    php-sqlite3 \
    php-curl \
    php-mbstring \
    php-xml \
    php-zip \
    php-gd \
    php-bcmath \
    curl \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Active mod_rewrite
RUN a2enmod rewrite

# Configuration Apache
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Démarre Apache au premier plan
CMD ["apache2ctl", "-D", "FOREGROUND"]

# Port d'exposition
EXPOSE 80
