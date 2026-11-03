# NotifySaaS

### Sistema SaaS de Recordatorios Multicanal

**Proyecto académico — IS873, Laboratorio de Software**  
**Universidad Tecnológica de Pereira (UTP)**  
**Docente:** Juan Manuel Mejía Naranjo  
**Pereira, Colombia — noviembre de 2026**

**Integrantes iniciales:** Jaime Andrés Villaneda Uribe y Yeison Andrés Jaramillo Carvajal.  
**Responsable actual de la continuidad:** Yeison Andrés Jaramillo Carvajal.

> **Estado:** primer incremento funcional en desarrollo y validación local. El Sprint 1 se concentra en US-01 a US-04. El envío real de mensajes y el despliegue público todavía no están comprobados.

## Inicio rápido (Windows / PowerShell)

### 1. Requisitos

- PHP **8.3 o superior**, con extensión SQLite habilitada.
- Composer.
- Git.
- Un navegador web.

No es necesario instalar React, MySQL ni ejecutar `npm run dev` para utilizar el panel administrativo actual. La interfaz la proporciona **Filament**.

### 2. Descargar el proyecto

```powershell
git clone https://github.com/JaimeVillaneda/NotifySaas.git
cd NotifySaas
git switch fix/us-03-aislamiento
```

La rama `fix/us-03-aislamiento` contiene el avance de US-03 que se presenta en esta entrega. No se debe asumir que esos cambios ya están fusionados con `main`.

### 3. Instalar y configurar el entorno local

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

En el archivo `.env`, revisar al menos estos valores:

```dotenv
APP_NAME=NotifySaaS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
```

