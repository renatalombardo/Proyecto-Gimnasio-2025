<?php
define('ACCESO_PERMITIDO', true);
include 'config.php';

// Si no está autenticado, redirigir al login
if (!esAdminAutenticado()) {
    header('Location: login.php');
    exit;
}

// Manejar el cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$conn = obtenerConexion();

// OBTENER DATOS DE INSCRIPCIONES
$sql = "SELECT i.*, r.nombre_rutina, r.id AS rutina_maestra_id 
        FROM inscripciones i
        LEFT JOIN alumno_rutina ar ON i.id = ar.alumno_id
        LEFT JOIN rutinas r ON ar.rutina_id = r.id
        ORDER BY i.fecha_registro DESC";
$resultado = $conn->query($sql);
$inscripciones = $resultado->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard_styles.css">
</head>
<body class="dashboard-body">
    
    <div class="dashboard-header">
        <h1>PANEL DE ADMINISTRACIÓN</h1>
        <div class="nav-admin">
            <a href="dashboard.php?logout=true" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> CERRAR SESIÓN
            </a>
        </div>
    </div>

    <div class="dashboard-content">
        
        <div class="titulo-seccion">
            <h2>Gestión de Alumnos</h2>
            <div class="linea-decorativa"></div>
            <div class="contador-registros">
                Total de inscripciones: <strong><?php echo count($inscripciones); ?></strong>
            </div>
        </div>

        <div class="filtros-container">
    <div class="filtro-grupo">
        <label><i class="fas fa-search"></i> Buscar</label>
        <input type="text" id="filtroNombre" placeholder="Nombre o Email...">
    </div>

    <div class="filtro-grupo">
        <label><i class="fas fa-filter"></i> Plan</label>
        <select id="filtroPlan">
            <option value="">Todos los planes</option>
            <option value="Clase Suelta">Clase Suelta</option>
            <option value="2 Veces x Semana">2 Veces x Semana</option>
            <option value="3 Veces x Semana">3 Veces x Semana</option>
            <option value="4 Veces x Semana">4 Veces x Semana</option>
            <option value="5 Veces x Semana">5 Veces x Semana</option>
        </select>
    </div>

    <div class="filtro-grupo">
        <label><i class="fas fa-dumbbell"></i> Estado Rutina</label>
        <select id="filtroEstado">
            <option value="">Cualquiera</option>
            <option value="asignada">Con Rutina</option>
            <option value="pendiente">Pendiente</option>
        </select>
    </div>

    <button id="btnLimpiarFiltros" class="btn-limpiar">
        <i class="fas fa-eraser"></i> Limpiar
    </button>
