<?php
define('ACCESO_PERMITIDO', true);
include 'config.php';

// Verificar autenticación y método POST
if (!esAdminAutenticado() || $_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

header('Content-Type: application/json');
$conn = obtenerConexion();

$alumnoId = (int)$_POST['alumno_id'] ?? 0;

if ($alumnoId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de alumno no válido.']);
    $conn->close();
    exit;
}

// Función auxiliar para eliminar archivos del servidor
function eliminarArchivosFisicos($ruta_dni, $ruta_apto_fisico) {
    $archivos_eliminados = [];
    $errores_archivos = [];

    // Intenta eliminar el DNI si existe
    if (!empty($ruta_dni) && file_exists($ruta_dni)) {
        if (unlink($ruta_dni)) {
            $archivos_eliminados[] = basename($ruta_dni);
        } else {
            $errores_archivos[] = "Error al eliminar DNI.";
        }
    }
    
    // Intenta eliminar el Apto Físico si existe
    if (!empty($ruta_apto_fisico) && file_exists($ruta_apto_fisico)) {
        if (unlink($ruta_apto_fisico)) {
            $archivos_eliminados[] = basename($ruta_apto_fisico);
        } else {
            $errores_archivos[] = "Error al eliminar Apto Físico.";
        }
    }

    return ['success' => empty($errores_archivos), 'eliminados' => $archivos_eliminados, 'errores' => $errores_archivos];
}

try {
    $conn->begin_transaction();
    
    // 1. OBTENER RUTAS DE ARCHIVOS ANTES DE ELIMINAR LA INSCRIPCIÓN
    $stmt = $conn->prepare("SELECT ruta_dni, ruta_apto_fisico FROM inscripciones WHERE id = ?");
    $stmt->bind_param("i", $alumnoId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $archivos = $resultado->fetch_assoc();
    $stmt->close();
    
    if (!$archivos) {
        throw new Exception("Inscripción no encontrada.");
    }
    
    $ruta_dni = $archivos['ruta_dni'] ?? null;
    $ruta_apto_fisico = $archivos['ruta_apto_fisico'] ?? null;
    
    // 2. ELIMINAR ASIGNACIÓN DE RUTINA (si existe en la tabla alumno_rutina)
    $stmt = $conn->prepare("DELETE FROM alumno_rutina WHERE alumno_id = ?");
    $stmt->bind_param("i", $alumnoId);
    $stmt->execute();
    $stmt->close();

    // 3. ELIMINAR LA INSCRIPCIÓN PRINCIPAL
    $stmt = $conn->prepare("DELETE FROM inscripciones WHERE id = ?");
    $stmt->bind_param("i", $alumnoId);

    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar la inscripción: " . $stmt->error);
    }
    $stmt->close();
    
    // 4. COMMIT de la transacción
    $conn->commit();
    
    // 5. ELIMINAR ARCHIVOS FÍSICOS (FUERA DE LA TRANSACCIÓN DE BD)
    $resultadoArchivos = eliminarArchivosFisicos($ruta_dni, $ruta_apto_fisico);
    $mensajeArchivos = "";
    if (!empty($resultadoArchivos['errores'])) {
        $mensajeArchivos = ". Advertencia: " . implode(", ", $resultadoArchivos['errores']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Inscripción eliminada exitosamente' . $mensajeArchivos,
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error en la transacción de eliminación: ' . $e->getMessage()]);
}

$conn->close();
?>