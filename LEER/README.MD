📘 Sistema de Registro Académico – IE Mariscal Sucre
Este sistema fue desarrollado para la Institución Educativa Mariscal Sucre (Buenavista, Córdoba), con el objetivo de facilitar la gestión académica, incluyendo el registro de notas, asistencia y generación de boletines estudiantiles.

⚙️ Tecnologías utilizadas
PHP 8+

MySQL

Bootstrap 5

DOMPDF (para generar PDF)

XAMPP o servidor Apache compatible

👥 Roles y funcionalidades
🔒 Administrador
Crear, editar y eliminar docentes

Gestionar asignaturas, grados, niveles y estudiantes

Crear periodos académicos

Consultar reportes generales

Descargar boletines individuales (PDF)

👨‍🏫 Docente
Ver sus asignaturas y grados asignados

Registrar 5 notas por periodo (con cálculo automático del promedio)

Registrar asistencia diaria (asistió, falta, excusa)

Ver el estado del estudiante (aprobado/reprobado)

Generar boletines en PDF

Cambiar su contraseña desde el panel

📄 Características principales
Registro y edición completa de estudiantes

Panel independiente para administrador y docente

Gestión por periodos académicos

Reportes generales y personalizados

Sistema de login con control de rol

Interfaz moderna y responsiva

Puede funcionar en red local o servidor web

🧪 Instalación local (modo XAMPP)
Clona el repositorio:

bash
Copiar
Editar
git clone https://github.com/tu_usuario/registro-academico.git
Copia la carpeta al directorio htdocs/

Importa la base de datos desde /db/registro_academico.sql

Configura el acceso en /config/database.php

Abre en navegador:

ruby
Copiar
Editar
http://localhost/registrodenotas/public/login.php
📬 Contacto del desarrollador
Mario Álvarez Ramos
📧 alvarezramosmario37@gmail.com

🛡️ Licencia
Este sistema está protegido bajo Licencia Propietaria.
Queda prohibida su copia, distribución o uso con fines comerciales sin autorización.