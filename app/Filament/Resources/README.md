# Carpeta `app/Filament/Resources`

Cada subcarpeta es un modulo visible en el panel lateral de Filament.

Ejemplos:

- `Companies`: modulo de empresas.
- `Users`: modulo de usuarios administradores.
- `People`: modulo de clientes.
- `MessageTemplates`: modulo de plantillas.
- `MessageAutomations`: modulo de recordatorios.
- `MessageDispatches`: modulo de historial.
Las conexiones externas no estan como CRUD aqui porque se administran desde una pagina de configuracion con pestanas en `app/Filament/Pages/ConnectionSettings.php`.

Dentro de cada recurso normalmente hay:

- `Schemas`: define los campos del formulario.
- `Tables`: define columnas, filtros y acciones de la lista.
- `Pages`: define las paginas de listar, crear y editar.
