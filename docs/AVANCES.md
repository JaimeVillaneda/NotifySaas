
# Avances de NotifySaaS

## Estado del proyecto

NotifySaaS es una aplicación desarrollada con Laravel y Filament para gestionar clientes, plantillas y recordatorios, con envío de mensajes mediante Telegram, correo electrónico y WhatsApp.

Fecha de actualización: 6 de noviembre de 2026.

## Funcionalidades implementadas

- Registro de empresas y administradores.
- Autenticación de usuarios.
- Aislamiento de información entre empresas.
- Gestión de clientes.
- Creación y administración de plantillas.
- Programación de recordatorios.
- Historial de mensajes enviados y fallidos.
- Procesamiento de mensajes pendientes.
- Ejecución automática mediante Laravel Scheduler.
- Prevención de envíos duplicados.
- Recuperación de mensajes interrumpidos.

## Integraciones verificadas

### Telegram

- Conexión mediante Telegram Bot API.
- Envío real confirmado.
- Recepción del mensaje verificada.
- Envío automático confirmado.

### Gmail SMTP

- Configuración de conexión SMTP.
- Envío real de correo confirmado.
- Recepción en la bandeja de entrada verificada.
- Envío automático confirmado.

### WhatsApp - Evolution API

- Evolution API instalada mediante Docker.
- Instancia `notifysaas` configurada y conectada.
- Servicio `WhatsAppDeliveryService` implementado.
- Integración con el comando `messages:send`.
- Validación de empresa, cliente y conexión.
- Normalización de números telefónicos colombianos.
- Envío real confirmado.
- Recepción del mensaje en WhatsApp verificada.
- Envío automático mediante Laravel Scheduler confirmado.
- Registro correcto del identificador de Evolution API.
- Recordatorio de una sola ejecución finalizado sin duplicados.

## Pruebas automatizadas

Resultado de `php artisan test`:

- 22 pruebas aprobadas.
- 125 aserciones exitosas.
- 0 pruebas fallidas.

Se incluyen pruebas de:

- Seguridad y aislamiento entre empresas.
- Configuración de conexiones.
- Gestión de clientes.
- Procesamiento de recordatorios.
- Prevención de duplicados.
- Recuperación de mensajes.
- Envíos de Telegram.
- Envíos de correo.
- Servicio de WhatsApp.
- Procesamiento de WhatsApp exitoso y fallido.

Las pruebas de WhatsApp utilizan respuestas HTTP simuladas para evitar envíos reales durante su ejecución.

## Seguridad

- Credenciales almacenadas fuera del repositorio.
- Archivo `.env` no rastreado por Git.
- Validación de pertenencia de registros a la empresa.
- Verificación de clientes y conexiones activas.
- Manejo de errores durante los envíos.
- Credenciales de conexiones protegidas mediante cifrado en el modelo.

## Evidencias funcionales

El 6 de noviembre de 2026 se comprobó:

- Telegram: envío real y automático.
- Gmail: envío real y automático.
- WhatsApp: envío real y automático.
- WhatsApp: registro exitoso en el historial.
- WhatsApp: un único mensaje generado por cada recordatorio de prueba.
- Recordatorio automático de WhatsApp finalizado con estado `completed` y sin próxima ejecución.

## Pendientes

- Revisión funcional final del sistema.
- Documentación de instalación y despliegue.
- Revisión de seguridad para un entorno de producción.
- Preparación de las evidencias y sustentación académica.

Nota: la integración actual de WhatsApp utiliza Evolution API con Baileys. Antes de un uso comercial o productivo se deben evaluar las condiciones, restricciones y riesgos de este mecanismo no oficial.
