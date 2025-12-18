# Sistema de Gestión Integral - Gimnasio Viviana Woods

![Estado del Proyecto](https://img.shields.io/badge/Estado-Finalizado-brightgreen)
![PHP](https://img.shields.io/badge/Backend-PHP-blue)
![MySQL](https://img.shields.io/badge/DB-MySQL-orange)
![Hosting](https://img.shields.io/badge/Despliegue-InfinityFree-purple)

## 📄 Descripción

Plataforma web desarrollada para la digitalización completa de los procesos administrativos del **Gimnasio Viviana Woods**. Este sistema elimina el uso de fichas de papel, permitiendo la **inscripción online** de alumnos, la carga digital de documentación legal y la gestión centralizada de rutinas de entrenamiento.

El proyecto está diseñado con una arquitectura **Cliente-Servidor**, priorizando la usabilidad, el diseño responsive y la seguridad de datos. Actualmente se encuentra alojado en **InfinityFree**.

---

## 🚀 Funcionalidades Principales

### 👤 Módulo Público (Alumnos)

* **Landing Page Informativa:** Sección *Single Page* con información general del gimnasio, planes y contacto.
* **Inscripción Digital:** Formulario web para el registro de nuevos socios.
* **Carga de Documentos:** Sistema de *file upload* para adjuntar **DNI** y **Apto Físico** (imágenes o archivos PDF) directamente al servidor.
* **Validaciones:** Control de datos en tiempo real tanto en **Frontend** como en **Backend**.

### 🛠 Módulo Administrativo (Panel Admin)

* **Login Seguro:** Acceso restringido para administradores mediante manejo de sesiones en PHP.
* **Dashboard de Gestión:**

  * Listado completo de alumnos inscritos con buscador y filtros.
  * **Visor de Documentos:** Visualización de DNI y Apto Físico sin necesidad de descarga.
  * **Gestión de Rutinas:** Subida, asignación y eliminación de rutinas personalizadas en formato PDF para cada alumno.
  * **Baja de Usuarios:** Eliminación lógica y física de registros junto con los archivos asociados.
* **Notificaciones Automáticas:** Envío de correos electrónicos de confirmación y alertas mediante **SMTP** utilizando **PHPMailer**.

---

## 🛠️ Tecnologías Utilizadas

Este proyecto fue desarrollado utilizando el stack **LAMP** (Linux, Apache, MySQL, PHP) en un entorno de hosting compartido.

### Frontend

* HTML5 semántico
* CSS3 (diseño responsive, Flexbox y Grid)
* JavaScript (Vanilla JS, Fetch API)
* Google Fonts (Oswald y Roboto)
* FontAwesome (iconografía)

### Backend

* **PHP 7/8 (Nativo):** Lógica de negocio, manejo de sesiones y manipulación de archivos.
* **PHPMailer:** Librería para el envío de correos electrónicos autenticados vía SMTP.

### Base de Datos

* **MySQL:** Base de datos relacional para la persistencia de usuarios, planes y rutas de archivos.
* **phpMyAdmin:** Herramienta utilizada para la gestión y administración de la base de datos en el servidor.

### Infraestructura

* **InfinityFree:** Hosting gratuito utilizado para el despliegue en producción.

---

## 🔧 Instalación y Despliegue

### Requisitos Previos

* Servidor web (Apache o Nginx)
* PHP 7.4 o superior
* MySQL o MariaDB

### Pasos para instalación local (XAMPP / WAMP)

1. **Clonar el repositorio:**

   ```bash
   git clone https://github.com/tu-usuario/nombre-repo.git
   ```

2. **Base de datos:**

   * Crear una base de datos llamada `gym_db`.
   * Importar el archivo SQL (si está disponible) o generar las tablas según el DER del proyecto.

3. **Configuración:**

   * Editar el archivo `config.php` con las credenciales locales:

     ```php
     $host = "localhost";
     $user = "root";
     $pass = "";
     $db   = "gym_db";
     ```

4. **Ejecutar:**

   * Mover la carpeta del proyecto a `htdocs`.
   * Acceder desde el navegador a: `http://localhost/nombre-repo`

---

## 📂 Estructura del Proyecto

```
/
├── index.html            # Landing Page principal
├── index_styles.css      # Estilos de la landing page
├── login.php             # Acceso administrativo
├── dashboard.php         # Panel de control principal
├── dashboard_styles.css  # Estilos específicos del panel administrativo
├── procesar.php          # Lógica de inscripción y envío de correos
├── asignar_rutina.php    # Asignación de rutinas a alumnos
├── crear_y_asignar.php   # Creación y asignación de rutinas
├── obtener_rutina.php    # Obtención de rutinas del alumno
├── eliminar_inscripcion.php    # Eliminación de inscripciones
├── config.php            # Configuración y credenciales de base de datos
├── phpmailer/            # Librería para envío de correos electrónicos
│   ├── PHPMailer.php
│   ├── SMTP.php
│   └── Exception.php
├── uploads/              # Almacenamiento de documentos (DNI y Apto Físico)
├── logo_gym.png          # Logo institucional
├── logo_letras.png       # Logo tipográfico
├── pesas.jpg             # Recurso gráfico de la Landing Page
├── crossfit.jpg          # Recurso gráfico del Login
└── pesos.jpg             # Recurso gráfico del Dashboard
```

---

## 👤 Autor

**Renata Lombardo**

* [LinkedIn](https://ar.linkedin.com/in/renata-lombardo)
* [Gmail](mailto:olga.lombardo@comunidad.ub.edu.ar)

---

**Nota:** Este Proyecto de Prácticas de Laboratorio fue desarrollado para la *Tecnicatura en Programación de Computadoras* de la *Universidad de Belgrano*.
