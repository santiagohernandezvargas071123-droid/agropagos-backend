FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

ENV REPO=https://raw.githubusercontent.com/santiagohernandezvargas071123-droid/agropagos-backend/master

RUN mkdir -p /var/www/html/config /var/www/html/models && \
    wget -q "$REPO/api.php"                  -O /var/www/html/api.php && \
    wget -q "$REPO/install.php"              -O /var/www/html/install.php && \
    wget -q "$REPO/config/database.php"      -O /var/www/html/config/database.php && \
    wget -q "$REPO/models/User.php"          -O /var/www/html/models/User.php && \
    wget -q "$REPO/models/Worker.php"        -O /var/www/html/models/Worker.php && \
    wget -q "$REPO/models/Payment.php"       -O /var/www/html/models/Payment.php && \
    wget -q "$REPO/models/Setting.php"       -O /var/www/html/models/Setting.php && \
    wget -q "$REPO/models/LotTask.php"       -O /var/www/html/models/LotTask.php

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]
