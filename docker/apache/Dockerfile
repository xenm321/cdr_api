FROM ubuntu:12.04

VOLUME ["/var/www/html"]

RUN apt-get update && \
    apt-get install -y \
      curl \
      apache2 \
      php5 \
      php5-cli \
      libapache2-mod-php5 \
      php5-mysql \
      nano

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY default /etc/apache2/sites-available/default
COPY run /usr/local/bin/run
RUN chmod +x /usr/local/bin/run
RUN a2enmod rewrite

WORKDIR /var/www/html

EXPOSE 80
CMD ["/usr/local/bin/run"]
