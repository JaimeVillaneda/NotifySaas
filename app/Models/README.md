# Carpeta `app/Models`

Los modelos conectan la logica de Laravel con la base de datos.

Cada modelo representa una tabla:

- `Company`: empresas que usan el SaaS.
- `User`: usuarios administradores que pueden entrar al panel.
- `Person`: clientes o destinatarios de mensajes.
- `MessageTemplate`: plantillas reutilizables de mensajes.
- `MessageAutomation`: recordatorios programados.
- `MessageDispatch`: historial de envios e intentos.
- `CommunicationConnection`: conexiones externas como Telegram, SMTP y Evolution API. Se editan desde una pagina con pestanas, no desde un CRUD independiente.

Los metodos de relacion, como `company()`, `people()` o `dispatches()`, permiten navegar entre tablas sin escribir SQL manual.
