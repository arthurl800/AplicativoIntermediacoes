# Estágio 1: Instalar dependências com o Composer
# Usamos uma imagem específica do Composer para manter o estágio final limpo.
FROM composer:2 as composer

WORKDIR /app

# Copia os arquivos de definição de dependências (do subdiretório aplicativoIntermediacoes)
COPY aplicativoIntermediacoes/composer.json aplicativoIntermediacoes/composer.lock ./

# Instala as dependências de produção e otimiza o autoloader
# Ignora platform-reqs porque as extensões estarão disponíveis no estágio final
RUN composer install --no-dev --no-interaction --no-scripts --no-plugins --optimize-autoloader --ignore-platform-reqs


# Estágio 2: Construir a imagem final da aplicação
# Usamos a imagem oficial do PHP com Apache. A versão 8.1 é requerida pelo phpspreadsheet.
FROM php:8.3-apache

# Habilita o módulo de rewrite do Apache para o .htaccess funcionar
RUN a2enmod rewrite

# Instala as extensões PHP necessárias para o projeto e para o phpspreadsheet
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo pdo_mysql

# Copia as dependências instaladas do estágio 'composer'
COPY --from=composer /app/vendor/ /var/www/html/vendor/

# Copia o código-fonte da aplicação (do subdiretório aplicativoIntermediacoes) para o diretório do Apache
COPY aplicativoIntermediacoes/ /var/www/html/

# Garante que o usuário do Apache (www-data) tenha permissão nos arquivos
RUN chown -R www-data:www-data /var/www/html
