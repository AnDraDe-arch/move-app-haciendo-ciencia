Sistema web para la promoción autónoma de la actividad física y el rendimiento en adultos
Tecnologías: PHP, MySQL, HTML, CSS, JavaScript (fetch) y Bootstrap (CDN)
Contenido:
 - db.sql              -> Script SQL para crear la base de datos y tablas
 - config.php          -> Archivo de configuración (poner credenciales)
 - register.php        -> Registro de usuarios
 - login.php           -> Inicio de sesión
 - logout.php          -> Cerrar sesión
 - dashboard.php       -> Panel de usuario (registro y vista de actividades)
 - save_activity.php   -> API para guardar actividad
 - fetch_activities.php-> API para obtener actividades (JSON)
 - index.php           -> Página principal (dirección a login/register)
 - assets/             -> carpeta con recursos (css js)
Instrucciones rápidas:
1) Crear la base de datos: mysql -u root -p < db.sql  (ó usar phpMyAdmin e importar db.sql)
2) Editar config.php con los datos de conexión a tu servidor MySQL.
3) Colocar la carpeta en tu servidor local (XAMPP, WAMP, LAMP) dentro de htdocs/www.
4) Acceder a http://localhost/sistema_web_actividad_fisica/index.php
