# ÁTICA FCT
[![Build Status](https://travis-ci.org/iesoretania/atica-fct.svg?branch=master)](https://travis-ci.org/iesoretania/atica-fct)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/iesoretania/atica-fct/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/iesoretania/atica-fct/?branch=master)
[![Code Climate](https://codeclimate.com/github/iesoretania/atica-fct/badges/gpa.svg)](https://codeclimate.com/github/iesoretania/atica-fct)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4bb0a4d5-d6d4-46c8-b51d-97b37ab2f5f2/mini.png)](https://insight.sensiolabs.com/projects/4bb0a4d5-d6d4-46c8-b51d-97b37ab2f5f2)

Aplicación web para la gestión de la formación en centros de trabajo (en desarrollo, aún no está lista para producción).

Este proyecto utiliza [Symfony2] y otros muchos componentes que se instalan usando [Composer] y [npmjs].

Para facilitar el desarrollo se proporciona un entorno [Vagrant] con todas las dependencias ya instaladas.

## Requisitos

- PHP 5.6.x o superior
- Servidor web Apache2 (podría funcionar con nginx, pero no se ha probado aún)
- Cualquier sistema gestor de bases de datos que funcione bajo Doctrine (p.ej. MySQL, MariaDB, PosgreSQL, SQLite, etc.)
- PHP [Composer]
- [Node.js] y [npmjs] (si se ha descargado una build completa, no serán necesarios)

## Instalación

- Ejecutar `composer install` desde la carpeta del proyecto.
- Ejecutar `npm install`.
- Ejecutar `gulp`. [Gulp.js] se instala automáticamente con el comando anterior.
- Configurar el sitio de Apache2 para que el `DocumentRoot` sea la carpeta `web/` dentro de la carpeta de instalación.
- Modificar el fichero `parameters.yml` con los datos de acceso al sistema gestor de bases de datos o otros parámetros
de configuración globales que considere interesantes.
- Ejecutar `app/console assets:install` para completar la instalación de los recursos en la carpeta `web/`.
- Para crear la base de datos: `app/console doctrine:database:create`.
- Para crear las tablas: `app/console doctrine:schema:create`.
- Para insertar los datos iniciales: `app/console doctrine:fixtures:load`.

## Entorno de desarrollo

Para poder ejecutar la aplicación en un entorno de desarrollo basta con tener [Vagrant] instalado junto con [VirtualBox]
y ejecutar el comando `vagrant up`. La aplicación será accesible desde la dirección http://192.168.33.10/

## Licencia
Esta aplicación se ofrece bajo licencia [AGPL versión 3].

[Vagrant]: https://www.vagrantup.com/
[VirtualBox]: https://www.virtualbox.org
[Symfony2]: http://symfony.com/
[Composer]: http://getcomposer.org
[AGPL versión 3]: http://www.gnu.org/licenses/agpl.html
[Node.js]: https://nodejs.org/en/
[npmjs]: https://www.npmjs.com/
[Gulp.js]: http://gulpjs.com/
