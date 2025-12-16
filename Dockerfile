FROM dunglas/frankenphp:php8.4.15-bookworm

# Install MySQL PDO driver
RUN docker-php-ext-install pdo_mysql

# Copy app files
COPY . /app
WORKDIR /app

# Expose Railway port
EXPOSE 8080

# Start PHP server
CMD ["php", "-S", "0.0.0.0:8080"]
