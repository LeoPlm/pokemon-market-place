# Image de l'application Laravel : PHP 8.4 + extensions + Composer + Node.js
# Les dépendances du projet (vendor/, node_modules/) sont installées au démarrage
# du conteneur par compose.yml, pas dans l'image.
FROM php:8.4-cli

# Extensions PHP nécessaires à Laravel et au projet (MySQL, images, intl…)
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl zip gd bcmath pcntl exif opcache

# Outils système
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && git config --system --add safe.directory /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Node.js + npm (pour Vite et Tailwind)
COPY --from=node:24-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Réglages PHP : jusqu'à 8 photos de 5 Mo par annonce (RG-08)
RUN { \
      echo 'upload_max_filesize=6M'; \
      echo 'post_max_size=50M'; \
      echo 'memory_limit=256M'; \
      echo 'date.timezone=Europe/Paris'; \
      echo 'opcache.enable=1'; \
      echo 'opcache.enable_cli=1'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'opcache.revalidate_freq=1'; \
    } > /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

EXPOSE 8000 5173
