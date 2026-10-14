# Carpeta `routes`

Aqui se definen las rutas de la aplicacion.

- `web.php`: redirige la raiz `/` al panel Filament `/admin`.
- `console.php`: registra tareas programadas y comandos de consola.

El CRUD principal no se define aqui manualmente porque Filament registra sus rutas desde los recursos en `app/Filament/Resources`.