</div>

        <div class="table-wrapper">
            <?php if (count($inscripciones) > 0): ?>
                <table class="inscripciones-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Contacto</th>
                            <th>Plan y Objetivo</th>
                            <th>Documentos</th>
                            <th>Rutina</th>
                            <th>Gestión de Rutina</th>
                            <th>Fecha</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inscripciones as $inscripcion): ?>
                            <tr>
                                <td data-label="Nombre">
                                    <?php echo htmlspecialchars($inscripcion['nombre']); ?>
                                </td>

                                <td data-label="Contacto">
                                    <span class="email-estilo"><?php echo htmlspecialchars($inscripcion['email']); ?></span>
                                    <span class="telefono-estilo"><?php echo htmlspecialchars($inscripcion['telefono']); ?></span>
                                </td>

                                <td data-label="Plan/Objetivo">
                                    <span class="plan-estilo"><?php echo htmlspecialchars($inscripcion['plan']); ?></span><br/>
                                    <span class="objetivo-estilo"><?php echo htmlspecialchars($inscripcion['objetivo']); ?></span>
                                </td>

                                <td data-label="Documentos">
                                    <div>
                                        <?php if (!empty($inscripcion['ruta_dni'])): ?>
                                            <button class="btn-accion btn-ver-imagen" data-ruta="<?php echo htmlspecialchars($inscripcion['ruta_dni']); ?>" data-tipo="DNI">
                                                <i class="fas fa-eye"></i> Ver DNI
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!empty($inscripcion['ruta_apto_fisico'])): ?>
                                            <button class="btn-accion btn-ver-imagen" data-ruta="<?php echo htmlspecialchars($inscripcion['ruta_apto_fisico']); ?>" data-tipo="Apto Físico">
                                                <i class="fas fa-eye"></i> Ver Apto
                                            </button>
                                        <? endif; ?>
                                        <?php if (empty($inscripcion['ruta_dni']) && empty($inscripcion['ruta_apto_fisico'])): ?>
                                            <span class="sin-documentos">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <td data-label="Rutina Actual">
                                    <?php if (!empty($inscripcion['nombre_rutina'])): ?>
                                        <button class="btn-accion btn-ver-rutina" data-alumno-id="<?php echo $inscripcion['id']; ?>" data-alumno-nombre="<?php echo htmlspecialchars($inscripcion['nombre']); ?>">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                    <?php else: ?>
                                        <span class="rutina-pendiente">Pendiente</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Gestión Rutina">
                                    <button class="btn-accion btn-crear" data-alumno-id="<?php echo $inscripcion['id']; ?>" data-alumno-nombre="<?php echo htmlspecialchars($inscripcion['nombre']); ?>">
                                        <i class="fas fa-dumbbell"></i> Crear/Borrar
                                    </button>
                                </td>

                                <td data-label="Fecha Reg.">
                                    <?php echo date('d/m/y', strtotime($inscripcion['fecha_registro'])); ?>
                                </td>

                                <td data-label="Eliminar">
                                    <button class="btn-accion btn-eliminar" data-alumno-id="<?php echo $inscripcion['id']; ?>" data-alumno-nombre="<?php echo htmlspecialchars($inscripcion['nombre']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">Aún no hay inscripciones registradas.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="miModal" class="modal">
        <div class="modal-imagen-container">
            <span class="modal-close">&times;</span>
            <img id="imagen-modal" alt="Documento">
        </div>
    </div>
    
    <div id="asignacionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 class="modal-titulo">Asignar Rutina a: <span id="alumnoNombre"></span></h3>
            
            <form id="form-creacion-asignacion">
                <input type="hidden" id="alumnoId" name="alumno_id">
                
                <label for="nombre_rutina">Nombre de la Rutina:</label>
                <input type="text" id="nombre_rutina" name="nombre_rutina" placeholder="Ej: Hipertrofia A" required>

                <label for="descripcion">Descripción:</label>
                <input type="text" id="descripcion" name="descripcion" placeholder="Opcional">

                <label for="contenido_ejercicios">Ejercicios:</label>
                <textarea id="contenido_ejercicios" name="contenido_ejercicios" required>Ej: Press Banca 4x10...</textarea>

                <div class="botones-contenedor-modal">
                    <button type="submit" class="btn-modal btn-guardar">
                        <i class="fas fa-save"></i> GUARDAR RUTINA
                    </button>

                    <button type="button" id="btnEliminarAsignacion" class="btn-modal btn-eliminar-modal">
                        <i class="fas fa-trash"></i> ELIMINAR ASIGNACIÓN
                    </button>
                </div>
            </form>
            
            <p id="asignacionMensaje"></p>
        </div>
    </div>
    
    <div id="verRutinaModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3 class="modal-titulo-rutina">Rutina de: <span id="verRutinaAlumnoNombre"></span></h3>
            <h4 id="verRutinaNombre" class="ver-rutina-nombre">Cargando...</h4>
            <div id="verRutinaFecha" class="rutina-fecha">
                Creada el: <span id="verRutinaFechaTexto"></span>
            </div>
            <div id="verRutinaContenido" class="ver-rutina-contenido">
                Contenido...
            </div>
        </div>
    </div>

    <script>
        // Elementos DOM
        const miModal = document.getElementById('miModal');
        const asignacionModal = document.getElementById('asignacionModal');
        const verRutinaModal = document.getElementById('verRutinaModal');
        const imagenModal = document.getElementById('imagen-modal');
        const alumnoIdInput = document.getElementById('alumnoId');
        const alumnoNombreSpan = document.getElementById('alumnoNombre');
        const verRutinaAlumnoNombreSpan = document.getElementById('verRutinaAlumnoNombre');
        const verRutinaNombreEl = document.getElementById('verRutinaNombre');
        const verRutinaContenidoEl = document.getElementById('verRutinaContenido');
        const verRutinaFechaEl = document.getElementById('verRutinaFecha');
        const verRutinaFechaTextoEl = document.getElementById('verRutinaFechaTexto');
        const asignacionMensajeEl = document.getElementById('asignacionMensaje');
        const formCreacionAsignacion = document.getElementById('form-creacion-asignacion');
        const btnEliminarAsignacion = document.getElementById('btnEliminarAsignacion');

        function getScrollbarWidth() {
            const div = document.createElement('div');
            div.style.overflow = 'scroll';
            div.style.visibility = 'hidden';
            div.style.width = '100px';
            document.body.appendChild(div);
            const scrollbarWidth = div.offsetWidth - div.clientWidth;
            document.body.removeChild(div);
            return scrollbarWidth;
        }

        function openModal(modalElement) {
            const scrollbarWidth = getScrollbarWidth();

            document.documentElement.style.setProperty('--scrollbar-width', `${scrollbarWidth}px`);

            document.body.classList.add('modal-open');

            modalElement.style.display = "block";
        }

        function closeModal(modalElement) {
            // 1. Ocultar el modal
            modalElement.style.display = "none";
            
            // 2. Desbloqueo de scroll
            document.body.classList.remove('modal-open');
            
            // 3. Limpiar la compensación
            document.documentElement.style.removeProperty('--scrollbar-width');
        }

        // Funciones auxiliares
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return str;
            return str.replace(/&/g, '&amp;')
                     .replace(/</g, '&lt;')
                     .replace(/>/g, '&gt;')
                     .replace(/"/g, '&quot;')
                     .replace(/'/g, '&#039;');
        }

        // Event Listeners para botones de cierre de modal
        document.querySelectorAll('.modal-close, .close').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                closeModal(this.closest('.modal'));
            });
        });

        // Cerrar modal haciendo clic fuera
        [miModal, asignacionModal, verRutinaModal].forEach(modal => {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeModal(this);
                }
            });
        });

        // Event Listeners para botones de ver imagen
        document.querySelectorAll('.btn-ver-imagen').forEach(btn => {
            btn.addEventListener('click', function() {
                const ruta = this.getAttribute('data-ruta');
                const tipo = this.getAttribute('data-tipo');
                mostrarModalImagen(ruta, tipo);
            });
        });

        // Event Listeners para botones de ver rutina
        document.querySelectorAll('.btn-ver-rutina').forEach(btn => {
            btn.addEventListener('click', function() {
                const alumnoId = this.getAttribute('data-alumno-id');
                const alumnoNombre = this.getAttribute('data-alumno-nombre');
                mostrarVerRutinaModal(alumnoId, alumnoNombre);
            });
        });

        // Event Listeners para botones de crear/borrar rutina
        document.querySelectorAll('.btn-crear').forEach(btn => {
            btn.addEventListener('click', function() {
                const alumnoId = this.getAttribute('data-alumno-id');
                const alumnoNombre = this.getAttribute('data-alumno-nombre');
                mostrarCreacionAsignacionModal(alumnoId, alumnoNombre);
            });
        });

        // Event Listeners para botones de eliminar inscripción
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                const alumnoId = this.getAttribute('data-alumno-id');
                const alumnoNombre = this.getAttribute('data-alumno-nombre');
                confirmarEliminarInscripcion(alumnoId, alumnoNombre);
            });
        });

        // Funciones principales
        function mostrarModalImagen(ruta, tipoDocumento) {
            const extension = ruta.split('.').pop().toLowerCase();
            
            if (extension === 'pdf') {
                window.open(ruta, '_blank');
                return; 
            }
            
            // Usar la función centralizada para abrir
            openModal(miModal);
            
            imagenModal.src = ruta;
            imagenModal.alt = tipoDocumento || "Documento";
        }

        function mostrarCreacionAsignacionModal(alumnoId, alumnoNombre) {
            alumnoIdInput.value = alumnoId;
            alumnoNombreSpan.textContent = alumnoNombre;
            asignacionMensajeEl.textContent = '';
            document.getElementById('nombre_rutina').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('contenido_ejercicios').value = 'Ej: Día 1 - Pecho y Tríceps...';

            openModal(asignacionModal);
        }

        async function procesarDesasignacion() {
            const alumnoId = alumnoIdInput.value;
            asignacionMensajeEl.textContent = "Desasignando...";
            asignacionMensajeEl.style.color = "#FFD700";

            const formData = new FormData();
            formData.append('alumno_id', alumnoId);
            formData.append('accion', 'eliminar');
            
            try {
                const response = await fetch('asignar_rutina.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const data = await response.json();
                if (data.success) {
                    asignacionMensajeEl.textContent = data.message;
                    asignacionMensajeEl.style.color = "#2ecc71";
                    setTimeout(() => { 
                        // El modal debe cerrarse antes de la recarga
                        closeModal(asignacionModal);
                        window.location.reload(); 
                    }, 1000); 
                } else {
                    asignacionMensajeEl.textContent = data.message;
                    asignacionMensajeEl.style.color = "#e74c3c";
                }
            } catch (error) {
                asignacionMensajeEl.textContent = "Error de conexión.";
                asignacionMensajeEl.style.color = "#e74c3c";
            }
        }

        async function procesarCreacionAsignacion(event) {
            event.preventDefault();
            asignacionMensajeEl.textContent = "Procesando...";
            asignacionMensajeEl.style.color = "#FFD700";
            
            const formData = new FormData(formCreacionAsignacion);
            if(!formData.get('nombre_rutina') || !formData.get('contenido_ejercicios')) {
                asignacionMensajeEl.textContent = "Faltan datos obligatorios.";
                asignacionMensajeEl.style.color = "#e74c3c";
                return;
            }

            try {
                const response = await fetch('crear_y_asignar.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const data = await response.json();
                if (data.success) {
                    asignacionMensajeEl.textContent = data.message;
                    asignacionMensajeEl.style.color = "#2ecc71";
                    setTimeout(() => { 
                        closeModal(asignacionModal);
                        window.location.reload(); 
                    }, 1500); 
                } else {
                    asignacionMensajeEl.textContent = "Error: " + data.message;
                    asignacionMensajeEl.style.color = "#e74c3c";
                }
            } catch (error) {
                asignacionMensajeEl.textContent = "Error de conexión.";
                asignacionMensajeEl.style.color = "#e74c3c";
            }
        }

        async function mostrarVerRutinaModal(alumnoId, alumnoNombre) {
            verRutinaAlumnoNombreSpan.textContent = alumnoNombre;
            verRutinaNombreEl.textContent = "Cargando...";
            verRutinaContenidoEl.textContent = "Obteniendo datos...";
            verRutinaFechaEl.style.display = "none";

            openModal(verRutinaModal);

            try {
                const response = await fetch(`obtener_rutina.php?alumno_id=${alumnoId}`, { 
                    method: 'GET' 
                });
                const data = await response.json();
                if (data.success) {
                    verRutinaNombreEl.textContent = htmlspecialchars(data.nombre);
                    verRutinaContenidoEl.innerHTML = data.contenido; 

                    if (data.fecha_creacion) {
                        const fecha = new Date(data.fecha_creacion);
                        const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        verRutinaFechaTextoEl.textContent = fechaFormateada;
                        verRutinaFechaEl.style.display = "block";
                    }
                } else {
                    verRutinaNombreEl.textContent = "Error";
                    verRutinaContenidoEl.textContent = data.message || "Error al cargar.";
                }
            } catch (error) {
                verRutinaNombreEl.textContent = "Error";
                verRutinaContenidoEl.textContent = "Error de conexión.";
            }
        }
        
        function confirmarEliminarInscripcion(alumnoId, alumnoNombre) {
            if (confirm(`¿ELIMINAR inscripción de ${alumnoNombre}? \n\nEsta acción es irreversible.`)) {
                procesarEliminacion(alumnoId, alumnoNombre);
            }
        }
        
        async function procesarEliminacion(alumnoId, alumnoNombre) {
            alert(`Eliminando a ${alumnoNombre}...`); 
            const formData = new FormData();
            formData.append('alumno_id', alumnoId);
            
            try {
                const response = await fetch('eliminar_inscripcion.php', { 
                    method: 'POST', 
                    body: formData 
                });
                const data = await response.json();
                if (data.success) {
                    alert(`Éxito: ${data.message}`);
                    window.location.reload(); 
                } else {
                    alert(`Error: ${data.message}`);
                }
            } catch (error) {
                alert("Error de conexión.");
            }
        }

        // Event Listeners para formularios
        formCreacionAsignacion.addEventListener('submit', procesarCreacionAsignacion);
        btnEliminarAsignacion.addEventListener('click', procesarDesasignacion);    

// LÓGICA DE FILTRADO EN TIEMPO REAL

const inputNombre = document.getElementById('filtroNombre');
const selectPlan = document.getElementById('filtroPlan');
const selectEstado = document.getElementById('filtroEstado');
const btnLimpiar = document.getElementById('btnLimpiarFiltros');
const tablaFilas = document.querySelectorAll('.inscripciones-table tbody tr');

function filtrarTabla() {
    const textoBusqueda = inputNombre.value.toLowerCase();
    const planSeleccionado = selectPlan.value.toLowerCase();
    const estadoSeleccionado = selectEstado.value; // "asignada" o "pendiente"

    tablaFilas.forEach(fila => {
        // Obtiene los datos de las celdas (usando los data-labels)
        const nombreEmail = fila.querySelector('[data-label="Nombre"]').textContent.toLowerCase() + 
                            fila.querySelector('[data-label="Contacto"]').textContent.toLowerCase();
        
        const planTexto = fila.querySelector('[data-label="Plan/Objetivo"]').textContent.toLowerCase();
        
        const celdaRutina = fila.querySelector('[data-label="Rutina Actual"]');
        const tieneRutina = !celdaRutina.textContent.includes('Pendiente');

        // Lógica de coincidencia
        const coincideNombre = nombreEmail.includes(textoBusqueda);
        const coincidePlan = planSeleccionado === "" || planTexto.includes(planSeleccionado);
        
        let coincideEstado = true;
        if (estadoSeleccionado === "asignada") coincideEstado = tieneRutina;
        if (estadoSeleccionado === "pendiente") coincideEstado = !tieneRutina;

        // Mostrar u ocultar fila
        if (coincideNombre && coincidePlan && coincideEstado) {
            fila.style.display = "";
        } else {
            fila.style.display = "none";
        }
    });
    
    actualizarContadorVisible();
}

function actualizarContadorVisible() {
    const visibles = Array.from(tablaFilas).filter(f => f.style.display !== "none").length;
    const contadorEl = document.querySelector('.contador-registros strong');
    if(contadorEl) contadorEl.textContent = visibles;
}

// Eventos para disparar el filtrado
inputNombre.addEventListener('input', filtrarTabla);
selectPlan.addEventListener('change', filtrarTabla);
selectEstado.addEventListener('change', filtrarTabla);

// Botón limpiar
btnLimpiar.addEventListener('click', () => {
    inputNombre.value = "";
    selectPlan.value = "";
    selectEstado.value = "";
    filtrarTabla();
});
    </script>
</body>
</html>