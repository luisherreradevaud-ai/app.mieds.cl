<?php

require_once("../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>DEBUG - Calendario de Tareas</h1>";
echo "<hr>";
echo "<h2>Usuario Actual</h2>";
echo "<pre>";
echo "ID: " . $usuario->id . "\n";
echo "Nombre: " . $usuario->nombre . "\n";
echo "Nivel: " . $usuario->nivel . "\n";
echo "</pre>";

echo "<hr>";
echo "<h2>Tableros del Usuario</h2>";

// Get all tableros
$all_tableros = KanbanTablero::getAll();
$mis_tableros = array();

echo "<h3>Análisis de Tableros:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Tablero ID</th><th>Nombre</th><th>Creador ID</th><th>¿Soy Creador?</th><th>¿Estoy Asignado?</th><th>¿Tengo Acceso?</th></tr>";

foreach($all_tableros as $tablero) {
    $es_creador = ($tablero->id_usuario_creador == $usuario->id);

    $usuarios_asignados = $tablero->getUsuarios();
    $estoy_asignado = false;
    foreach($usuarios_asignados as $rel) {
        if($rel->id_usuarios == $usuario->id) {
            $estoy_asignado = true;
            break;
        }
    }

    $tiene_acceso = $tablero->usuarioTieneAcceso($usuario->id);

    if($tiene_acceso) {
        $mis_tableros[] = $tablero->id;
    }

    echo "<tr>";
    echo "<td>" . $tablero->id . "</td>";
    echo "<td>" . htmlspecialchars($tablero->nombre) . "</td>";
    echo "<td>" . $tablero->id_usuario_creador . "</td>";
    echo "<td>" . ($es_creador ? '✅ SÍ' : '❌ NO') . "</td>";
    echo "<td>" . ($estoy_asignado ? '✅ SÍ' : '❌ NO') . "</td>";
    echo "<td style='background-color: " . ($tiene_acceso ? '#90EE90' : '#FFB6C1') . "'>" . ($tiene_acceso ? '✅ SÍ' : '❌ NO') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>Tareas en Calendario (próximos 30 días)</h2>";

$start = date('Y-m-d');
$end = date('Y-m-d', strtotime('+30 days'));

echo "<h3>Query SQL:</h3>";
if($usuario->nivel == "Administrador") {
    $query = "SELECT DISTINCT kt.* FROM kanban_tareas kt
              WHERE kt.fecha_vencimiento BETWEEN '".$start."' AND '".$end."'
              AND kt.fecha_vencimiento != '0000-00-00'
              AND kt.estado != 'Completada'
              ORDER BY kt.fecha_vencimiento";
} else {
    $query = "SELECT DISTINCT kt.* FROM kanban_tareas kt
              INNER JOIN kanban_columnas kc ON kt.id_kanban_columnas = kc.id
              INNER JOIN kanban_tableros ktb ON kc.id_kanban_tableros = ktb.id
              LEFT JOIN kanban_tableros_usuarios ktu ON ktb.id = ktu.id_kanban_tableros
              WHERE (ktb.id_usuario_creador = '".$usuario->id."' OR ktu.id_usuarios = '".$usuario->id."')
              AND kt.fecha_vencimiento BETWEEN '".$start."' AND '".$end."'
              AND kt.fecha_vencimiento != '0000-00-00'
              AND kt.estado != 'Completada'
              ORDER BY kt.fecha_vencimiento";
}

echo "<pre>" . htmlspecialchars($query) . "</pre>";

$result = $GLOBALS['mysqli']->query($query);

if($result) {
    echo "<h3>Tareas encontradas: " . $result->num_rows . "</h3>";

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Tarea ID</th><th>Nombre</th><th>Fecha Venc.</th><th>Tablero ID</th><th>Tablero</th><th>Creador Tablero</th><th>¿Tengo Acceso?</th></tr>";

    while($row = $result->fetch_assoc()) {
        $tarea = new KanbanTarea($row['id']);
        $columna = new KanbanColumna($tarea->id_kanban_columnas);
        $tablero_id = $columna->id_kanban_tableros;
        $tablero = new KanbanTablero($tablero_id);

        $tiene_acceso = $tablero->usuarioTieneAcceso($usuario->id);

        echo "<tr>";
        echo "<td>" . $tarea->id . "</td>";
        echo "<td>" . htmlspecialchars($tarea->nombre) . "</td>";
        echo "<td>" . $tarea->fecha_vencimiento . "</td>";
        echo "<td>" . $tablero_id . "</td>";
        echo "<td>" . htmlspecialchars($tablero->nombre) . "</td>";
        echo "<td>" . $tablero->id_usuario_creador . "</td>";
        echo "<td style='background-color: " . ($tiene_acceso ? '#90EE90' : '#FFB6C1') . "'>" . ($tiene_acceso ? '✅ SÍ' : '❌ NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error en query: " . $GLOBALS['mysqli']->error . "</p>";
}

echo "<hr>";
echo "<h2>Verificación de tabla kanban_tableros_usuarios</h2>";

$query_check = "SELECT ktu.*, u.nombre as usuario_nombre, kt.nombre as tablero_nombre
                FROM kanban_tableros_usuarios ktu
                LEFT JOIN usuarios u ON ktu.id_usuarios = u.id
                LEFT JOIN kanban_tableros kt ON ktu.id_kanban_tableros = kt.id
                WHERE ktu.id_usuarios = '".$usuario->id."'";

$result_check = $GLOBALS['mysqli']->query($query_check);

if($result_check && $result_check->num_rows > 0) {
    echo "<h3>Estás asignado a " . $result_check->num_rows . " tablero(s):</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Relación ID</th><th>Tablero ID</th><th>Tablero Nombre</th><th>Usuario ID</th><th>Usuario Nombre</th></tr>";

    while($row = $result_check->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['id_kanban_tableros'] . "</td>";
        echo "<td>" . htmlspecialchars($row['tablero_nombre']) . "</td>";
        echo "<td>" . $row['id_usuarios'] . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No estás asignado a ningún tablero mediante la tabla kanban_tableros_usuarios.</p>";
}

?>
