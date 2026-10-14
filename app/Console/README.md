# Carpeta `app/Console`

Aqui se guardan comandos que se ejecutan por terminal o por el scheduler de Laravel.

Actualmente contiene:

- `ProcessPendingReminders`: busca recordatorios pendientes y crea registros de envio en el historial.

El comando se ejecuta con:

```bash
php artisan reminders:process
```

Tambien esta programado en `routes/console.php` para correr cada minuto sin solaparse.
