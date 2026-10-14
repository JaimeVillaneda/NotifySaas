
## Estado tecnico del proyecto


admin@example.com
password123


Esta aplicacion Laravel implementa un panel administrativo modular con Filament.

URL principal:

```text
http://127.0.0.1:8000/admin
```

Usuario local de prueba:

```text
admin@example.com
password123
```

Modulos disponibles en el menu lateral:

- Administracion: empresas y usuarios.
- Clientes: clientes o destinatarios.
- Mensajeria: plantillas, recordatorios e historial de envios.
- Configuracion: conexiones externas en un solo modulo con pestanas para Telegram, Email SMTP y WhatsApp Evolution API.

Comandos utiles:

```bash
php artisan serve
php artisan migrate
php artisan reminders:process
php artisan test
```

No es necesario ejecutar `npm run dev` para ver el panel actual. El frontend administrativo lo sirve Filament con assets publicados en `public/css/filament` y `public/js/filament`.

Para sustentacion, cada carpeta principal tiene un `README.md` explicando su funcion dentro de Laravel.

<div align="center">

# NotifySaaS

### Sistema SaaS de Recordatorios Multicanal

**Planteamiento y definición del proyecto**

---

**Jaime Andres Villaneda Uribe**  
**Yeison Andrés Jaramillo Carvajal**

IS873 — Laboratorio de Software  
Docente: Juan Manuel Mejia Naranjo

**Universidad Tecnológica de Pereira**  
Pereira, Risaralda  
Octubre de 2026

</div>

---

## Resumen del proyecto

NotifySaaS es una solución web propuesta bajo el modelo Software como Servicio (SaaS), que permite a diferentes empresas administrar la información de sus clientes, crear recordatorios, programar su envío y consultar los resultados de cada intento.

El proyecto surge de la necesidad de reducir las tareas repetitivas y los olvidos asociados con la notificación manual de citas, pagos, vencimientos y renovaciones.

La plataforma busca integrar la programación automática de mensajes, diferentes canales de comunicación y un historial de envíos verificable, manteniendo la independencia de la información de cada empresa.

La primera versión funcional estará enfocada en dos canales:

- **Telegram:** mediante la integración con Telegram Bot API.
- **Correo electrónico:** mediante servicios de envío SMTP.

La integración con WhatsApp se contempla como una posible ampliación futura, dependiendo de la disponibilidad técnica y del tiempo de desarrollo.

Actualmente, el proyecto se encuentra en etapa de análisis y diseño preliminar. Como avance técnico, se dispone de una prueba de concepto local, independiente de la aplicación web definitiva, orientada a comprobar el procesamiento de recordatorios, los reintentos y el registro de eventos.

La integración completa con proveedores externos y el despliegue de la plataforma permanecen pendientes.

---

## Tabla de contenidos

