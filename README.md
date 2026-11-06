# NotifySaaS

### Sistema SaaS de Recordatorios Multicanal

**Proyecto académico — IS873, Laboratorio de Software**  
**Universidad Tecnológica de Pereira (UTP)**  
**Docente:** Juan Manuel Mejía Naranjo  
**Pereira, Colombia — noviembre de 2026**

**Integrantes iniciales:** Jaime Andrés Villaneda Uribe y Yeison Andrés Jaramillo Carvajal.  
**Responsable actual de la continuidad:** Yeison Andrés Jaramillo Carvajal.

> **Estado:** versión funcional de demostración local. Se han implementado y probado el registro de empresas, la autenticación, controles de aislamiento multiempresa, la gestión de clientes y plantillas, los recordatorios y la mensajería por Telegram, Gmail SMTP y WhatsApp mediante Evolution API. Los tres canales cuentan con envíos reales y automáticos confirmados. La última ejecución automatizada obtuvo **22 pruebas aprobadas y 125 aserciones**. Continúan pendientes el despliegue público, la revisión de seguridad para producción y la validación formal de todos los criterios académicos de aceptación.

## Inicio rápido (Windows / PowerShell)

### 1. Requisitos

- PHP **8.3 o superior**, con la extensión SQLite habilitada.
- Composer.
- Git.
- Un navegador web.
- Para la integración local de WhatsApp: **Docker Desktop** y una instancia de **Evolution API** configurada y conectada.
- Para los otros canales: credenciales propias de Telegram Bot API o de un servidor SMTP, según corresponda.

No es necesario instalar React, MySQL ni ejecutar `npm run dev` para utilizar el panel administrativo actual. La interfaz web la proporciona **Filament**.

### 2. Descargar el proyecto

```powershell
git clone https://github.com/JaimeVillaneda/NotifySaas.git
cd NotifySaas
git switch fix/us-03-aislamiento
```

La rama `fix/us-03-aislamiento` contiene los avances descritos en este documento. **No se debe asumir que ya están fusionados con `main`.**

### 3. Instalar y configurar el entorno local

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Revisar al menos los siguientes valores del archivo `.env`:

```dotenv
APP_NAME=NotifySaaS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
```

Esta configuración es exclusivamente para desarrollo local. **No utilizar `APP_DEBUG=true` en producción.**

