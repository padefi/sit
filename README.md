# Bank-API-INSTP-UTN

## :newspaper: Introducción
S.I.T. es una aplicación web SPA desarrollada utilizando los frameworks Laravel y VueJS.
Dicho sistema ha sido diseñado para facilitar la gestión y administración de los ingresos y egresos financieros de la tesorería de una PyME. Las funciones que ofrece el sistema permiten relizar la carga, consulta y modificación de datos, aplicar filtros de búsqueda para obtener información precisa y emitir informes detallados para un control adecuado del dinero y el trabajo realizado por los usuarios.

## Índice
- [Introducción](#newspaper-introducción)
- [Funcionalidades](#hammer-funcionalidades-del-sistema)
- [Instalación](#rocket-instalación)
- [Tecnologías utilizadas](#heavy_check_mark-tecnologías-utilizadas)
- [Créditos](#coffee-créditos)
- [Autor](#blue_book-autor)

## :hammer: Funcionalidades del sistema
+ ### Inicio: 
    + Resumen comprobantes pendientes tesorería.
    + Resumen movimientos diarios.
    + Resumen facturas pendientes proveedores.

+ ### Configuración                
    + #### Bancos
        + ABM bancos
        + ABM cuentas bancarias.
    + ####  Relaciones
        + #####  Tipos
            + Relación Tipo - Subtipo
        + #####  Subtipos
            + ABM subtipos
            + Relación Subtipo - Gasto
            + Relación Subtipo - Proveedor
        + #####  Gastos
            + ABM gastos
    + ####  Retenciones
        + #####  Ganancias
            + ABM retenciones ganancias.
        + #####  SUSS
            + ABM retenciones Suss
        + #####  I.V.A.
            + ABM retenciones I.V.A.

+ ### Operaciones:
    + #### Comrpobantes
        + ABM comprobantes de tesorería 
    + #### Mov. Diarios
        + Movimientos diarios de ingreso y egreso de la tesorería.
    + #### Proveedores
        + ABM proveedores
        + ABM facturas
        + Generación de orden de pago/cobro a la tesorería

+ ### Panel de usuarios: Únicamente disponible para el rol Administrador y Tesorero
    + ABM usuarios.

## :rocket: Instalación
La aplicación cuenta con diferentes directorios
+ #### App
Contiene todo el backend del sistema (funciones necesarias para la conexión, consultas y almacenamientos de los datos en la BD).

+ #### Database
Contiene la estructura de todas las tablas de la BD, las migraciones y seeders necesarios para poder configurar el sistema por primera vez.

+ #### Resources
Contiene todo el frontend del sistema (views, funciones y css).

+ #### Routes
Contiene todas las rutas, junto con los permisos, del sistema.

Antes que nada, deberá tener instalado NodeJS, Composer y un entorno de desarrollo (Xampp, Docker, etc).
<https://nodejs.org/en>

<https://getcomposer.org/>

<https://www.apachefriends.org>

<https://www.docker.com/>

crear un archivo **.env** en la raíz.
```sh
cp .env.example .env
```

Una vez hecho esto se deberá ejecutar los siguientes comandos que instalará las librerías necesarias:
```sh
npm i
composer i
php artisan key:generate
php artisan config:cache
php artisan migrate
php artisan db:seed
```

Finalizada la instalación de todas las librerías se deberá ejecutar el siguiente comando (ya que se utiliza el script npm-run-all, el cual permite ejecutar varios scripts en paralelo):
```sh
npm start
```
Se puede acceder al sistema a través de la ruta <http://127.0.0.1:8000> o <http://localhost:8000>

Si se desea poner la app en producción, se deberán ejecutar los siguientes comandos
```sh
npm run build
npm run serve
php artisan reverb:start
```

Se puede acceder al sistema a través de la ruta <http://127.0.0.1:8000> o <http://localhost:8000>

Puede utilizar los siguientes usuarios:
+ ##### Usuarios:
    + **Usuario**: admin
    + **Contraseña**: administrador
    + **Perfil**: Admin
    + **Usuario**: jperez
    + **Contraseña**: 12345678
    + **Perfil**: Tesorero
    + **Usuario**: jgonzalez
    + **Contraseña**: 12345678
    + **Perfil**: Auxiliar
    + **Usuario**: rgomez
    + **Contraseña**: 12345678
    + **Perfil**: Administrativo

## :heavy_check_mark: Tecnologías utilizadas
- Laravel 10/11
- VueJS
- InertiaJS
- PrimeVue 3.53
- Tailwind
- Axios
- Pusher
- Laravel Reverb
- Laravel Sanctum
- Spatie
- MPDF
- Laravel Excel/ Maatwebsite
- Entre otras

## :coffee: Créditos
El sistema está desarrollado teniendo como referencia la guía gratuita **MERN Crash Course** [Parte 1 ](https://www.traversymedia.com/blog/mern-crash-course-part-1) y [Parte 2](https://www.traversymedia.com/blog/mern-crash-course-part-2), ofrecida por **Brad Traversy** en su sitio web <https://www.traversymedia.com>

## :blue_book: Autor
**Desarrollado por Pablo De Filpo**