1. [Planteamiento del proyecto](#1-planteamiento-del-proyecto)
2. [Objetivos](#2-objetivos)
3. [Usuarios y sistemas relacionados](#3-usuarios-y-sistemas-relacionados)
4. [Alcance del MVP](#4-alcance-del-mvp)
5. [Funcionamiento general](#5-funcionamiento-general)
6. [Restricciones y consideraciones](#6-restricciones-y-consideraciones)
7. [Tecnologías propuestas](#7-tecnologías-propuestas)
8. [Estado del proyecto](#8-estado-del-proyecto)
9. [Documentación complementaria](#9-documentación-complementaria)

---

## 1. Planteamiento del proyecto

### 1.1 Contexto y problemática

Las empresas necesitan mantener una comunicación oportuna con sus clientes cuando existen compromisos como citas, pagos pendientes, vencimientos de documentos o renovaciones de servicios.

En muchos casos, estas actividades dependen de la revisión manual de agendas y del envío individual de mensajes, lo que genera una carga operativa adicional y aumenta la posibilidad de errores, retrasos u omisiones.

Además, cuando los recordatorios se gestionan mediante herramientas independientes, resulta más difícil consultar el historial de comunicaciones y verificar si un mensaje fue procesado correctamente.

### 1.2 Solución propuesta

NotifySaaS plantea centralizar estas actividades mediante una plataforma web en la que cada empresa pueda administrar sus clientes y programar recordatorios.

El sistema permitirá registrar un mensaje, seleccionar el destinatario, establecer una fecha y hora de envío y elegir el canal de comunicación.

Posteriormente, un proceso automático identificará los recordatorios que deban ejecutarse y realizará la solicitud de envío mediante el proveedor correspondiente.

Cada operación quedará registrada y asociada exclusivamente a la empresa propietaria de la información.

---

## 2. Objetivos

### 2.1 Objetivo general

Desarrollar una plataforma SaaS que permita a diferentes empresas crear, programar y enviar automáticamente recordatorios a sus clientes mediante distintos canales de comunicación.

### 2.2 Objetivos específicos

1. Implementar mecanismos para el registro y la administración independiente de empresas y clientes.

2. Permitir la creación y programación de recordatorios mediante fecha, hora y canal de comunicación.

3. Automatizar el procesamiento de recordatorios pendientes y gestionar los intentos de envío.

4. Integrar inicialmente Telegram y correo electrónico como canales de notificación.

5. Mantener un historial de recordatorios, estados e intentos que facilite el seguimiento de las comunicaciones.

---

## 3. Usuarios y sistemas relacionados

El funcionamiento de NotifySaaS contempla los siguientes actores:

| Actor | Responsabilidad |
|---|---|
| Administrador de empresa | Administra los clientes de su organización, programa recordatorios y consulta los resultados. |
| Cliente destinatario | Recibe las notificaciones mediante el canal habilitado. No necesita acceder a la plataforma durante el MVP. |
| Planificador automático | Identifica los recordatorios que han alcanzado su fecha y hora programadas. |
| Trabajador automático | Procesa los recordatorios pendientes y gestiona los intentos de envío. |
| Telegram Bot API | Servicio externo encargado de procesar las solicitudes de envío por Telegram. |
| Servidor SMTP | Servicio utilizado para transmitir los mensajes de correo electrónico. |

### 3.1 Separación de información

Al tratarse de una aplicación SaaS orientada a varias empresas, uno de los requisitos principales es garantizar que cada organización acceda únicamente a su información.

Esto comprende:

- Clientes registrados.
- Recordatorios programados.
- Configuración de canales.
- Historial de mensajes.
- Estados e intentos de envío.

Las restricciones de acceso deberán validarse desde el servidor y no depender exclusivamente de la interfaz.

---

## 4. Alcance del MVP

El Producto Mínimo Viable (MVP) se concentra en ofrecer un flujo funcional de programación y seguimiento de recordatorios.

### 4.1 Funcionalidades incluidas

| Módulo | Funcionalidad |
|---|---|
| Empresas | Registro, autenticación y separación de información. |
| Clientes | Creación, actualización y consulta de destinatarios. |
| Recordatorios | Creación de mensajes y programación de envíos. |
| Automatización | Procesamiento de recordatorios pendientes. |
| Notificaciones | Envío mediante Telegram y correo electrónico. |
| Seguimiento | Consulta de estados, intentos e historial de ejecución. |

### 4.2 Funcionalidades fuera del alcance inicial

Las siguientes características no forman parte del MVP:

- Cobro de suscripciones y administración de planes comerciales.
- Aplicaciones móviles nativas.
- Paneles de analítica avanzada.
- Campañas masivas de publicidad o marketing.
- Integración con WhatsApp.

Estas funcionalidades podrán evaluarse en etapas posteriores.

### 4.3 Resultado esperado

El MVP deberá permitir completar el siguiente proceso:

1. Un administrador autorizado accede a la plataforma.
2. Registra o selecciona un cliente.
3. Crea un recordatorio y configura su programación.
4. El sistema identifica el momento establecido para su ejecución.
5. Se realiza el intento de envío mediante el canal seleccionado.
6. El resultado queda almacenado para su posterior consulta.

Es importante distinguir entre la aceptación técnica de un mensaje por parte del proveedor y su recepción o lectura efectiva por el destinatario.

---

## 5. Funcionamiento general

NotifySaaS plantea una arquitectura en la que la aplicación web administra los datos y un proceso independiente se encarga de ejecutar los recordatorios programados.

### 5.1 Flujo de procesamiento

```text
Administrador de empresa
          |
          v
Registro del cliente
          |
          v
Creación del recordatorio
          |
          v
Programación de fecha y hora
          |
          v
Almacenamiento en el sistema
          |
          v
Planificador automático
          |
          v
Procesamiento del recordatorio
          |
          v
Selección del canal
          |
     +----+----+
     |         |
     v         v
 Telegram    Correo
             SMTP
     |         |
     +----+----+
          |
          v
Registro del resultado
          |
          v
Historial y seguimiento
```

### 5.2 Gestión de intentos

El procesamiento deberá contemplar situaciones en las que un proveedor no pueda aceptar una solicitud de envío.

Para ello se propone:

- Registrar cada intento realizado.
- Identificar los errores presentados.
- Controlar los reintentos cuando corresponda.
- Mantener actualizado el estado del recordatorio.
- Evitar procesamientos duplicados cuando sea posible.

Estas características permitirán mejorar la trazabilidad y facilitar la identificación de fallos.

---

## 6. Restricciones y consideraciones

Durante el análisis y desarrollo deberán tenerse en cuenta las siguientes condiciones:

**Desarrollo incremental**

La implementación se realizará de forma progresiva, priorizando las funcionalidades indispensables para el MVP y ajustando el alcance a la capacidad del equipo.

**Servicios externos**

El funcionamiento de Telegram y SMTP depende de la configuración de credenciales válidas, disponibilidad de los proveedores y correcta habilitación de los destinatarios.

**Seguridad y acceso a datos**

Cada empresa debe contar con mecanismos de autenticación y autorización que impidan consultar o modificar información perteneciente a otras organizaciones.

**Tratamiento de información**

Antes de utilizar datos reales deberán revisarse los requisitos relacionados con la autorización de contacto, el tratamiento de datos personales y los períodos de conservación.

**Limitaciones del MVP**

La primera versión estará enfocada en la automatización y trazabilidad de recordatorios, sin incluir funcionalidades comerciales o de marketing avanzado.

---

## 7. Tecnologías propuestas

Para el desarrollo de la aplicación se contempla el siguiente conjunto de tecnologías:

| Tecnología | Uso previsto |
|---|---|
| Laravel | Desarrollo del backend, reglas de negocio y API. |
| React | Construcción de la interfaz web. |
| MySQL | Almacenamiento de empresas, clientes, recordatorios y eventos. |
| Telegram Bot API | Integración para envío de mensajes por Telegram. |
| SMTP | Envío de recordatorios mediante correo electrónico. |
| Procesos programados | Identificación y ejecución de recordatorios pendientes. |

Estas tecnologías corresponden a la propuesta inicial de desarrollo y no deben interpretarse como componentes completamente implementados.

---

## 8. Estado del proyecto

**Fase actual:** Análisis y diseño preliminar.

| Componente | Estado |
|---|---|
| Planteamiento del problema | Documentado |
| Definición de objetivos | Documentado |
| Identificación de actores | Documentado |
| Requisitos y casos de uso | Documentados en anexos |
| Priorización del backlog | Documentada en anexos |
| Prueba de concepto local | Disponible |
| Aplicación web definitiva | Pendiente |
| Integración completa con proveedores | Pendiente |
| Despliegue del sistema | Pendiente |

### 8.1 Prueba de concepto

Como avance técnico se desarrolló una prueba de concepto local e independiente de la aplicación web definitiva.

Su propósito es validar aspectos relacionados con:

- Procesamiento de recordatorios.
- Gestión de intentos.
- Control de reintentos.
- Registro de eventos.
- Comportamiento básico del proceso automático.

La existencia de esta prueba no implica que la plataforma SaaS se encuentre completamente implementada.

---

## 9. Documentación complementaria

El expediente del proyecto se encuentra distribuido en documentos que respaldan las distintas etapas del análisis.

| Anexo | Documento | Contenido |
|---|---|---|
| A | Propuestas de proyecto, Lab Software.docx | Problema original, objetivos, MVP y tecnologías sugeridas. |
| B | NotifySaaS_Casos_de_Uso_y_Requerimientos.xlsx | Casos de uso CU-01 a CU-09 y catálogo inicial de requisitos RF/RNF. |
| C | NotifySaaS_Backlog_Priorizado_Nota1.xlsx | 21 historias de usuario, priorización, sprints y matriz RF–US–CU. |
| D | NotifySaaS_PoC_Funcional.zip | Código, documentación, pruebas y evidencia de ejecución. |

Los anexos conservan las fichas detalladas y la planificación operativa del proyecto.

---

## 10. Equipo de desarrollo

| Integrante | Institución |
|---|---|
| Jaime Andres Villaneda Uribe | Universidad Tecnológica de Pereira |
| Yeison Andrés Jaramillo Carvajal | Universidad Tecnológica de Pereira |

**Asignatura:** IS873 — Laboratorio de Software  
**Docente:** Juan Manuel Mejia Naranjo  
**Institución:** Universidad Tecnológica de Pereira  
**Ubicación:** Pereira, Risaralda, Colombia  
**Fecha:** Octubre de 2026

---

<div align="center">

**NotifySaaS**

Sistema SaaS de Recordatorios Multicanal

Proyecto académico — Universidad Tecnológica de Pereira

2026

</div>
