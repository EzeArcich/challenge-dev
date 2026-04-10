## Cómo ejecutar el proyecto

# Requisitos
PHP 8.2 o superior
Composer
Node.js y npm
Base de datos configurable en .env

# Instalación
composer install
npm install
php artisan key:generate
php artisan migrate

# Levantar el proyecto
composer run dev
La API quedará disponible normalmente en:
http://127.0.0.1:8000


# Ejecutar tests
php artisan test

# Notas
Al agregar o cambiar variables de entorno, conviene limpiar caché:
php artisan optimize:clear