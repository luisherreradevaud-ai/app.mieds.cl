<?php
/**
 * BatchInstruccionesPDF - Generador de PDF de instrucciones detalladas de batch
 * Genera un PDF cronológico con toda la información del proceso de producción
 */

class BatchInstruccionesPDF {

  private $batch;
  private $receta;
  private $insumos = [];
  private $lupulizaciones = [];
  private $enfriados = [];
  private $traspasos = [];
  private $activos = [];
  private $levaduras = [];
  private $timeline = [];

  public function __construct(Batch $batch) {
    $this->batch = $batch;
    $this->receta = $batch->id_recetas ? new Receta($batch->id_recetas) : null;
    $this->cargarDatos();
    $this->construirTimeline();
  }

  /**
   * Carga todos los datos relacionados al batch
   */
  private function cargarDatos() {
    // Insumos
    $this->insumos = BatchInsumo::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY etapa, etapa_index");

    // Lupulizaciones
    $this->lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY date, hora, seq_index");

    // Enfriados
    $this->enfriados = BatchEnfriado::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY date, hora_inicio, seq_index");

    // Traspasos
    $this->traspasos = BatchTraspaso::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY date, hora, seq_index");

    // Activos (fermentadores)
    $this->activos = BatchActivo::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "'");

