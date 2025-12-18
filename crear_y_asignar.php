<?php
define('ACCESO_PERMITIDO', true);
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $alumnoId = $_POST['alumno_id'] ?? null;
    $nombreRutina = trim($_POST['nombre_rutina'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? ''); 
    $contenidoEjercicios = trim($_POST['contenido_ejercicios'] ?? '');

    if (!$alumnoId || empty($nombreRutina) || empty($contenidoEjercicios)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios para crear y asignar la rutina.']);
        exit;
    }

    $conn = obtenerConexion();
    $conn->begin_transaction();
    $nuevaRutinaId = null;

    try {
        // 1. CREAR LA NUEVA RUTINA MAESTRA
        $stmt = $conn->prepare("INSERT INTO rutinas (nombre_rutina, descripcion, contenido_ejercicios) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombreRutina, $descripcion, $contenidoEjercicios);

        if (!$stmt->execute()) {
            throw new Exception("Error al crear la rutina: " . $stmt->error);
        }
        $nuevaRutinaId = $conn->insert_id;
        $stmt->close();

        // 2. ASIGNAR LA NUEVA RUTINA AL ALUMNO
        $stmt = $conn->prepare("INSERT INTO alumno_rutina (alumno_id, rutina_id) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE rutina_id = ?");
        $stmt->bind_param("iii", $alumnoId, $nuevaRutinaId, $nuevaRutinaId);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al asignar la rutina al alumno: " . $stmt->error);
        }
        $stmt->close();

        // 3. COMMIT de la transacción
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Rutina "' . htmlspecialchars($nombreRutina) . '" creada y asignada exitosamente.',
            'rutina_id' => $nuevaRutinaId
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error en la transacción: ' . $e->getMessage()]);
    }

    $conn->close();

} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>