Si aún no existe el archivo de SQLite, crearlo con PowerShell:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
```

Preparar las tablas y los recursos visuales:

```powershell
php artisan migrate
php artisan filament:assets
```

> No ejecutar `migrate:fresh` sobre una base de datos con información que se quiera conservar: elimina y vuelve a crear las tablas.

### 4. Crear una cuenta local de acceso

```powershell
php artisan make:filament-user
```

Ingresar un nombre, un correo electrónico y una contraseña propios para la demostración local. El acceso al panel depende de la configuración de los usuarios y sus permisos, incluido el campo `is_admin` utilizado por el proyecto.

**Importante:** una cuenta administradora sin empresa asociada (`company_id = null`) se utiliza provisionalmente como administradora de la plataforma. Esta solución es adecuada solo para el contexto de demostración actual y **no sustituye un sistema definitivo de roles y permisos**.

Las credenciales deben mantenerse privadas. No se incluyen contraseñas, tokens ni claves API reales en este README.

### 5. Ejecutar la aplicación

```powershell
php artisan serve
```

Abrir **http://127.0.0.1:8000/admin** e iniciar sesión con una cuenta autorizada.

Para detener el servidor, presionar `Ctrl + C` en su terminal.

### 6. Configurar las conexiones de mensajería

Desde el panel de administración, configurar las conexiones correspondientes a los canales que se van a usar:

| Canal | Configuración necesaria |
|---|---|
| Telegram | Bot y token de Telegram Bot API; destinatario con identificador de chat válido. |
| Gmail SMTP | Servidor SMTP y credenciales autorizadas para enviar correo. |
| WhatsApp | Evolution API accesible, instancia conectada, URL del servicio y clave API. |

Para las pruebas locales se utilizó Evolution API en Docker y una instancia denominada `notifysaas`. Los valores de conexión son propios de cada entorno: **no deben copiarse contraseñas ni API Keys al repositorio**. Evolution API se instala y opera como servicio independiente del código Laravel.

La integración actual de WhatsApp utiliza **Baileys**, un mecanismo no oficial basado en WhatsApp Web; antes de un uso real a escala comercial deben revisarse las restricciones del proveedor y los riesgos de suspensión de cuentas.

### 7. Ejecutar las pruebas automatizadas

```powershell
php artisan test
```

**Última ejecución local registrada (6 de noviembre de 2026):** **22 pruebas aprobadas, 125 aserciones y 0 fallos**.

Las pruebas comprueban distintos escenarios de la aplicación, como el aislamiento entre empresas, la configuración de conexiones, la generación y recuperación de mensajes, y el procesamiento de envíos por Telegram, correo y WhatsApp. Las pruebas que simulan proveedores externos no realizan envíos reales.

> Este resultado no significa que se haya certificado formalmente cada criterio de aceptación de las historias US-01 a US-04 ni que el sistema esté listo para producción.

### 8. Ejecutar los recordatorios automáticamente

El proyecto contiene los siguientes comandos de consola:

```powershell
php artisan reminders:process
php artisan messages:send
php artisan messages:recover
```

- `reminders:process`: identifica recordatorios elegibles y genera registros de envío pendientes; **no realiza el envío externo por sí mismo**.
- `messages:send`: procesa los mensajes pendientes de Telegram, correo y WhatsApp, e informa el resultado en el historial.
- `messages:recover`: revisa envíos que permanecieron interrumpidos en `processing`, sin reenviarlos automáticamente.

Para ejecutar las tareas programadas en el entorno local:

```powershell
php artisan schedule:work
```

El programador de Laravel está configurado para procesar recordatorios y mensajes cada minuto y revisar interrupciones cada cinco minutos. Se detiene con `Ctrl + C`.

**Precaución:** `messages:send` y `schedule:work` pueden producir **envíos reales** a los destinatarios configurados. Usarlos únicamente con conexiones activas, destinatarios autorizados y mensajes pendientes previamente revisados.

---

## 1. Planteamiento del proyecto

NotifySaaS busca ayudar a las empresas a organizar recordatorios sobre citas, pagos, vencimientos y renovaciones, reduciendo tareas manuales y facilitando el seguimiento de las notificaciones.

El sistema se plantea como una solución **SaaS multiempresa**: varias organizaciones utilizan una misma aplicación, pero cada una debe consultar y administrar únicamente la información que le corresponde.

La implementación actual dispone de un panel administrativo local, programación de recordatorios, procesamiento de mensajes e integración comprobada con **Telegram, Gmail SMTP y WhatsApp Evolution API**. No obstante, las pruebas de funcionamiento realizadas en desarrollo no equivalen a una validación integral de seguridad o a un despliegue público.

## 2. Objetivos

### Objetivo general

Desarrollar una plataforma SaaS multiempresa que permita administrar destinatarios, crear y programar recordatorios y enviar notificaciones mediante distintos canales, incorporando controles de acceso y trazabilidad de los mensajes.

### Objetivos específicos

1. Administrar empresas y usuarios autorizados.
2. Permitir el inicio y cierre de sesión.
3. Restringir el acceso y la modificación de los datos a la empresa correspondiente.
4. Registrar, consultar, actualizar y gestionar el estado de los clientes destinatarios.
5. Crear plantillas y programar recordatorios de una sola ejecución o según las opciones disponibles.
6. Enviar notificaciones por Telegram, correo SMTP y WhatsApp.
7. Mantener un historial de resultados y evitar duplicados en recordatorios de una sola ejecución.
8. Ampliar progresivamente la cobertura de pruebas y la seguridad para un posible despliegue.

## 3. Actores y separación de información

| Actor | Función prevista o actual |
|---|---|
| Administrador de plataforma | Administra el entorno general; el esquema actual basado en una cuenta sin empresa es provisional. |
| Administrador de empresa | Gestiona los registros autorizados de su organización. |
| Cliente destinatario | Recibe notificaciones; no necesita entrar al panel administrativo. |
| Proceso automático | Identifica recordatorios elegibles, genera despachos y procesa envíos mediante Laravel Scheduler. |
| Proveedores externos | Telegram Bot API, servidor SMTP y Evolution API para entregar mensajes según el canal. |

**Aislamiento multiempresa:** la información de una organización no debe ser visible ni modificable por otra. La historia US-03 incorpora controles en recursos del panel, políticas y validaciones del servidor; se han realizado pruebas automatizadas y manuales en escenarios intervenidos. Aún corresponde verificar formalmente todos los módulos y accesos directos contemplados en los criterios de aceptación.

## 4. Alcance del primer sprint (Nota 2)

El equipo comprometió las siguientes historias del Product Backlog de la Nota 1:

| Historia | Descripción | Prioridad | Estimación | Estado de revisión académica |
|---|---|---|---:|---|
| **US-01** | Registro de empresa y administrador | Must | 3 SP | Funcionalidad implementada y utilizada; corresponde reunir la evidencia formal de todos los criterios. |
| **US-02** | Inicio y cierre de sesión | Must | 3 SP | Autenticación implementada y utilizada; corresponde validar formalmente el cierre de sesión y el rechazo de credenciales incorrectas. |
| **US-03** | Aislamiento de información entre empresas | Must | 5 SP | Controles y pruebas en módulos intervenidos; falta cerrar la revisión exhaustiva de accesos y operaciones. |
| **US-04** | Gestión de clientes | Must | 5 SP | Registro, consulta y edición utilizados; corresponde completar la validación formal de desactivación. |
| **Total** | **4 historias comprometidas** | | **16 SP** | **No equivale automáticamente a 16 puntos aceptados.** |

Las historias **US-05 a US-21 no fueron comprometidas originalmente en este Sprint 1** y permanecen como parte del backlog planificado. Se implementaron adicionalmente funcionalidades de recordatorios, plantillas e integraciones de mensajes; esto no implica que todas esas historias hayan sido formalmente aceptadas. Las historias clasificadas como *Won't* conservan su clasificación original fuera del MVP inicial.

### Criterios principales de aceptación

- **US-01:** registrar una empresa y asociarle un administrador con datos válidos.
- **US-02:** iniciar y cerrar sesión, rechazando credenciales incorrectas.
- **US-03:** impedir el acceso no autorizado a datos de otra empresa, incluso mediante enlaces directos.
- **US-04:** crear, consultar, editar y desactivar clientes de la empresa correspondiente.

### Definition of Done

Una historia se considera terminada cuando funciona en la aplicación, cumple todos sus criterios de aceptación, cuenta con evidencia verificable, puede demostrarse y tiene su código versionado. Un formulario visible o una única prueba manual no son suficientes para declarar una historia completa.

## 5. Módulos existentes y funcionamiento actual

| Módulo | Situación actual |
|---|---|
| Empresas y usuarios | Recursos administrativos disponibles, con controles de acceso desarrollados. |
| Autenticación | Panel Filament en `/admin`, protegido por inicio de sesión. |
| Clientes | Registro, consulta, edición y estado de activación disponibles; falta completar evidencia formal de todos los criterios de US-04. |
| Plantillas de mensajes | Creación y administración de plantillas con controles de pertenencia empresarial. |
| Recordatorios | Creación y programación; ejecución automática comprobada y finalización de recordatorios de una sola vez sin duplicados en los casos probados. |
| Historial de envíos | Registro del canal, estado, intentos, fecha de envío, respuesta del proveedor y errores. |
| Conexiones externas | Configuración utilizada para Telegram Bot API, Gmail SMTP y WhatsApp Evolution API. |
| Envío real de mensajes | **Verificado manualmente y de forma automática** en Telegram, Gmail SMTP y WhatsApp. |

### Flujo de uso que se puede demostrar actualmente

1. Iniciar sesión como administrador autorizado.
2. Consultar registros pertenecientes a la empresa correspondiente.
3. Registrar o modificar un cliente destinatario.
4. Crear o seleccionar una plantilla.
5. Configurar la conexión del canal elegido.
6. Crear un recordatorio asociado a un único destinatario autorizado.
7. Ejecutar una prueba controlada o activar Laravel Scheduler para procesarlo automáticamente.
8. Verificar la recepción y comprobar el resultado en el historial.
9. Confirmar que un recordatorio de una sola ejecución cambia a `completed` y queda sin próxima ejecución.

**Pendiente en US-03:** completar la revisión formal de accesos directos y operaciones sobre recursos de otras empresas, incluso en Historial y Conexiones, según los criterios del curso. La presencia de filtros no demuestra, por sí sola, aislamiento completo en todos los escenarios.

### Procesamiento técnico de recordatorios

El flujo implementado separa tres responsabilidades:

1. **Generación:** `reminders:process` busca recordatorios elegibles y crea registros `pending`.
2. **Envío:** `messages:send` procesa esos registros con el servicio de Telegram, correo o WhatsApp y actualiza su estado a `sent` o `failed`.
3. **Recuperación:** `messages:recover` revisa registros interrumpidos en `processing`; no reenvía por su cuenta.

Laravel Scheduler automatiza estas tareas. En las pruebas reales del 6 de noviembre de 2026 se confirmaron envíos por los tres canales y, para WhatsApp, la finalización de un recordatorio de una sola ejecución con un único despacho registrado.

## 6. Tecnologías y estructura

### Tecnologías utilizadas actualmente

| Tecnología | Uso |
|---|---|
| **PHP 8.3+** | Lenguaje base de la aplicación. |
| **Laravel 13** | Backend, modelos, migraciones, servicios, comandos y programación de tareas. |
| **Filament 5** | Panel administrativo, formularios y tablas. |
| **SQLite** | Base de datos del entorno local y pruebas. |
| **Composer** | Administración de dependencias PHP. |
| **Pest** | Pruebas automatizadas. |
| **Laravel Scheduler** | Ejecución programada de recordatorios, envíos y recuperación. |
| **Telegram Bot API** | Envío de mensajes por Telegram. |
| **Gmail SMTP** | Envío de correo electrónico en el entorno de prueba. |
| **Evolution API** | Envío de mensajes de WhatsApp mediante una instancia configurada. |
| **Docker Desktop** | Ejecución local de Evolution API y sus servicios auxiliares. |
| **Git y GitHub** | Control de versiones y evidencias de desarrollo. |

**Evolución de la propuesta:** en la Nota 1 se consideraron React y MySQL como opciones tecnológicas. La implementación demostrada usa **Filament y SQLite**. Las integraciones con Telegram, SMTP y WhatsApp ya fueron verificadas en el entorno local.

### Estructura básica del repositorio

```text
app/
  Console/Commands/             # Procesamiento, envio y recuperacion
  Filament/Resources/           # Modulos administrativos
  Models/                      # Modelos de datos
  Policies/                    # Autorizacion de operaciones
  Providers/                   # Configuracion de servicios y panel
  Services/                    # Integraciones de mensajeria

