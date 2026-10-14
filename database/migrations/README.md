# Carpeta `database/migrations`

Las migraciones describen la estructura de la base de datos.

Cada archivo crea o modifica tablas como:

- `companies`: empresas.
- `users`: usuarios administradores.
- `people`: clientes.
- `communication_connections`: conexiones externas.
- `message_templates`: plantillas.
- `message_automations`: recordatorios.
- `message_dispatches`: historial de envios.

Se ejecutan con:

```bash
php artisan migrate
```
