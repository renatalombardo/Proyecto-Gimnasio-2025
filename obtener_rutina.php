<?php
define('ACCESO_PERMITIDO', true);
include 'config.php';

header('Content-Type: application/json');

$alumnoId = $_GET['alumno_id'] ?? null;

if (!$alumnoId || !is_numeric($alumnoId)) {
    echo json_encode(['success' => false, 'message' => 'ID de alumno no válido.']);
    exit;
}

$conn = obtenerConexion();

try {
    $sql = "SELECT r.nombre_rutina, r.descripcion, r.contenido_ejercicios, r.fecha_creacion 
            FROM alumno_rutina ar
            JOIN rutinas r ON ar.rutina_id = r.id
            WHERE ar.alumno_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $alumnoId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $rutina = $resultado->fetch_assoc();
    $stmt->close();

    if ($rutina) {
        
        $contenido = '';
        
        if (!empty($rutina['descripcion'])) {
            $contenido .= '<h5 class="rutina-subtitulo">Descripción:</h5>';
            $contenido .= '<p class="rutina-descripcion">' . htmlspecialchars($rutina['descripcion']) . '</p>';
        }
        
        $contenido .= '<h5 class="rutina-subtitulo">Ejercicios:</h5>';
        $contenido .= '<div class="rutina-ejercicios">' . nl2br(htmlspecialchars($rutina['contenido_ejercicios'])) . '</div>';
        

        echo json_encode([
            'success' => true,
            'nombre' => $rutina['nombre_rutina'],
            'contenido' => $contenido,
            'fecha_creacion' => $rutina['fecha_creacion'] 
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'No hay rutina asignada a este alumno.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}

$conn->close();