# CRUD de paises

Systema operativo : Linux (ubuntu)

Crud sencillo hecho con :
- Symfony 7.1.2 
- bootstrap/5.3.0
- jquery 3.6
- mysql:5.7
- Php 8.2
- nginx
- Docker 


## Instalación

1. Clona este repositorio: git clone https://github.com/juanshoweb/prueba_auren.git

2. Crea la imagenes y levanta los contendores de Docker
    - docker-compose down
    - docker-compose build
    - docker-compose up -d

3. Crea las migraciones
    - Entrar al contenedor de php : 
        - docker-compose exec php bash
    - Ejucutar las migraciones :
        - php bin/console make:migration
        - php bin/console doctrine:migrations:migrate

4. Actualización de paquetes 
    - composer install

## Ejecución de pruebas unitarias: (desde la Raíz del proyecto)

- ./vendor/bin/phpunit src/tests/Controller/CountryControllerTest.php
- ./vendor/bin/phpunit src/tests/Service/CountryServiceTest.php
           
## Extra

1. Borrar cache (desde la raíz del proyecto):
    docker-compose run php bash -c "php bin/console cache:clear"


