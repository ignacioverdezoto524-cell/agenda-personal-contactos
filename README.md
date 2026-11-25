# Agenda Personal de Contactos

## Descripción del proyecto

Agenda Personal de Contactos es una aplicación web desarrollada con PHP y MySQL que permite a los usuarios registrarse, iniciar sesión y administrar sus contactos personales de manera sencilla y segura.

Cada usuario puede gestionar únicamente sus propios contactos, evitando que otras cuentas puedan visualizar, modificar o eliminar información que no les pertenece.

## Tecnologías utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- XAMPP
- Visual Studio Code

## Funcionalidades principales

- Registro de nuevos usuarios.
- Inicio y cierre de sesión.
- Autenticación mediante sesiones.
- Almacenamiento seguro de contraseñas mediante `password_hash()`.
- Verificación de contraseñas mediante `password_verify()`.
- Agregar nuevos contactos.
- Consultar la lista de contactos.
- Editar información de contactos.
- Eliminar contactos.
- Protección de información mediante consultas preparadas.
- Validación de acceso para que cada usuario gestione únicamente sus propios contactos.
- Protección de formularios mediante token CSRF.

## Estructura de la base de datos

La base de datos se llama `agenda_contactos` y contiene dos tablas principales:

### Tabla usuarios

- id
- nombre
- correo
- password
- fecha_registro

### Tabla contactos

- id
- id_usuario
- nombre
- telefono
- correo
- ciudad

La tabla contactos se relaciona con usuarios mediante el campo `id_usuario`.

## Instalación y ejecución

1. Instalar XAMPP.
2. Iniciar Apache y MySQL.
3. Copiar la carpeta `agenda_contactos` dentro de `C:\xampp\htdocs\`.
4. Abrir phpMyAdmin desde `http://localhost/phpmyadmin`.
5. Crear la base de datos `agenda_contactos`.
6. Importar el archivo `database/agenda_contactos.sql`.
7. Verificar la configuración de conexión en `config/conexion.php`.
8. Abrir la aplicación desde:

`http://localhost/agenda_contactos/`

## Seguridad

La aplicación implementa mecanismos de seguridad como:

- Contraseñas cifradas mediante funciones de hash.
- Consultas preparadas para interactuar con la base de datos.
- Control de acceso mediante sesiones.
- Validación de propiedad de los contactos.
- Protección CSRF en las operaciones correspondientes.

## Autor

Proyecto académico desarrollado como práctica de programación web utilizando PHP y MySQL.