La configuración de desarrollo usa SQLite. Si aún no existe el archivo de la base de datos, crearlo con PowerShell:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
```

A continuación, preparar las tablas y los recursos visuales:

```powershell
php artisan migrate
php artisan filament:assets
```

> No ejecutar `migrate:fresh` sobre una base de datos que contenga información que se quiera conservar: ese comando elimina y vuelve a crear las tablas.

### 4. Crear una cuenta local de acceso

```powershell
php artisan make:filament-user
```

Ingresar un nombre, un correo y una contraseña propios para la demostración local. La aplicación utiliza el campo `is_admin` para determinar quién puede entrar al panel.

**Importante:** en la implementación actual, una cuenta administradora sin empresa asociada (`company_id = null`) se utiliza provisionalmente como administradora de la plataforma. Es un mecanismo temporal de demostración, no un sistema de roles definitivo.

Las credenciales deben mantenerse privadas. **No se incluyen contraseñas de prueba en este README.**

### 5. Ejecutar la aplicación

```powershell
php artisan serve
```

Abrir **http://127.0.0.1:8000/admin** e iniciar sesión con la cuenta creada.

Para detener el servidor, presionar `Ctrl + C` en la terminal donde se está ejecutando.

### 6. Ejecutar las pruebas automatizadas

```powershell
php artisan test
```

**Última ejecución local registrada (3 de noviembre de 2026):** 2 pruebas aprobadas, 3 aserciones, sin fallos. Las pruebas actuales comprueban una condición unitaria de ejemplo y la redirección de `/` a `/admin`; **todavía no cubren automáticamente todos los criterios de US-01 a US-04**.

---

## 1. Planteamiento del proyecto

NotifySaaS busca ayudar a las empresas a organizar recordatorios sobre citas, pagos, vencimientos y renovaciones, reduciendo las tareas manuales y facilitando el seguimiento de los avisos.

El sistema se plantea como una solución **SaaS multiempresa**: varias organizaciones podrán utilizar la misma aplicación, pero cada una debe consultar y administrar únicamente la información que le corresponde.

La aplicación web ya cuenta con un panel administrativo local. El procesamiento y el envío real por canales externos forman parte de etapas posteriores y todavía no deben presentarse como funcionalidades completamente verificadas.

## 2. Objetivos

### Objetivo general

Desarrollar progresivamente una plataforma SaaS que permita administrar destinatarios y programar recordatorios para diferentes empresas, con controles de acceso y la futura integración de canales de comunicación.

### Objetivos específicos

1. Administrar empresas y usuarios autorizados.
2. Permitir el inicio y cierre de sesión.
3. Restringir las consultas y modificaciones de datos a la empresa correspondiente.
4. Registrar y actualizar los clientes que recibirán los avisos.
5. Incorporar progresivamente la programación, el procesamiento, el envío y el historial de recordatorios.

## 3. Actores y separación de información

| Actor | Función prevista o actual |
|---|---|
| Administrador de plataforma | Administra el entorno general. Su tratamiento actual mediante una cuenta sin empresa es provisional. |
| Administrador de empresa | Gestiona los registros autorizados de su organización. |
| Cliente destinatario | Persona que eventualmente recibirá un aviso; no necesita entrar al panel. |
| Proceso automático | Identifica recordatorios pendientes y prepara registros para su posterior envío. |
| Proveedores externos | Telegram y un servidor SMTP, previstos para enviar mensajes cuando se complete la integración. |

**Aislamiento multiempresa:** la información de una organización no debe ser visible ni modificable por otra. La historia US-03 implementa controles en los recursos del panel, las políticas y algunas validaciones del servidor. Aún se requieren pruebas adicionales y la revisión de módulos pendientes.

## 4. Alcance del primer sprint (Nota 2)

El equipo comprometió las siguientes historias del Product Backlog de la Nota 1:

| Historia | Descripción | Prioridad | Estimación | Estado actual |
|---|---|---|---:|---|
| **US-01** | Registro de empresa y administrador | Must | 3 SP | Creación desde el panel disponible; faltan verificaciones del registro completo. |
| **US-02** | Inicio y cierre de sesión | Must | 3 SP | Inicio de sesión probado; completar evidencia formal de todos los criterios. |
| **US-03** | Aislamiento de información entre empresas | Must | 5 SP | Controles y pruebas manuales en diversos módulos; aún parcial. |
| **US-04** | Gestión de clientes | Must | 5 SP | Creación, consulta y edición probadas; falta validar la desactivación. |
| **Total** | **4 historias comprometidas** | | **16 SP** | **No equivale a 16 puntos aceptados.** |

Las historias **US-05 a US-21 no fueron comprometidas en este Sprint 1** y permanecen en el backlog. Algunas pantallas y componentes relacionados con historias posteriores ya existen, pero eso no significa que se hayan completado o aceptado dichas historias. Las historias clasificadas como *Won't* conservan su clasificación original fuera del MVP inicial.

### Criterios principales de aceptación

- **US-01:** registrar una empresa y asociarle un administrador con datos válidos.
- **US-02:** iniciar y cerrar sesión, rechazando credenciales incorrectas.
- **US-03:** impedir el acceso no autorizado a datos de otra empresa, incluso por enlaces directos.
- **US-04:** crear, consultar, editar y desactivar clientes de la empresa correspondiente.

### Definition of Done

Una historia se considerará terminada cuando funcione en la aplicación, cumpla todos sus criterios de aceptación, cuente con evidencia verificable, pueda demostrarse y tenga su código versionado. Un formulario visible o una prueba manual aislada no son suficientes para declarar una historia completa.

## 5. Módulos existentes y funcionamiento actual

| Módulo | Situación actual |
|---|---|
| Empresas y usuarios | Recursos administrativos disponibles, con controles de acceso desarrollados. |
| Autenticación | Panel Filament en `/admin`, protegido por inicio de sesión. |
| Clientes | Registro, consulta y edición comprobados manualmente. |
| Plantillas de mensajes | Recurso disponible; se incorporaron controles de pertenencia empresarial. |
| Recordatorios | Creación y edición local; separación en el listado por empresa comprobada. |
| Historial de envíos | Estructura disponible; seguridad y funcionamiento completos pendientes de validar. |
| Conexiones externas | Configuración prevista para canales; aislamiento y conexiones reales pendientes de validar. |
| Envío real de mensajes | **No verificado** mediante Telegram ni SMTP. |

### Flujo de uso que se puede demostrar actualmente

1. Iniciar sesión como administrador autorizado.
2. Consultar los registros de la empresa correspondiente.
3. Registrar o modificar clientes de esa empresa.
4. Crear o editar una plantilla de mensaje.
5. Crear un recordatorio de prueba, manteniéndolo **inactivo/pausado** para no producir envíos no previstos.
6. Cambiar de cuenta y comprobar que los registros de una empresa no aparecen en el listado de otra, en los módulos donde ya se incorporaron filtros.

**Pendiente en US-03:** completar la prueba de acceso directo a un recordatorio ajeno y las revisiones de Historial de envíos y Conexiones. La implementación del filtro de un listado no demuestra por sí sola el aislamiento completo de toda la aplicación.

### Procesamiento técnico de recordatorios

El repositorio incluye el comando:

```powershell
php artisan reminders:process
```

Este comando busca recordatorios activos y pendientes que sean elegibles y **genera registros de despacho con estado `pending`**. **No envía por sí mismo mensajes reales** a Telegram ni a un servidor SMTP.

No es necesario ejecutar este comando para la demostración de US-01 a US-04. Solo debe probarse en un entorno controlado, con datos de prueba y entendiendo que puede crear registros en la base de datos.

## 6. Tecnologías y estructura

### Tecnologías utilizadas actualmente

| Tecnología | Uso |
|---|---|
| **PHP 8.3+** | Lenguaje requerido por el proyecto. |
| **Laravel 13** | Backend, modelos, migraciones, rutas y reglas de negocio. |
| **Filament 5** | Panel administrativo, formularios y tablas. |
| **SQLite** | Base de datos del entorno local de desarrollo y pruebas. |
| **Composer** | Administración de dependencias PHP. |
| **Pest** | Pruebas automatizadas. |
| **Git y GitHub** | Control de versiones y evidencias de desarrollo. |

**Evolución de la propuesta:** en la Nota 1 se consideraron React y MySQL como opciones tecnológicas. La implementación que se está demostrando utiliza **Filament y SQLite**. Telegram Bot API y SMTP siguen siendo integraciones previstas y no un servicio de envío real ya validado.

### Estructura básica del repositorio

```text
app/
  Console/Commands/              # Procesamiento técnico de recordatorios
  Filament/Resources/            # Panel y módulos administrativos
  Models/                       # Modelos de datos
  Policies/                     # Autorización de operaciones
  Providers/                    # Configuración de servicios y panel