database/
  migrations/                  # Tablas y relaciones

routes/
  console.php                  # Tareas de Laravel Scheduler

tests/
  Feature/                     # Pruebas funcionales y de integracion simulada
  Unit/                        # Pruebas unitarias

docs/
  AVANCES.md                   # Estado actualizado y evidencias

README.md                       # Guia principal del proyecto
composer.json                   # Dependencias PHP
.env.example                    # Ejemplo de configuracion local
```

## 7. Evidencias y control de versiones

**Repositorio:** https://github.com/JaimeVillaneda/NotifySaas  
**Rama de trabajo presentada:** `fix/us-03-aislamiento`

### Commits representativos

- [`13f5f56`](https://github.com/JaimeVillaneda/NotifySaas/commit/13f5f5667e): restricciones iniciales en usuarios y empresas.
- [`9ef79b9`](https://github.com/JaimeVillaneda/NotifySaas/commit/9ef79b960b): aislamiento en empresas y clientes.
- [`49106d7`](https://github.com/JaimeVillaneda/NotifySaas/commit/49106d7470): aislamiento de plantillas.
- [`5286619`](https://github.com/JaimeVillaneda/NotifySaas/commit/52866194b5): aislamiento de recordatorios.
- [`bd46d6a`](https://github.com/JaimeVillaneda/NotifySaas/commit/bd46d6a): integración de WhatsApp mediante Evolution API, pruebas y documentación.

### Pruebas realizadas

- **Automatizadas:** 22 aprobadas, 125 aserciones y 0 fallos, según `php artisan test` del 6 de noviembre de 2026.
- **Telegram:** envío real recibido y ejecución automática confirmada.
- **Gmail SMTP:** correo real recibido y ejecución automática confirmada.
- **WhatsApp Evolution API:** mensaje real recibido y ejecución automática confirmada.
- **Recordatorios:** generación de registros pendientes, procesamiento y actualización a `completed` para recordatorios de una sola ejecución.
- **Historial:** registro correcto de estado, intentos, fecha de envío y respuesta del proveedor.
- **Pruebas de seguridad:** validaciones automatizadas de pertenencia empresarial y escenarios de aislamiento.
- **Pendientes:** cobertura formal integral de las historias de usuario, pruebas de seguridad adicionales, despliegue y operación en producción.

### Evidencia específica de WhatsApp

El **6 de noviembre de 2026** se verificó un envío real y otro automático de WhatsApp. En ambos casos se confirmó la recepción en el teléfono destinatario. En la prueba automática, el despacho terminó en `sent`, con **1 intento**, y el recordatorio quedó en `completed`, con `next_run_at = null` y sin duplicados.

Las pruebas automatizadas de servicios externos utilizan simulaciones de respuestas HTTP o de servicios de correo; sus resultados no deben confundirse con las evidencias manuales de recepción real.

## 8. Restricciones, seguridad y pendientes

- El proyecto funciona en un **entorno local de demostración**; no se ha realizado un despliegue público.
- El aislamiento multiempresa requiere una revisión exhaustiva adicional de permisos, acciones y accesos directos.
- El administrador de plataforma basado en `company_id = null` es una solución provisional.
- La asignación predeterminada de `is_admin` debe revisarse antes de habilitar altas públicas o gestión de usuarios en producción.
- El archivo `.env`, las contraseñas, los tokens, las claves API y los datos personales deben permanecer fuera del repositorio.
- En producción deben reforzarse la validación de direcciones de servicios externos, la protección de credenciales, la supervisión y el tratamiento de fallos y reintentos.
- El estado `sent` indica que el proveedor aceptó el mensaje; **no garantiza por sí solo la entrega o lectura**. En las pruebas mencionadas la recepción se comprobó directamente.
- El mecanismo de WhatsApp utilizado, Evolution API con Baileys, **no es la API oficial de WhatsApp Business**. Deben evaluarse sus condiciones de uso, la estabilidad y el riesgo de restricciones de cuenta antes de utilizarlo comercialmente.
- Las pruebas de envío deben realizarse solo con destinatarios propios o que hayan autorizado recibir los mensajes.
- Si alguna contraseña de demostración estuvo publicada anteriormente en el README, debe **cambiarse** donde siga vigente: retirarla del archivo actual no la elimina del historial de Git.

### Próximas actividades

1. Completar la validación formal de todos los criterios de aceptación del Sprint 1.
2. Ampliar pruebas de autorización, manejo de errores y recuperación de mensajes.
3. Preparar evidencias visuales y guion para la sustentación académica.
4. Documentar un procedimiento reproducible de instalación de Evolution API y de configuración de los canales, sin revelar credenciales.
5. Evaluar requisitos y seguridad de un eventual despliegue público.

## 9. Documentación complementaria

La documentación de análisis y la planificación original se conservan como antecedentes de la **Nota 1**. Este README reúne el alcance del **Sprint 1 (Nota 2)** y los avances técnicos adicionales verificados posteriormente, sin confundir funcionalidades implementadas con historias formalmente aceptadas.

| Documento | Contenido |
|---|---|
| Propuestas de proyecto, Lab Software | Planteamiento, problema y alcance inicial. |
| NotifySaaS_Casos_de_Uso_y_Requerimientos | Casos de uso y requisitos funcionales y no funcionales. |
| NotifySaaS_Backlog_Priorizado_Nota1 | Historias US-01 a US-21, prioridades, estimaciones y trazabilidad. |
| NotifySaaS_PoC_Funcional | Prueba de concepto anterior, independiente de la aplicación Laravel. |
| `docs/AVANCES.md` | Registro de las funcionalidades y pruebas verificadas en la aplicación actual. |

La prueba de concepto de la Nota 1 no debe confundirse con las pruebas automatizadas ni con los mensajes reales comprobados en la aplicación Laravel actual.

---

**NotifySaaS — Universidad Tecnológica de Pereira — 2026**  
*README actualizado el 6 de noviembre de 2026. Distingue el alcance académico del Sprint 1, los avances funcionales comprobados y las actividades todavía pendientes.*
