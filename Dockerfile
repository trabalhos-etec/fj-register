# Use a imagem oficial do PHP com Apache
FROM php:8.1-apache

# Instala a extensão PDO para MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilita o mod_rewrite
RUN a2enmod rewrite

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto para o contêiner
COPY . .

# Executa o composer install ao iniciar o contêiner
RUN composer install

# Configura as permissões
RUN chown -R www-data:www-data /var/www/html

# Exponha a porta 80
EXPOSE 80

# Inicia o Apache
CMD ["apache2-foreground"]