database/
  migrations/                   # Definición de tablas y relaciones

tests/
  Feature/                      # Pruebas de funcionamiento HTTP
  Unit/                         # Pruebas unitarias

README.md                        # Guía principal del proyecto
composer.json                    # Dependencias PHP
.env.example                     # Ejemplo de configuración local
```

## 7. Evidencias y control de versiones

**Repositorio:** https://github.com/JaimeVillaneda/NotifySaas  
**Rama de trabajo presentada:** `fix/us-03-aislamiento`

Commits principales del avance de US-03:

- [`13f5f56`](https://github.com/JaimeVillaneda/NotifySaas/commit/13f5f5667e): restricciones iniciales en usuarios y empresas.
- [`9ef79b9`](https://github.com/JaimeVillaneda/NotifySaas/commit/9ef79b960b): aislamiento en empresas y clientes.
- [`49106d7`](https://github.com/JaimeVillaneda/NotifySaas/commit/49106d7470): aislamiento de plantillas.
- [`5286619`](https://github.com/JaimeVillaneda/NotifySaas/commit/52866194b5): aislamiento de recordatorios.

### Pruebas realizadas

- **Automatizadas:** 2 aprobadas y 3 aserciones, según `php artisan test` ejecutado el 3 de noviembre de 2026.
- **Manuales:** ingreso con usuarios administradores, registro y modificación de clientes, y comprobaciones de visibilidad de datos por empresa en módulos intervenidos.
- **Aún pendientes:** pruebas automatizadas específicas de US-01 a US-04, cobertura de todas las operaciones de autorización y casos completos de desactivación y acceso directo en recordatorios.

## 8. Restricciones, seguridad y pendientes

- El sistema se ejecuta **localmente**. No se ha completado el despliegue público.
- La seguridad multiempresa de US-03 está **en desarrollo**; quedan módulos y escenarios sin verificar.
- La cuenta administradora de plataforma identificada por `company_id = null` es **una solución provisional**, no un esquema de roles listo para producción.
- La asignación predeterminada de `is_admin` en las migraciones requiere revisión antes de permitir registros públicos o usuarios no administradores.
- Se deben configurar los canales externos, proteger sus credenciales y comprobar los envíos reales antes de afirmar que Telegram o SMTP funcionan de extremo a extremo.
- La programación temporal y los reintentos necesitan validaciones adicionales.
- No publicar el archivo `.env`, datos personales, credenciales de acceso ni bases de datos locales con información sensible.
- Si alguna contraseña de demostración estuvo publicada anteriormente en el README, debe **cambiarse** en las cuentas donde siga vigente: retirarla del texto actual no la elimina del historial de Git.

## 9. Documentación complementaria

La documentación de análisis y la planificación original se mantienen como antecedentes de la **Nota 1**; este README describe el estado de la implementación correspondiente a la **Nota 2**.

| Documento | Contenido |
|---|---|
| Propuestas de proyecto, Lab Software | Planteamiento, problema y alcance inicial. |
| NotifySaaS_Casos_de_Uso_y_Requerimientos | Casos de uso y requisitos funcionales y no funcionales. |
| NotifySaaS_Backlog_Priorizado_Nota1 | Historias US-01 a US-21, prioridades, estimaciones y trazabilidad. |
| NotifySaaS_PoC_Funcional | Prueba de concepto anterior, independiente de la aplicación Laravel. |

La prueba de concepto de la Nota 1 no debe confundirse con pruebas automatizadas o envíos reales ya comprobados en la aplicación actual.

---

**NotifySaaS — Universidad Tecnológica de Pereira — 2026**  
*Documento actualizado para la entrega académica del Sprint Review 1. Describe avances comprobados y distingue expresamente las funcionalidades pendientes.*
