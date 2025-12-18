<?php
define('ACCESO_PERMITIDO', true);
include 'config.php';

if (!esAdminAutenticado() || $_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

header('Content-Type: application/json');
$conn = obtenerConexion();

$alumno_id = (int)$_POST['alumno_id'] ?? 0;
$rutina_id = (int)$_POST['rutina_id'] ?? 0;
$accion = $_POST['accion'] ?? '';

if ($alumno_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de alumno no válido.']);
    $conn->close();
    exit;
}

try {
    if ($accion === 'asignar') {
        if ($rutina_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Selecciona una rutina válida.']);
            $conn->close();
            exit;
        }

        // 1. Eliminar cualquier asignación existente para este alumno
        $stmt = $conn->prepare("DELETE FROM alumno_rutina WHERE alumno_id = ?");
        $stmt->bind_param("i", $alumno_id);
        $stmt->execute();
        $stmt->close();

        // 2. Insertar la nueva asignación
        $fecha_asignacion = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO alumno_rutina (alumno_id, rutina_id, fecha_asignacion) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $alumno_id, $rutina_id, $fecha_asignacion);
        
        if ($stmt->execute()) {
            // Obtener el nombre de la rutina para la respuesta
            $stmt_nombre = $conn->prepare("SELECT nombre_rutina FROM rutinas WHERE id = ?");
            $stmt_nombre->bind_param("i", $rutina_id);
            $stmt_nombre->execute();
            $result_nombre = $stmt_nombre->get_result();
            $nombre_rutina = $result_nombre->fetch_assoc()['nombre_rutina'];
            
            echo json_encode(['success' => true, 'message' => 'Rutina asignada con éxito.', 'nombre_rutina' => $nombre_rutina]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al asignar la rutina.']);
        }
        $stmt->close();
    } elseif ($accion === 'eliminar') {
        $stmt = $conn->prepare("DELETE FROM alumno_rutina WHERE alumno_id = ?");
        $stmt->bind_param("i", $alumno_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Rutina desasignada con éxito.', 'nombre_rutina' => 'Pendiente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al desasignar la rutina.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

$conn->close();
?>