FROM dunglas/frankenphp:php8.4-bookworm

# Install required PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo_mysql

# Set working directory
WORKDIR /app

# Copy all project files
COPY . .

# Railway exposes port 8080 internally
EXPOSE 8080

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080"]
