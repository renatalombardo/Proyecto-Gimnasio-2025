<?php
define('ACCESO_PERMITIDO', true); 
include 'config.php'; 

// Usamos namespaces de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluimos los archivos de la librería
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $smtpHost = 'smtp.gmail.com'; 
    $smtpUser = 'latamdelsur@gmail.com'; 
    $smtpPass = 'cefjwaspwvmbjzpf'; 
    $smtpPort = 587; 
    $mailFromName = 'Gimnasio Viviana Woods'; 
    
    // --- DESTINATARIOS Y DATOS ---
    $destinatarioAdmin = "viviwoodsgym@gmail.com";
    
    // Captura de datos
    $nombre = $_POST['nombre'] ?? 'No especificado';
    $email_alumno = $_POST['email'] ?? null;
    $edad = $_POST['edad'] ?? 'No especificado';
    $telefono = $_POST['telefono'] ?? 'No especificado';
    $plan = $_POST['plan'] ?? 'No especificado';
    $horarios = $_POST['horarios'] ?? 'No especificado';
    $objetivo = $_POST['objetivo'] ?? 'No especificado';
    $consultas = $_POST['consultas'] ?? 'Ninguna';
    
    // 1. MANEJO DE ARCHIVOS Y RUTAS 
    $uploadDir = 'uploads/'; 

    // Asegurarse de que la carpeta exista
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); 
    }
    
    // Función para subir el archivo de forma segura
    function subirArchivo($inputName, $nombreAlumno, $uploadDir) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES[$inputName]['tmp_name'];
            $fileName = $_FILES[$inputName]['name'];
            
            // Crea un nombre único y seguro (incluye un timestamp para evitar colisiones)
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $safeFileName = 'insc_' . strtolower(str_replace(' ', '_', $nombreAlumno)) . '_' . $inputName . '_' . time() . '.' . $extension;
            $destPath = $uploadDir . $safeFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                return $destPath; 
            }
        }
        return NULL; // Devuelve NULL si no se subió o hubo un error
    }

    // Subir archivos y obtener sus rutas
    $rutaDNI = subirArchivo('dni', $nombre, $uploadDir);
    $rutaAptoFisico = subirArchivo('apto_fisico', $nombre, $uploadDir);

    // 2. GUARDAR DATOS EN LA BASE DE DATOS 
    $conn = obtenerConexion();
    
    // Prepara la consulta SQL
    $stmt = $conn->prepare("INSERT INTO inscripciones (nombre, email, edad, telefono, plan, horarios, objetivo, consultas, ruta_dni, ruta_apto_fisico) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Vincula los 10 parámetros 
    $edad_int = is_numeric($edad) ? (int)$edad : null; 
    
    $stmt->bind_param("ssisssssss", $nombre, $email_alumno, $edad_int, $telefono, $plan, $horarios, $objetivo, $consultas, $rutaDNI, $rutaAptoFisico);

    $insercionOk = $stmt->execute();
    
    $stmt->close();
    $conn->close();

    // --- 3. FUNCIÓN DE ENVÍO CON PHPMailer ---
    function enviarCorreo($to, $subject, $body, $isHTML, $attachments = [], $isConfirmation = false, $smtpConfig) {
        $mail = new PHPMailer(true);
        try {
            // Configuración del Servidor SMTP
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['user'];
            $mail->Password = $smtpConfig['pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port = $smtpConfig['port'];
            $mail->CharSet = 'UTF-8';

            // Remitente y Destinatario
            $mail->setFrom($smtpConfig['user'], $smtpConfig['name']);
            $mail->addAddress($to);
            
            // Permite al administrador responder directamente al alumno
            if (!$isConfirmation) {
                global $email_alumno, $nombre;
                if ($email_alumno) {
                    $mail->addReplyTo($email_alumno, $nombre);
                }
            }

            // Contenido
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body; 
            if (!$isHTML) {
                $mail->AltBody = $body; 
            }
            
            // Adjuntos 
            foreach ($attachments as $file) {
                // usamos las rutas guardadas para adjuntar, si existen
                if ($file['path'] && file_exists($file['path'])) {
                    $mail->addAttachment($file['path'], basename($file['path']));
                } else if (isset($_FILES[$file['inputName']]) && $_FILES[$file['inputName']]['error'] == UPLOAD_ERR_OK) {
                     // Caso de respaldo: adjuntar directamente desde la ruta temporal 
                    $mail->addAttachment($_FILES[$file['inputName']]['tmp_name'], $_FILES[$file['inputName']]['name']);
                }
            }

            return $mail->send();

        } catch (Exception $e) {
            return false;
        }
    }


    $smtpConfiguration = [
        'host' => $smtpHost,
        'user' => $smtpUser,
        'pass' => $smtpPass,
        'port' => $smtpPort,
        'name' => $mailFromName
    ];

    // --- 4. CORREO PARA EL GIMNASIO (ADMINISTRADORA) ---
    $asuntoAdmin = "Nueva Inscripción Web - Gimnasio Viviana Woods";
    
    $mensajeAdmin = "
        <html>
        <body style='font-family: sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 8px; border-top: 5px solid #FFD700; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #FFD700; margin-top: 0;'>📢 ¡NUEVA INSCRIPCIÓN WEB RECIBIDA!</h2>
                <p style='font-size: 1.1em;'>Se ha completado el formulario de inscripción en el sitio web. Los archivos DNI y Apto Físico están adjuntos a este correo.</p>
                
                <h3 style='color: #333; border-bottom: 2px solid #eee; padding-bottom: 5px;'>DATOS DEL ALUMNO:</h3>

                <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                    <tr><td style='padding: 8px 0; font-weight: bold; width: 35%; border-bottom: 1px solid #eee;'>Nombre Completo:</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$nombre</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;'>Email:</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>" . ($email_alumno ? $email_alumno : 'No proporcionado') . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;'>Edad:</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$edad</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;'>Teléfono (WhatsApp):</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$telefono</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;'>Plan Seleccionado:</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$plan</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;'>Horarios Preferidos:</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$horarios</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee;'>Objetivo Principal:</td><td style='padding: 8px 0; border-bottom: 1px solid #eee;'>$objetivo</td></tr>
                </table>

                <h3 style='color: #333; border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top: 20px;'>CONSULTAS / COMENTARIOS:</h3>
                <p style='background-color: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px solid #ddd;'>
                    $consultas
                </p>

                <p style='margin-top: 30px; text-align: center;'>
                    <a href='mailto:" . ($email_alumno ? $email_alumno : $destinatarioAdmin) . "' style='display: inline-block; background-color: #FFD700; color: #121212; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; text-transform: uppercase;'>
                        Responder al Alumno
                    </a>
                </p>
                <p style='font-size: 0.9em; text-align: center; margin-top: 20px;'><a href='https://viviwoodsgym.page.gd/dashboard.php' style='color: #999;'>Ver en Panel de Administración</a></p>

            </div>
        </body>
        </html>
    ";
    
    $attachments = [
        ['inputName' => 'dni', 'path' => $rutaDNI],
        ['inputName' => 'apto_fisico', 'path' => $rutaAptoFisico]
    ];

    $envioAdminOk = enviarCorreo($destinatarioAdmin, $asuntoAdmin, $mensajeAdmin, true, $attachments, false, $smtpConfiguration);

    // --- 5. CORREO DE CONFIRMACIÓN PARA EL ALUMNO ---
    $envioAlumnoOk = true; 
    if ($envioAdminOk && $email_alumno) {
        $asuntoAlumno = "¡Inscripción Exitosa! - Gimnasio Viviana Woods";
        
        $mensajeAlumno = "
            <html>
            <body style='font-family: sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 8px; border-top: 5px solid #FFD700;'>
                    <h2 style='color: #FFD700;'>¡Hola $nombre! Tu solicitud de inscripción fue recibida.</h2>
                    <p>Gracias por elegir el Gimnasio Viviana Woods. Hemos registrado tu solicitud con los siguientes datos:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Plan Seleccionado:</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>$plan</td></tr>
                        <tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Teléfono de Contacto:</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>$telefono</td></tr>
                        <tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Horarios Preferidos:</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>$horarios</td></tr>
                        <tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Tu Objetivo:</td><td style='padding: 8px; border-bottom: 1px solid #eee;'>$objetivo</td></tr>
                    </table>

                    <p style='margin-top: 30px; background-color: #fffac2; padding: 10px; border-left: 5px solid #FFD700;'>
                        <strong>Próximos pasos:</strong> Nuestro equipo revisará los archivos (DNI y Apto Físico) y los datos. 
                        Te contactaremos al número <strong>$telefono</strong> a la brevedad para confirmar el inicio de tus entrenamientos.
                    </p>

                    <p>¡Te esperamos!</p>
                    <p>Atentamente,<br>Equipo de Viviana Woods Gym</p>
                    <p style='font-size: 0.8em; color: #999;'>Este es un correo automático, por favor no lo respondas.</p>
                </div>
            </body>
            </html>
        ";

        $envioAlumnoOk = enviarCorreo($email_alumno, $asuntoAlumno, $mensajeAlumno, true, [], true, $smtpConfiguration);
    }

    // --- 6. REDIRECCIÓN FINAL ---
    if ($insercionOk && $envioAdminOk) {
        header("Location: index.html?status=success");
        exit;
    } else {
        header("Location: index.html?status=error");
        exit;
    }

} else {
    echo "Acceso no válido.";
}
?>