    // Levaduras
    $this->levaduras = BatchLevadura::getByBatch($this->batch->id);
  }

  /**
   * Construye la línea de tiempo cronológica del proceso
   */
  private function construirTimeline() {
    $events = [];

    // Fecha de inicio del batch
    if($this->batch->batch_date && $this->batch->batch_date != '0000-00-00') {
      $events[] = [
        'datetime' => $this->batch->batch_date . ' 00:00:00',
        'tipo' => 'inicio',
        'titulo' => 'Inicio del Batch',
        'descripcion' => 'Creación del batch #' . $this->batch->batch_nombre
      ];
    }

    // Maceración
    if($this->batch->maceracion_hora_inicio) {
      $fecha = $this->batch->batch_date ?: date('Y-m-d');
      $events[] = [
        'datetime' => $fecha . ' ' . $this->batch->maceracion_hora_inicio,
        'tipo' => 'maceracion',
        'titulo' => 'Inicio Maceración',
        'descripcion' => 'Temperatura: ' . $this->batch->maceracion_temperatura . '°C'
      ];
    }
    if($this->batch->maceracion_hora_finalizacion) {
      $fecha = $this->batch->batch_date ?: date('Y-m-d');
      $events[] = [
        'datetime' => $fecha . ' ' . $this->batch->maceracion_hora_finalizacion,
        'tipo' => 'maceracion',
        'titulo' => 'Fin Maceración',
        'descripcion' => 'pH final: ' . $this->batch->maceracion_ph
      ];
    }

    // Lavado de granos
    if($this->batch->lavado_de_granos_hora_inicio) {
      $fecha = $this->batch->batch_date ?: date('Y-m-d');
      $events[] = [
        'datetime' => $fecha . ' ' . $this->batch->lavado_de_granos_hora_inicio,
        'tipo' => 'lavado',
        'titulo' => 'Inicio Lavado de Granos',
        'descripcion' => ''
      ];
    }
    if($this->batch->lavado_de_granos_hora_termino) {
      $fecha = $this->batch->batch_date ?: date('Y-m-d');
      $events[] = [
        'datetime' => $fecha . ' ' . $this->batch->lavado_de_granos_hora_termino,
        'tipo' => 'lavado',
        'titulo' => 'Fin Lavado de Granos',
        'descripcion' => 'Mosto: ' . $this->batch->lavado_de_granos_mosto . 'L, Densidad: ' . $this->batch->lavado_de_granos_densidad
      ];
    }

    // Lupulizaciones
    foreach($this->lupulizaciones as $l) {
      if($l->date && $l->date != '0000-00-00') {
        $events[] = [
          'datetime' => $l->date . ' ' . ($l->hora ?: '00:00:00'),
          'tipo' => 'lupulizacion',
          'titulo' => 'Lupulización #' . ($l->seq_index + 1),
          'descripcion' => 'Tipo: ' . ($l->tipo ?: 'N/E')
        ];
      }
    }

    // Enfriados
    foreach($this->enfriados as $e) {
      if($e->date && $e->date != '0000-00-00') {
        $events[] = [
          'datetime' => $e->date . ' ' . ($e->hora_inicio ?: '00:00:00'),
          'tipo' => 'enfriado',
          'titulo' => 'Enfriado #' . ($e->seq_index + 1),
          'descripcion' => 'Temp. inicial: ' . $e->temperatura_inicio . '°C'
        ];
      }
    }

    // Fermentación
    if($this->batch->fermentacion_date && $this->batch->fermentacion_date != '0000-00-00') {
      $events[] = [
        'datetime' => $this->batch->fermentacion_date . ' ' . ($this->batch->fermentacion_hora_inicio ?: '00:00:00'),
        'tipo' => 'fermentacion',
        'titulo' => 'Inicio Fermentación',
        'descripcion' => 'Temperatura: ' . $this->batch->fermentacion_temperatura . '°C'
      ];
    }
    if($this->batch->fermentacion_finalizada && $this->batch->fermentacion_finalizada_datetime) {
      $events[] = [
        'datetime' => $this->batch->fermentacion_finalizada_datetime,
        'tipo' => 'fermentacion',
        'titulo' => 'Fin Fermentación',
        'descripcion' => 'pH: ' . $this->batch->fermentacion_ph . ', Densidad: ' . $this->batch->fermentacion_densidad
      ];
    }

    // Traspasos
    foreach($this->traspasos as $t) {
      if($t->date && $t->date != '0000-00-00') {
        $origen = $t->id_fermentadores_inicio ? new Activo($t->id_fermentadores_inicio) : null;
        $destino = $t->id_fermentadores_final ? new Activo($t->id_fermentadores_final) : null;
        $events[] = [
          'datetime' => $t->date . ' ' . ($t->hora ?: '00:00:00'),
          'tipo' => 'traspaso',
          'titulo' => 'Traspaso #' . ($t->seq_index + 1),
          'descripcion' => ($origen ? $origen->nombre : '?') . ' → ' . ($destino ? $destino->nombre : '?') . ' (' . $t->cantidad . 'L)'
        ];
      }
    }

    // Maduración
    if($this->batch->maduracion_date && $this->batch->maduracion_date != '0000-00-00') {
      $events[] = [
        'datetime' => $this->batch->maduracion_date . ' ' . ($this->batch->maduracion_hora_inicio ?: '00:00:00'),
        'tipo' => 'maduracion',
        'titulo' => 'Inicio Maduración',
        'descripcion' => 'Temp. inicio: ' . $this->batch->maduracion_temperatura_inicio . '°C'
      ];
    }

    // Finalización
    if($this->batch->datetime_finalizacion && $this->batch->datetime_finalizacion != '0000-00-00 00:00:00') {
      $events[] = [
        'datetime' => $this->batch->datetime_finalizacion,
        'tipo' => 'finalizacion',
        'titulo' => 'Batch Finalizado',
        'descripcion' => 'Proceso completado'
      ];
    }

    // Ordenar por fecha/hora
    usort($events, function($a, $b) {
      return strcmp($a['datetime'], $b['datetime']);
    });

    $this->timeline = $events;
  }

  /**
   * Descarga el PDF directamente
   */
  public function descargar($filename = null) {
    require_once($GLOBALS['base_dir'] . '/vendor_php/dompdf_autoload.php');

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', false);
    $options->set('isRemoteEnabled', false);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($this->generarHTML());
    $dompdf->setPaper('Letter', 'portrait');
    $dompdf->render();

    if(!$filename) {
      $filename = 'Instrucciones_Batch_' . $this->batch->batch_nombre . '_' . date('Ymd') . '.pdf';
    }

    $dompdf->stream($filename, array('Attachment' => true));
  }

  /**
   * Genera el HTML completo del PDF
   */
  private function generarHTML() {
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    $html .= '<style>' . $this->getCSS() . '</style>';
    $html .= '</head><body>';

    // Portada
    $html .= $this->generarPortada();

    // Timeline visual
    $html .= $this->generarTimelineVisual();

    // Información general detallada
    $html .= $this->generarInfoGeneral();

    // Insumos por etapa
    $html .= $this->generarInsumosPorEtapa();

    // ETAPA 1: Preparación del Licor
    $html .= $this->generarEtapaLicor();

    // ETAPA 2: Maceración
    $html .= $this->generarEtapaMaceracion();

    // ETAPA 3: Lavado de Granos
    $html .= $this->generarEtapaLavado();

    // ETAPA 4: Cocción
    $html .= $this->generarEtapaCoccion();

    // ETAPA 5: Lupulizaciones detalladas
    $html .= $this->generarLupulizacionesDetalladas();

    // ETAPA 6: Enfriado
    $html .= $this->generarEnfriadosDetallados();

    // ETAPA 7: Inoculación y Levadura
    $html .= $this->generarInoculacionYLevadura();

    // ETAPA 8: Fermentación
    $html .= $this->generarFermentacionDetallada();

    // ETAPA 9: Traspasos
    $html .= $this->generarTraspasosDetallados();

    // ETAPA 10: Maduración
    $html .= $this->generarMaduracionDetallada();

    // Métricas y Resultados Finales
    $html .= $this->generarMetricasFinales();

    // Observaciones y Notas
    $html .= $this->generarObservacionesYNotas();

    // Pie de página
    $html .= $this->generarPie();

    $html .= '</body></html>';
    return $html;
  }

  private function generarPortada() {
    $recetaNombre = $this->receta ? $this->receta->nombre : 'Sin receta asignada';
    $fechaBatch = $this->formatearFecha($this->batch->batch_date);
    $cocinero = $this->batch->batch_id_usuarios_cocinero ? new Usuario($this->batch->batch_id_usuarios_cocinero) : null;

    return '
    <div class="portada">
      <div class="portada-header">
        <h1>INSTRUCCIONES DE ELABORACION</h1>
        <div class="batch-numero">#' . htmlspecialchars($this->batch->batch_nombre) . '</div>
      </div>
      <div class="portada-info">
        <div class="portada-receta">' . htmlspecialchars($recetaNombre) . '</div>
        <div class="portada-linea">' . $this->batch->getLineaProductivaLabel() . '</div>
        <table class="portada-datos">
          <tr>
            <td><strong>Fecha de Elaboracion:</strong></td>
            <td>' . $fechaBatch . '</td>
          </tr>
          <tr>
            <td><strong>Cocinero Responsable:</strong></td>
            <td>' . ($cocinero ? htmlspecialchars($cocinero->nombre) : 'No asignado') . '</td>
          </tr>
          <tr>
            <td><strong>Volumen Objetivo:</strong></td>
            <td>' . number_format($this->batch->batch_litros, 1) . ' Litros</td>
          </tr>
          <tr>
            <td><strong>Estado:</strong></td>
            <td>' . ucfirst(str_replace('-', ' ', $this->batch->etapa_seleccionada ?: 'batch')) . '</td>
          </tr>
        </table>
      </div>
      <div class="portada-footer">
        <p>Documento generado: ' . date('d/m/Y H:i') . '</p>
        <p>Cerveza Cocholgue - Sistema de Trazabilidad</p>
      </div>
    </div>
    <div class="page-break"></div>';
  }

  private function generarTimelineVisual() {
    if(empty($this->timeline)) {
      return '';
    }

    $html = '
    <div class="section">
      <h2>Linea de Tiempo del Proceso</h2>
      <div class="timeline">';

    foreach($this->timeline as $event) {
      $fecha = $this->formatearFechaHora($event['datetime']);
      $clase = 'timeline-item timeline-' . $event['tipo'];

      $html .= '
        <div class="' . $clase . '">
          <div class="timeline-fecha">' . $fecha . '</div>
          <div class="timeline-contenido">
            <div class="timeline-titulo">' . htmlspecialchars($event['titulo']) . '</div>
            <div class="timeline-desc">' . htmlspecialchars($event['descripcion']) . '</div>
          </div>
        </div>';
    }

    $html .= '</div></div>';
    return $html;
  }

  private function generarInfoGeneral() {
    $cocinero = $this->batch->batch_id_usuarios_cocinero ? new Usuario($this->batch->batch_id_usuarios_cocinero) : null;

    $html = '
    <div class="section">
      <h2>1. Informacion General del Batch</h2>
      <table class="tabla-info">
        <tr>
          <td class="label">Identificador:</td>
          <td class="valor">#' . htmlspecialchars($this->batch->batch_nombre) . '</td>
          <td class="label">Receta Base:</td>
          <td class="valor">' . ($this->receta ? htmlspecialchars($this->receta->nombre) : 'N/A') . '</td>
        </tr>
        <tr>
          <td class="label">Fecha Inicio:</td>
          <td class="valor">' . $this->formatearFecha($this->batch->batch_date) . '</td>
          <td class="label">Cocinero:</td>
          <td class="valor">' . ($cocinero ? htmlspecialchars($cocinero->nombre) : 'No asignado') . '</td>
        </tr>
        <tr>
          <td class="label">Volumen Objetivo:</td>
          <td class="valor">' . number_format($this->batch->batch_litros, 1) . ' L</td>
          <td class="label">Linea Productiva:</td>
          <td class="valor">' . $this->batch->getLineaProductivaLabel() . '</td>
        </tr>
        <tr>
          <td class="label">Tipo:</td>
          <td class="valor">' . htmlspecialchars($this->batch->tipo ?: 'Batch') . '</td>
          <td class="label">Etapa Actual:</td>
          <td class="valor">' . ucfirst(str_replace('-', ' ', $this->batch->etapa_seleccionada ?: 'batch')) . '</td>
        </tr>
      </table>';

    // Condiciones ambientales si existen
    if($this->batch->temperatura_ambiente_promedio || $this->batch->humedad_relativa_promedio) {
      $html .= '
      <h3>Condiciones Ambientales Registradas</h3>
      <table class="tabla-info">
        <tr>
          <td class="label">Temperatura Ambiente Promedio:</td>
          <td class="valor">' . ($this->batch->temperatura_ambiente_promedio ? $this->batch->temperatura_ambiente_promedio . '°C' : 'No registrada') . '</td>
          <td class="label">Humedad Relativa Promedio:</td>
          <td class="valor">' . ($this->batch->humedad_relativa_promedio ? $this->batch->humedad_relativa_promedio . '%' : 'No registrada') . '</td>
        </tr>
      </table>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarInsumosPorEtapa() {
    if(empty($this->insumos)) {
      return '';
    }

    // Agrupar por etapa
    $insumosPorEtapa = [];
    foreach($this->insumos as $bi) {
      $etapa = $bi->etapa ?: 'General';
      if(!isset($insumosPorEtapa[$etapa])) {
        $insumosPorEtapa[$etapa] = [];
      }
      $insumosPorEtapa[$etapa][] = $bi;
    }

    $html = '
    <div class="section">
      <h2>2. Insumos Utilizados por Etapa</h2>';

    $etapaOrden = ['maceracion', 'coccion', 'lupulizacion', 'fermentacion', 'maduracion', 'General'];
    $etapasOrdenadas = [];
    foreach($etapaOrden as $e) {
      if(isset($insumosPorEtapa[$e])) {
        $etapasOrdenadas[$e] = $insumosPorEtapa[$e];
      }
    }
    // Agregar etapas que no están en el orden predefinido
    foreach($insumosPorEtapa as $e => $items) {
      if(!isset($etapasOrdenadas[$e])) {
        $etapasOrdenadas[$e] = $items;
      }
    }

    foreach($etapasOrdenadas as $etapa => $items) {
      $etapaNombre = ucfirst(str_replace('_', ' ', $etapa));

      $html .= '
      <div class="etapa-insumos">
        <h3>' . htmlspecialchars($etapaNombre) . '</h3>
        <table class="tabla-insumos">
          <thead>
            <tr>
              <th>#</th>
              <th>Insumo</th>
              <th>Tipo</th>
              <th>Cantidad</th>
              <th>Fecha Uso</th>
            </tr>
          </thead>
          <tbody>';

      $idx = 1;
      foreach($items as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $tipo = $insumo->id_tipos_de_insumos ? new TipoDeInsumo($insumo->id_tipos_de_insumos) : null;
        $fechaUso = $this->formatearFecha($bi->date);

        $html .= '
            <tr>
              <td>' . $idx . '</td>
              <td>' . htmlspecialchars($insumo->nombre) . '</td>
              <td>' . ($tipo ? htmlspecialchars($tipo->nombre) : '-') . '</td>
              <td><strong>' . $bi->cantidad . ' ' . htmlspecialchars($insumo->unidad_de_medida ?: '') . '</strong></td>
              <td>' . $fechaUso . '</td>
            </tr>';
        $idx++;
      }

      $html .= '</tbody></table></div>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarEtapaLicor() {
    if(!$this->batch->licor_temperatura && !$this->batch->licor_litros && !$this->batch->licor_ph) {
      return '';
    }

    return '
    <div class="section etapa-section">
      <h2>3. Etapa: Preparacion del Licor</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Preparar el agua de elaboracion</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">Volumen de Agua:</td>
            <td class="param-valor">' . ($this->batch->licor_litros ?: 'No especificado') . ' Litros</td>
          </tr>
          <tr>
            <td class="param-label">Temperatura Objetivo:</td>
            <td class="param-valor">' . ($this->batch->licor_temperatura ?: 'No especificada') . ' °C</td>
          </tr>
          <tr>
            <td class="param-label">pH del Agua:</td>
            <td class="param-valor">' . ($this->batch->licor_ph ?: 'No medido') . '</td>
          </tr>
        </table>
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Llenar el tanque de licor con ' . ($this->batch->licor_litros ?: 'la cantidad especificada de') . ' litros de agua</li>
            <li>Calentar hasta alcanzar ' . ($this->batch->licor_temperatura ?: 'la temperatura objetivo de') . '°C</li>
            <li>Verificar y ajustar pH si es necesario (objetivo: ' . ($this->batch->licor_ph ?: 'según receta') . ')</li>
            <li>Mantener temperatura estable antes de iniciar maceración</li>
          </ol>
        </div>
      </div>
    </div>';
  }

  private function generarEtapaMaceracion() {
    if(!$this->batch->maceracion_temperatura && !$this->batch->maceracion_hora_inicio) {
      return '';
    }

    $duracion = '';
    if($this->batch->maceracion_hora_inicio && $this->batch->maceracion_hora_finalizacion) {
      $inicio = strtotime($this->batch->maceracion_hora_inicio);
      $fin = strtotime($this->batch->maceracion_hora_finalizacion);
      $diff = ($fin - $inicio) / 60;
      if($diff > 0) {
        $duracion = $diff . ' minutos';
      }
    }

    // Obtener insumos de maceración
    $insumosMaceracion = array_filter($this->insumos, function($i) {
      return $i->etapa == 'maceracion';
    });

    $html = '
    <div class="section etapa-section">
      <h2>4. Etapa: Maceracion</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Conversion de almidones en azucares fermentables</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">Hora de Inicio:</td>
            <td class="param-valor">' . ($this->batch->maceracion_hora_inicio ?: 'No registrada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Hora de Finalizacion:</td>
            <td class="param-valor">' . ($this->batch->maceracion_hora_finalizacion ?: 'No registrada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Duracion Total:</td>
            <td class="param-valor">' . ($duracion ?: 'No calculada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Temperatura:</td>
            <td class="param-valor">' . ($this->batch->maceracion_temperatura ?: 'No especificada') . ' °C</td>
          </tr>
          <tr>
            <td class="param-label">Volumen de Mosto:</td>
            <td class="param-valor">' . ($this->batch->maceracion_litros ?: 'No registrado') . ' L</td>
          </tr>
          <tr>
            <td class="param-label">pH:</td>
            <td class="param-valor">' . ($this->batch->maceracion_ph ?: 'No medido') . '</td>
          </tr>
        </table>';

    if(!empty($insumosMaceracion)) {
      $html .= '
        <div class="instruccion-pasos">
          <p><strong>Granos utilizados:</strong></p>
          <ul>';
      foreach($insumosMaceracion as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $html .= '<li>' . htmlspecialchars($insumo->nombre) . ': <strong>' . $bi->cantidad . ' ' . htmlspecialchars($insumo->unidad_de_medida ?: '') . '</strong></li>';
      }
      $html .= '</ul></div>';
    }

    $html .= '
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Agregar los granos al agua caliente a ' . ($this->batch->maceracion_temperatura ?: 'temperatura objetivo') . '°C</li>
            <li>Revolver para evitar grumos y asegurar hidratacion uniforme</li>
            <li>Mantener temperatura constante durante todo el proceso</li>
            <li>Realizar prueba de yodo para verificar conversion completa</li>
            <li>Registrar pH final: ' . ($this->batch->maceracion_ph ?: 'según medición') . '</li>
          </ol>
        </div>
      </div>
    </div>';

    return $html;
  }

  private function generarEtapaLavado() {
    if(!$this->batch->lavado_de_granos_mosto && !$this->batch->lavado_de_granos_hora_inicio) {
      return '';
    }

    $duracion = '';
    if($this->batch->lavado_de_granos_hora_inicio && $this->batch->lavado_de_granos_hora_termino) {
      $inicio = strtotime($this->batch->lavado_de_granos_hora_inicio);
      $fin = strtotime($this->batch->lavado_de_granos_hora_termino);
      $diff = ($fin - $inicio) / 60;
      if($diff > 0) {
        $duracion = $diff . ' minutos';
      }
    }

    return '
    <div class="section etapa-section">
      <h2>5. Etapa: Lavado de Granos (Sparging)</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Extraer azucares residuales del grano</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">Hora de Inicio:</td>
            <td class="param-valor">' . ($this->batch->lavado_de_granos_hora_inicio ?: 'No registrada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Hora de Termino:</td>
            <td class="param-valor">' . ($this->batch->lavado_de_granos_hora_termino ?: 'No registrada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Duracion:</td>
            <td class="param-valor">' . ($duracion ?: 'No calculada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Mosto Obtenido:</td>
            <td class="param-valor">' . ($this->batch->lavado_de_granos_mosto ?: 'No registrado') . ' Litros</td>
          </tr>
          <tr>
            <td class="param-label">Densidad:</td>
            <td class="param-valor">' . ($this->batch->lavado_de_granos_densidad ?: 'No medida') . ' ' . ($this->batch->lavado_de_granos_tipo_de_densidad ?: '') . '</td>
          </tr>
        </table>
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Recircular el mosto hasta que salga claro (vorlauf)</li>
            <li>Iniciar lavado con agua a 76-78°C</li>
            <li>Mantener flujo constante y suave</li>
            <li>Medir densidad del ultimo lavado (detener cuando baje de 1.010)</li>
            <li>Total de mosto recolectado: ' . ($this->batch->lavado_de_granos_mosto ?: 'según registro') . ' L</li>
          </ol>
        </div>
      </div>
    </div>';
  }

  private function generarEtapaCoccion() {
    if(!$this->batch->coccion_ph_inicial && !$this->batch->coccion_ph_final && !$this->batch->combustible_gas) {
      return '';
    }

    // Obtener insumos de cocción
    $insumosCoccion = array_filter($this->insumos, function($i) {
      return $i->etapa == 'coccion';
    });

    $html = '
    <div class="section etapa-section">
      <h2>6. Etapa: Coccion (Hervido)</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Esterilizar, isomerizar lupulos y concentrar mosto</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">pH Inicial:</td>
            <td class="param-valor">' . ($this->batch->coccion_ph_inicial ?: 'No medido') . '</td>
          </tr>
          <tr>
            <td class="param-label">pH Final:</td>
            <td class="param-valor">' . ($this->batch->coccion_ph_final ?: 'No medido') . '</td>
          </tr>
          <tr>
            <td class="param-label">Recircular:</td>
            <td class="param-valor">' . ($this->batch->coccion_recilar ? 'Si' : 'No') . '</td>
          </tr>
          <tr>
            <td class="param-label">Combustible (Gas):</td>
            <td class="param-valor">' . ($this->batch->combustible_gas ?: 'No registrado') . '</td>
          </tr>
        </table>';

    if(!empty($insumosCoccion)) {
      $html .= '
        <div class="instruccion-pasos">
          <p><strong>Adiciones durante coccion:</strong></p>
          <ul>';
      foreach($insumosCoccion as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $html .= '<li>' . htmlspecialchars($insumo->nombre) . ': <strong>' . $bi->cantidad . ' ' . htmlspecialchars($insumo->unidad_de_medida ?: '') . '</strong></li>';
      }
      $html .= '</ul></div>';
    }

    $html .= '
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Llevar el mosto a ebullicion vigorosa</li>
            <li>Realizar adiciones de lupulo segun cronograma</li>
            <li>Mantener hervido durante el tiempo especificado</li>
            <li>Agregar clarificantes si aplica</li>
            <li>Apagar fuego al completar el hervido</li>
          </ol>
        </div>
      </div>
    </div>';

    return $html;
  }

  private function generarLupulizacionesDetalladas() {
    if(empty($this->lupulizaciones)) {
      return '';
    }

    // Obtener insumos de lupulización
    $insumosLupulizacion = array_filter($this->insumos, function($i) {
      return $i->etapa == 'lupulizacion';
    });

    $html = '
    <div class="section etapa-section">
      <h2>7. Lupulizaciones</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Registro cronologico de adiciones de lupulo</p>';

    if(!empty($insumosLupulizacion)) {
      $html .= '
        <h3>Lupulos Utilizados</h3>
        <table class="tabla-insumos">
          <thead>
            <tr>
              <th>Lupulo</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>';
      foreach($insumosLupulizacion as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $html .= '<tr>
              <td>' . htmlspecialchars($insumo->nombre) . '</td>
              <td><strong>' . $bi->cantidad . ' ' . htmlspecialchars($insumo->unidad_de_medida ?: '') . '</strong></td>
            </tr>';
      }
      $html .= '</tbody></table>';
    }

    $html .= '
        <h3>Cronograma de Adiciones</h3>
        <table class="tabla-lupulizaciones">
          <thead>
            <tr>
              <th>N°</th>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Tipo</th>
              <th>Observacion</th>
            </tr>
          </thead>
          <tbody>';

    foreach($this->lupulizaciones as $l) {
      $html .= '
            <tr>
              <td><strong>' . ($l->seq_index + 1) . '</strong></td>
              <td>' . $this->formatearFecha($l->date) . '</td>
              <td>' . ($l->hora ?: '-') . '</td>
              <td>' . htmlspecialchars($l->tipo ?: 'No especificado') . '</td>
              <td>Adicion #' . ($l->seq_index + 1) . '</td>
            </tr>';
    }

    $html .= '</tbody></table></div></div>';
    return $html;
  }

  private function generarEnfriadosDetallados() {
    if(empty($this->enfriados)) {
      return '';
    }

    $html = '
    <div class="section etapa-section">
      <h2>8. Enfriado del Mosto</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Reducir temperatura para inoculacion de levadura</p>
        <table class="tabla-enfriados">
          <thead>
            <tr>
              <th>N°</th>
              <th>Fecha</th>
              <th>Hora Inicio</th>
              <th>Temp. Inicio</th>
              <th>pH Previo</th>
              <th>Densidad</th>
              <th>pH Enfriado</th>
            </tr>
          </thead>
          <tbody>';

    foreach($this->enfriados as $e) {
      $html .= '
            <tr>
              <td><strong>' . ($e->seq_index + 1) . '</strong></td>
              <td>' . $this->formatearFecha($e->date) . '</td>
              <td>' . ($e->hora_inicio ?: '-') . '</td>
              <td>' . ($e->temperatura_inicio ?: '-') . '°C</td>
              <td>' . ($e->ph ?: '-') . '</td>
              <td>' . ($e->densidad ?: '-') . '</td>
              <td>' . ($e->ph_enfriado ?: '-') . '</td>
            </tr>';
    }

    $html .= '</tbody></table>
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Activar sistema de enfriamiento (intercambiador de placas o serpentin)</li>
            <li>Enfriar lo mas rapido posible para evitar contaminacion</li>
            <li>Objetivo: Alcanzar temperatura de inoculacion (' . ($this->batch->inoculacion_temperatura ?: '18-22') . '°C)</li>
            <li>Registrar parametros durante el proceso</li>
          </ol>
        </div>
      </div>
    </div>';

    return $html;
  }

  private function generarInoculacionYLevadura() {
    $html = '
    <div class="section etapa-section">
      <h2>9. Inoculacion de Levadura</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Agregar levadura al mosto enfriado</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">Temperatura de Inoculacion:</td>
            <td class="param-valor">' . ($this->batch->inoculacion_temperatura ?: 'No registrada') . ' °C</td>
          </tr>
          <tr>
            <td class="param-label">Temperatura Inicio:</td>
            <td class="param-valor">' . ($this->batch->inoculacion_temperatura_inicio ?: 'No registrada') . ' °C</td>
          </tr>
        </table>';

    // Obtener insumos de fermentación (levaduras)
    $insumosLevadura = array_filter($this->insumos, function($i) {
      return $i->etapa == 'fermentacion';
    });

    if(!empty($insumosLevadura)) {
      $html .= '
        <h3>Levaduras Utilizadas</h3>
        <table class="tabla-insumos">
          <thead>
            <tr>
              <th>Levadura</th>
              <th>Cantidad</th>
            </tr>
          </thead>
          <tbody>';
      foreach($insumosLevadura as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $html .= '<tr>
              <td>' . htmlspecialchars($insumo->nombre) . '</td>
              <td><strong>' . $bi->cantidad . ' ' . htmlspecialchars($insumo->unidad_de_medida ?: '') . '</strong></td>
            </tr>';
      }
      $html .= '</tbody></table>';
    }

    // Datos detallados de levadura (BatchLevadura)
    if(!empty($this->levaduras)) {
      $html .= '<h3>Datos Detallados de Levadura</h3>';

      foreach($this->levaduras as $lev) {
        $batchInsumo = $lev->id_batches_insumos ? new BatchInsumo($lev->id_batches_insumos) : null;
        $insumoNombre = '';
        if($batchInsumo) {
          $insumo = new Insumo($batchInsumo->id_insumos);
          $insumoNombre = $insumo->nombre;
        }
        $origenBatch = $lev->origen_batch ? new Batch($lev->origen_batch) : null;

        $html .= '
        <div class="levadura-detalle">
          <table class="tabla-parametros">';

        if($insumoNombre) {
          $html .= '<tr>
              <td class="param-label">Cepa:</td>
              <td class="param-valor">' . htmlspecialchars($insumoNombre) . '</td>
            </tr>';
        }

        $html .= '
            <tr>
              <td class="param-label">Generacion:</td>
              <td class="param-valor">' . $lev->generacion . 'a generacion</td>
            </tr>';

        if($origenBatch) {
          $html .= '
            <tr>
              <td class="param-label">Origen (Reutilizada de):</td>
              <td class="param-valor">Batch #' . htmlspecialchars($origenBatch->batch_nombre) . '</td>
            </tr>';
        }

        $html .= '
            <tr>
              <td class="param-label">Cantidad:</td>
              <td class="param-valor">' . $lev->cantidad_gramos . ' gramos</td>
            </tr>
            <tr>
              <td class="param-label">Tasa de Inoculacion:</td>
              <td class="param-valor">' . ($lev->tasa_inoculacion ?: 'No calculada') . '</td>
            </tr>
            <tr>
              <td class="param-label">Viabilidad Medida:</td>
              <td class="param-valor">' . ($lev->viabilidad_medida ? $lev->viabilidad_medida . '%' : 'No medida') . '</td>
            </tr>
            <tr>
              <td class="param-label">Vitalidad Medida:</td>
              <td class="param-valor">' . ($lev->vitalidad_medida ? $lev->vitalidad_medida . '%' : 'No medida') . '</td>
            </tr>
            <tr>
              <td class="param-label">Uso de Starter:</td>
              <td class="param-valor">' . ($lev->uso_starter ? 'Si (' . $lev->volumen_starter_ml . ' ml)' : 'No') . '</td>
            </tr>
            <tr>
              <td class="param-label">Atenuacion Real:</td>
              <td class="param-valor">' . ($lev->atenuacion_real ? $lev->atenuacion_real . '%' : 'No medida') . '</td>
            </tr>
            <tr>
              <td class="param-label">Tiempo Lag:</td>
              <td class="param-valor">' . ($lev->tiempo_lag_h ? $lev->tiempo_lag_h . ' horas' : 'No registrado') . '</td>
            </tr>';

        if(!empty($lev->observaciones)) {
          $html .= '
            <tr>
              <td class="param-label">Observaciones:</td>
              <td class="param-valor">' . nl2br(htmlspecialchars($lev->observaciones)) . '</td>
            </tr>';
        }

        $html .= '</table></div>';
      }
    }

    $html .= '
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Verificar temperatura del mosto (debe estar a ' . ($this->batch->inoculacion_temperatura ?: '18-22') . '°C)</li>
            <li>Rehidratar levadura seca si aplica, o preparar starter</li>
            <li>Oxigenar el mosto antes de inocular</li>
            <li>Agregar levadura y mezclar suavemente</li>
            <li>Sellar fermentador y colocar airlock</li>
          </ol>
        </div>
      </div>
    </div>';

    return $html;
  }

  private function generarFermentacionDetallada() {
    if(!$this->batch->fermentacion_date && !$this->batch->fermentacion_temperatura) {
      return '';
    }

    $duracion = '';
    if($this->batch->fermentacion_date && $this->batch->fermentacion_finalizada_datetime) {
      $inicio = strtotime($this->batch->fermentacion_date);
      $fin = strtotime($this->batch->fermentacion_finalizada_datetime);
      $dias = floor(($fin - $inicio) / 86400);
      if($dias > 0) {
        $duracion = $dias . ' dias';
      }
    }

    $html = '
    <div class="section etapa-section">
      <h2>10. Fermentacion</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Conversion de azucares en alcohol y CO2</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">Fecha de Inicio:</td>
            <td class="param-valor">' . $this->formatearFecha($this->batch->fermentacion_date) . '</td>
          </tr>
          <tr>
            <td class="param-label">Hora de Inicio:</td>
            <td class="param-valor">' . ($this->batch->fermentacion_hora_inicio ?: 'No registrada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Temperatura:</td>
            <td class="param-valor">' . ($this->batch->fermentacion_temperatura ?: 'No registrada') . ' °C</td>
          </tr>
          <tr>
            <td class="param-label">pH:</td>
            <td class="param-valor">' . ($this->batch->fermentacion_ph ?: 'No medido') . '</td>
          </tr>
          <tr>
            <td class="param-label">Densidad Inicial:</td>
            <td class="param-valor">' . ($this->batch->fermentacion_densidad ?: 'No medida') . ' ' . ($this->batch->fermentacion_tipo_de_densidad ?: '') . '</td>
          </tr>
          <tr>
            <td class="param-label">Estado:</td>
            <td class="param-valor">' . ($this->batch->fermentacion_finalizada ? '<span class="estado-ok">FINALIZADA</span>' : '<span class="estado-proceso">En proceso</span>') . '</td>
          </tr>';

    if($this->batch->fermentacion_finalizada) {
      $html .= '
          <tr>
            <td class="param-label">Fecha Finalizacion:</td>
            <td class="param-valor">' . $this->formatearFechaHora($this->batch->fermentacion_finalizada_datetime) . '</td>
          </tr>
          <tr>
            <td class="param-label">Duracion Total:</td>
            <td class="param-valor">' . ($duracion ?: 'No calculada') . '</td>
          </tr>';
    }

    $html .= '</table>';

    // Activos/Fermentadores utilizados
    if(!empty($this->activos)) {
      $html .= '
        <h3>Equipos de Fermentacion Utilizados</h3>
        <table class="tabla-insumos">
          <thead>
            <tr>
              <th>Fermentador</th>
              <th>Capacidad</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>';

      foreach($this->activos as $ba) {
        $activo = new Activo($ba->id_activos);
        $html .= '
            <tr>
              <td>' . htmlspecialchars($activo->nombre ?: 'Sin nombre') . '</td>
              <td>' . ($ba->litraje ?: $activo->litraje ?: '-') . ' L</td>
              <td>' . htmlspecialchars($ba->estado ?: 'Activo') . '</td>
            </tr>';
      }

      $html .= '</tbody></table>';
    }

    $html .= '
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Mantener temperatura constante a ' . ($this->batch->fermentacion_temperatura ?: '18-22') . '°C</li>
            <li>Monitorear actividad del airlock (primeras 24-48h)</li>
            <li>Realizar mediciones de densidad cada 2-3 dias</li>
            <li>Fermentacion completa cuando densidad se estabilice por 3 dias</li>
            <li>Registrar densidad final y calcular atenuacion</li>
          </ol>
        </div>
      </div>
    </div>';

    return $html;
  }

  private function generarTraspasosDetallados() {
    if(empty($this->traspasos)) {
      return '';
    }

    $html = '
    <div class="section etapa-section">
      <h2>11. Traspasos entre Tanques</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Registro de movimientos de liquido entre equipos</p>
        <table class="tabla-traspasos">
          <thead>
            <tr>
              <th>N°</th>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Origen</th>
              <th>Destino</th>
              <th>Cantidad</th>
              <th>Merma</th>
            </tr>
          </thead>
          <tbody>';

    foreach($this->traspasos as $t) {
      $origen = $t->id_fermentadores_inicio ? new Activo($t->id_fermentadores_inicio) : null;
      $destino = $t->id_fermentadores_final ? new Activo($t->id_fermentadores_final) : null;

      $html .= '
            <tr>
              <td><strong>' . ($t->seq_index + 1) . '</strong></td>
              <td>' . $this->formatearFecha($t->date) . '</td>
              <td>' . ($t->hora ?: '-') . '</td>
              <td>' . ($origen ? htmlspecialchars($origen->nombre) : 'No especificado') . '</td>
              <td>' . ($destino ? htmlspecialchars($destino->nombre) : 'No especificado') . '</td>
              <td><strong>' . ($t->cantidad ?: '-') . ' L</strong></td>
              <td>' . ($t->merma_litros ? $t->merma_litros . ' L' : '-') . '</td>
            </tr>';
    }

    $html .= '</tbody></table>
        <div class="instruccion-pasos">
          <p><strong>Procedimiento para traspasos:</strong></p>
          <ol>
            <li>Sanitizar equipo de destino y mangueras</li>
            <li>Evitar incorporar oxigeno durante el traspaso</li>
            <li>Dejar sedimentos en el tanque de origen</li>
            <li>Registrar volumen traspasado y merma</li>
          </ol>
        </div>
      </div>
    </div>';

    return $html;
  }

  private function generarMaduracionDetallada() {
    if(!$this->batch->maduracion_date && !$this->batch->maduracion_temperatura_inicio) {
      return '';
    }

    return '
    <div class="section etapa-section">
      <h2>12. Maduracion</h2>
      <div class="instruccion-box">
        <p class="instruccion-titulo">Objetivo: Clarificacion y desarrollo de sabores</p>
        <table class="tabla-parametros">
          <tr>
            <td class="param-label">Fecha de Inicio:</td>
            <td class="param-valor">' . $this->formatearFecha($this->batch->maduracion_date) . '</td>
          </tr>
          <tr>
            <td class="param-label">Hora de Inicio:</td>
            <td class="param-valor">' . ($this->batch->maduracion_hora_inicio ?: 'No registrada') . '</td>
          </tr>
          <tr>
            <td class="param-label">Temperatura Inicial:</td>
            <td class="param-valor">' . ($this->batch->maduracion_temperatura_inicio ?: 'No registrada') . ' °C</td>
          </tr>
          <tr>
            <td class="param-label">Temperatura Final:</td>
            <td class="param-valor">' . ($this->batch->maduracion_temperatura_finalizacion ?: 'No registrada') . ' °C</td>
          </tr>
          <tr>
            <td class="param-label">Hora de Finalizacion:</td>
            <td class="param-valor">' . ($this->batch->maduracion_hora_finalizacion ?: 'No registrada') . '</td>
          </tr>
        </table>
        <div class="instruccion-pasos">
          <p><strong>Procedimiento:</strong></p>
          <ol>
            <li>Reducir gradualmente temperatura (1-2°C por dia)</li>
            <li>Mantener a temperatura de maduracion por tiempo especificado</li>
            <li>Verificar clarificacion visual</li>
            <li>Realizar prueba de sabor antes de envasar</li>
          </ol>
        </div>
      </div>
    </div>';
  }

  private function generarMetricasFinales() {
    $tieneMetricas = $this->batch->abv_final || $this->batch->ibu_final ||
                     $this->batch->color_ebc || $this->batch->rendimiento_litros_final ||
                     $this->batch->calificacion_sensorial || $this->batch->densidad_final_verificada;

    if(!$tieneMetricas && !$this->batch->datetime_finalizacion) {
      return '';
    }

    $eficiencia = $this->batch->calcularEficiencia();
    $mermaPct = $this->batch->calcularMermaPorcentual();

    $html = '
    <div class="section">
      <h2>13. Metricas y Resultados Finales</h2>';

    if($this->batch->datetime_finalizacion && $this->batch->datetime_finalizacion != '0000-00-00 00:00:00') {
      $html .= '
      <div class="finalizacion-box">
        <p><strong>BATCH FINALIZADO:</strong> ' . $this->formatearFechaHora($this->batch->datetime_finalizacion) . '</p>
      </div>';
    }

    $html .= '
      <table class="tabla-metricas">
        <tr>
          <th colspan="4">Parametros del Producto Final</th>
        </tr>
        <tr>
          <td class="metrica-label">ABV Final:</td>
          <td class="metrica-valor">' . ($this->batch->abv_final ? $this->batch->abv_final . '%' : 'No medido') . '</td>
          <td class="metrica-label">IBU:</td>
          <td class="metrica-valor">' . ($this->batch->ibu_final ?: 'No calculado') . '</td>
        </tr>
        <tr>
          <td class="metrica-label">Color EBC:</td>
          <td class="metrica-valor">' . ($this->batch->color_ebc ?: 'No medido') . '</td>
          <td class="metrica-label">Densidad Final:</td>
          <td class="metrica-valor">' . ($this->batch->densidad_final_verificada ?: 'No verificada') . '</td>
        </tr>
        <tr>
          <th colspan="4">Rendimiento</th>
        </tr>
        <tr>
          <td class="metrica-label">Volumen Objetivo:</td>
          <td class="metrica-valor">' . number_format($this->batch->batch_litros, 1) . ' L</td>
          <td class="metrica-label">Volumen Final:</td>
          <td class="metrica-valor">' . ($this->batch->rendimiento_litros_final ? number_format($this->batch->rendimiento_litros_final, 1) . ' L' : 'No registrado') . '</td>
        </tr>
        <tr>
          <td class="metrica-label">Eficiencia:</td>
          <td class="metrica-valor">' . ($eficiencia ? $eficiencia . '%' : 'No calculada') . '</td>
          <td class="metrica-label">Merma Total:</td>
          <td class="metrica-valor">' . ($this->batch->merma_total_litros ? $this->batch->merma_total_litros . ' L (' . ($mermaPct ?: '?') . '%)' : 'No registrada') . '</td>
        </tr>';

    if($this->batch->calificacion_sensorial) {
      $html .= '
        <tr>
          <th colspan="4">Evaluacion Sensorial</th>
        </tr>
        <tr>
          <td class="metrica-label">Calificacion:</td>
          <td class="metrica-valor" colspan="3"><strong>' . $this->batch->calificacion_sensorial . '/10</strong></td>
        </tr>';
    }

    $html .= '</table>';

    if(!empty($this->batch->notas_cata)) {
      $html .= '
      <div class="notas-cata">
        <h3>Notas de Cata</h3>
        <p>' . nl2br(htmlspecialchars($this->batch->notas_cata)) . '</p>
      </div>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarObservacionesYNotas() {
    if(empty($this->batch->observaciones)) {
      return '';
    }

    return '
    <div class="section">
      <h2>14. Observaciones Generales</h2>
      <div class="observaciones-box">
        ' . nl2br(htmlspecialchars($this->batch->observaciones)) . '
      </div>
    </div>';
  }

  private function generarPie() {
    return '
    <div class="footer">
      <p><strong>Cerveza Cocholgue</strong> - Instrucciones de Elaboracion Batch #' . htmlspecialchars($this->batch->batch_nombre) . '</p>
      <p class="small">Documento generado automaticamente el ' . date('d/m/Y') . ' a las ' . date('H:i:s') . '</p>
      <p class="small">Sistema de Trazabilidad y Control de Produccion</p>
    </div>';
  }

  // Helpers
  private function formatearFecha($fecha) {
    if(!$fecha || $fecha == '0000-00-00') return '-';
    return date('d/m/Y', strtotime($fecha));
  }

  private function formatearFechaHora($datetime) {
    if(!$datetime || $datetime == '0000-00-00 00:00:00') return '-';
    return date('d/m/Y H:i', strtotime($datetime));
  }

  private function getCSS() {
    return '
      @page {
        margin: 15mm 12mm;
      }
      body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 9px;
        line-height: 1.4;
        color: #2c3e50;
        margin: 0;
        padding: 0;
      }
      .page-break {
        page-break-after: always;
      }

      /* Portada */
      .portada {
        text-align: center;
        padding: 40px 20px;
      }
      .portada-header h1 {
        font-size: 22px;
        color: #c0392b;
        margin: 0 0 10px 0;
        letter-spacing: 2px;
      }
      .batch-numero {
        font-size: 28px;
        font-weight: bold;
        color: #2c3e50;
        margin: 20px 0;
      }
      .portada-receta {
        font-size: 16px;
        color: #16a085;
        font-weight: bold;
        margin: 10px 0;
      }
      .portada-linea {
        font-size: 12px;
        color: #8e44ad;
        margin-bottom: 30px;
      }
      .portada-datos {
        margin: 30px auto;
        text-align: left;
        width: 60%;
      }
      .portada-datos td {
        padding: 8px 10px;
        border-bottom: 1px solid #ecf0f1;
      }
      .portada-footer {
        margin-top: 50px;
        color: #95a5a6;
        font-size: 9px;
      }

      /* Secciones */
      .section {
        margin: 12px 0;
        page-break-inside: avoid;
      }
      .section h2 {
        font-size: 12px;
        color: #c0392b;
        border-bottom: 2px solid #c0392b;
        padding-bottom: 4px;
        margin: 0 0 10px 0;
      }
      .section h3 {
        font-size: 10px;
        color: #2980b9;
        margin: 10px 0 6px 0;
      }
      .etapa-section {
        background: #fafafa;
        padding: 10px;
        border-radius: 4px;
        margin: 15px 0;
      }

      /* Timeline */
      .timeline {
        border-left: 3px solid #3498db;
        padding-left: 15px;
        margin-left: 10px;
      }
      .timeline-item {
        position: relative;
        margin-bottom: 8px;
        padding: 6px 10px;
        background: #f8f9fa;
        border-radius: 3px;
      }
      .timeline-item:before {
        content: "";
        position: absolute;
        left: -21px;
        top: 10px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #3498db;
      }
      .timeline-inicio:before { background: #27ae60; }
      .timeline-maceracion:before { background: #e67e22; }
      .timeline-lavado:before { background: #9b59b6; }
      .timeline-lupulizacion:before { background: #2ecc71; }
      .timeline-enfriado:before { background: #00bcd4; }
      .timeline-fermentacion:before { background: #f39c12; }
      .timeline-traspaso:before { background: #e74c3c; }
      .timeline-maduracion:before { background: #1abc9c; }
      .timeline-finalizacion:before { background: #2c3e50; }
      .timeline-fecha {
        font-size: 8px;
        color: #7f8c8d;
      }
      .timeline-titulo {
        font-weight: bold;
        font-size: 9px;
      }
      .timeline-desc {
        font-size: 8px;
        color: #555;
      }

      /* Tablas */
      .tabla-info, .tabla-parametros, .tabla-metricas {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
      }
      .tabla-info td {
        padding: 5px 8px;
        border: 1px solid #ecf0f1;
      }
      .tabla-info .label, .tabla-parametros .param-label, .tabla-metricas .metrica-label {
        background: #f5f6fa;
        font-weight: bold;
        width: 25%;
        color: #34495e;
      }
      .tabla-info .valor, .tabla-parametros .param-valor, .tabla-metricas .metrica-valor {
        width: 25%;
      }
      .tabla-parametros td, .tabla-metricas td {
        padding: 5px 8px;
        border: 1px solid #ecf0f1;
      }
      .tabla-metricas th {
        background: #2c3e50;
        color: white;
        padding: 6px;
        text-align: left;
        font-size: 9px;
      }

      .tabla-insumos, .tabla-lupulizaciones, .tabla-enfriados, .tabla-traspasos {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
      }
      .tabla-insumos th, .tabla-lupulizaciones th, .tabla-enfriados th, .tabla-traspasos th {
        background: #34495e;
        color: white;
        padding: 5px 6px;
        text-align: left;
        font-size: 8px;
      }
      .tabla-insumos td, .tabla-lupulizaciones td, .tabla-enfriados td, .tabla-traspasos td {
        padding: 4px 6px;
        border-bottom: 1px solid #ecf0f1;
        font-size: 8px;
      }

      /* Instrucciones */
      .instruccion-box {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
      }
      .instruccion-titulo {
        font-style: italic;
        color: #7f8c8d;
        margin: 0 0 10px 0;
      }
      .instruccion-pasos {
        background: #e8f6f3;
        padding: 8px 10px;
        border-radius: 3px;
        margin-top: 10px;
      }
      .instruccion-pasos ol, .instruccion-pasos ul {
        margin: 5px 0;
        padding-left: 20px;
      }
      .instruccion-pasos li {
        margin: 3px 0;
      }

      /* Levadura detalle */
      .levadura-detalle {
        background: #fef9e7;
        border: 1px solid #f1c40f;
        border-radius: 4px;
        padding: 8px;
        margin: 8px 0;
      }

      /* Estados */
      .estado-ok {
        background: #27ae60;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 8px;
      }
      .estado-proceso {
        background: #f39c12;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 8px;
      }

      /* Boxes especiales */
      .finalizacion-box {
        background: #d5f5e3;
        border: 2px solid #27ae60;
        padding: 10px;
        text-align: center;
        border-radius: 4px;
        margin-bottom: 15px;
      }
      .notas-cata {
        background: #fef9e7;
        border: 1px solid #f1c40f;
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
      }
      .observaciones-box {
        background: #ebf5fb;
        border: 1px solid #3498db;
        padding: 10px;
        border-radius: 4px;
      }

      /* Etapa insumos */
      .etapa-insumos {
        margin: 10px 0;
        padding: 8px;
        background: #f8f9fa;
        border-left: 3px solid #3498db;
      }
      .etapa-insumos h3 {
        margin: 0 0 8px 0;
        color: #2980b9;
      }

      /* Footer */
      .footer {
        margin-top: 20px;
        text-align: center;
        color: #7f8c8d;
        font-size: 8px;
        border-top: 2px solid #c0392b;
        padding-top: 10px;
      }
      .footer .small {
        font-size: 7px;
        margin: 2px 0;
      }
    ';
  }
}
