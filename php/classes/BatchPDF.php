<?php
/**
 * BatchPDF - Generador de PDF de informe completo de batch
 * Genera un PDF con toda la información del proceso de producción
 */

class BatchPDF {

  private $batch;
  private $receta;

  public function __construct(Batch $batch) {
    $this->batch = $batch;
    $this->receta = $batch->id_recetas ? new Receta($batch->id_recetas) : null;
  }

  /**
   * Descarga el PDF directamente
   * @param string $filename
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
      $filename = 'Batch_' . $this->batch->batch_nombre . '_' . date('Ymd') . '.pdf';
    }

    $dompdf->stream($filename, array('Attachment' => true));
  }

  /**
   * Genera el HTML del PDF
   * @return string
   */
  private function generarHTML() {
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    $html .= '<style>' . $this->getCSS() . '</style>';
    $html .= '</head><body>';

    // Encabezado
    $html .= $this->generarEncabezado();

    // Información general
    $html .= $this->generarInfoGeneral();

    // Insumos utilizados
    $html .= $this->generarInsumos();

    // Etapas del proceso
    $html .= $this->generarEtapas();

    // Lupulizaciones
    $html .= $this->generarLupulizaciones();

    // Enfriados
    $html .= $this->generarEnfriados();

    // Traspasos
    $html .= $this->generarTraspasos();

    // Métricas finales
    $html .= $this->generarMetricasFinales();

    // Observaciones finales
    $html .= $this->generarObservaciones();

    // Pie de página
    $html .= $this->generarPie();

    $html .= '</body></html>';
    return $html;
  }

  private function generarEncabezado() {
    $recetaNombre = $this->receta ? $this->receta->nombre : 'Sin receta';
    $fechaBatch = $this->batch->batch_date && $this->batch->batch_date != '0000-00-00'
      ? date('d/m/Y', strtotime($this->batch->batch_date))
      : '-';

    return '
    <div class="header">
      <div class="header-left">
        <h1>INFORME DE BATCH</h1>
        <p class="batch-nombre">#' . htmlspecialchars($this->batch->batch_nombre) . '</p>
        <p class="batch-fecha">Fecha: ' . $fechaBatch . '</p>
      </div>
      <div class="header-right">
        <p class="receta">' . htmlspecialchars($recetaNombre) . '</p>
        <p class="linea">' . $this->batch->getLineaProductivaLabel() . '</p>
        <p class="fecha">Generado: ' . date('d/m/Y H:i') . '</p>
      </div>
    </div>
    <div class="separator"></div>';
  }

  private function generarInfoGeneral() {
    // Cocinero
    $cocinero = $this->batch->batch_id_usuarios_cocinero ? new Usuario($this->batch->batch_id_usuarios_cocinero) : null;
    $cocineroNombre = $cocinero ? $cocinero->nombre : '-';

    // Estado/Etapa
    $etapa = ucfirst(str_replace('-', ' ', $this->batch->etapa_seleccionada ?: 'batch'));

    $html = '<div class="section">
      <h2>Informacion General</h2>
      <table class="info-table">
        <tr>
          <td><strong>Batch:</strong></td>
          <td>#' . htmlspecialchars($this->batch->batch_nombre) . '</td>
          <td><strong>Receta:</strong></td>
          <td>' . ($this->receta ? htmlspecialchars($this->receta->nombre) : '-') . '</td>
        </tr>
        <tr>
          <td><strong>Cocinero:</strong></td>
          <td>' . htmlspecialchars($cocineroNombre) . '</td>
          <td><strong>Etapa Actual:</strong></td>
          <td><span class="estado">' . htmlspecialchars($etapa) . '</span></td>
        </tr>
        <tr>
          <td><strong>Volumen Objetivo:</strong></td>
          <td>' . number_format($this->batch->batch_litros, 1) . ' L</td>
          <td><strong>Linea Productiva:</strong></td>
          <td>' . $this->batch->getLineaProductivaLabel() . '</td>
        </tr>
      </table>
    </div>';

    return $html;
  }

  private function generarInsumos() {
    $insumos = BatchInsumo::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY etapa, etapa_index");
    if(empty($insumos)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Insumos Utilizados</h2>
      <table class="insumos-table">
        <thead>
          <tr>
            <th>Insumo</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Etapa</th>
          </tr>
        </thead>
        <tbody>';

    foreach($insumos as $bi) {
      $insumo = new Insumo($bi->id_insumos);
      $tipo = $insumo->id_tipos_de_insumos ? new TipoDeInsumo($insumo->id_tipos_de_insumos) : null;
      $tipoNombre = $tipo ? $tipo->nombre : '-';

      $html .= '<tr>
          <td>' . htmlspecialchars($insumo->nombre) . '</td>
          <td>' . htmlspecialchars($tipoNombre) . '</td>
          <td>' . $bi->cantidad . ' ' . htmlspecialchars($insumo->unidad_de_medida ?: '') . '</td>
          <td>' . htmlspecialchars(ucfirst($bi->etapa ?: 'General')) . '</td>
        </tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
  }

  private function generarEtapas() {
    $html = '<div class="section">
      <h2>Etapas del Proceso</h2>';

    // Licor
    if($this->batch->licor_temperatura > 0 || $this->batch->licor_litros > 0) {
      $html .= '<div class="etapa-box">
        <h3>Licor</h3>
        <p>Temperatura: ' . ($this->batch->licor_temperatura ?: '-') . ' C | ';
      $html .= 'Litros: ' . ($this->batch->licor_litros ?: '-') . ' L | ';
      $html .= 'pH: ' . ($this->batch->licor_ph ?: '-') . '</p>
      </div>';
    }

    // Maceración
    if($this->batch->maceracion_temperatura > 0 || $this->batch->maceracion_litros > 0) {
      $horaInicio = $this->batch->maceracion_hora_inicio ?: '-';
      $horaFin = $this->batch->maceracion_hora_finalizacion ?: '-';
      $html .= '<div class="etapa-box">
        <h3>Maceracion</h3>
        <p>Hora Inicio: ' . $horaInicio . ' | Hora Fin: ' . $horaFin . '</p>
        <p>Temperatura: ' . ($this->batch->maceracion_temperatura ?: '-') . ' C | ';
      $html .= 'Litros: ' . ($this->batch->maceracion_litros ?: '-') . ' L | ';
      $html .= 'pH: ' . ($this->batch->maceracion_ph ?: '-') . '</p>
      </div>';
    }

    // Lavado de Granos
    if($this->batch->lavado_de_granos_mosto > 0 || $this->batch->lavado_de_granos_densidad > 0) {
      $horaInicio = $this->batch->lavado_de_granos_hora_inicio ?: '-';
      $horaFin = $this->batch->lavado_de_granos_hora_termino ?: '-';
      $html .= '<div class="etapa-box">
        <h3>Lavado de Granos</h3>
        <p>Hora Inicio: ' . $horaInicio . ' | Hora Fin: ' . $horaFin . '</p>
        <p>Mosto: ' . ($this->batch->lavado_de_granos_mosto ?: '-') . ' L | ';
      $html .= 'Densidad: ' . ($this->batch->lavado_de_granos_densidad ?: '-') . ' ' . ($this->batch->lavado_de_granos_tipo_de_densidad ?: '') . '</p>
      </div>';
    }

    // Cocción
    if($this->batch->coccion_ph_inicial > 0 || $this->batch->coccion_ph_final > 0) {
      $html .= '<div class="etapa-box">
        <h3>Coccion</h3>
        <p>pH Inicial: ' . ($this->batch->coccion_ph_inicial ?: '-') . ' | ';
      $html .= 'pH Final: ' . ($this->batch->coccion_ph_final ?: '-') . '</p>';
      if($this->batch->tiempo_hervido_total_min) {
        $html .= '<p>Tiempo Hervido: ' . $this->batch->tiempo_hervido_total_min . ' min</p>';
      }
      $html .= '</div>';
    }

    // Inoculación
    if($this->batch->inoculacion_temperatura > 0) {
      $html .= '<div class="etapa-box">
        <h3>Inoculacion</h3>
        <p>Temperatura: ' . $this->batch->inoculacion_temperatura . ' C</p>
      </div>';
    }

    // Fermentación
    if($this->batch->fermentacion_date || $this->batch->fermentacion_temperatura > 0) {
      $fechaFerm = $this->batch->fermentacion_date && $this->batch->fermentacion_date != '0000-00-00'
        ? date('d/m/Y', strtotime($this->batch->fermentacion_date))
        : '-';
      $html .= '<div class="etapa-box">
        <h3>Fermentacion</h3>
        <p>Fecha: ' . $fechaFerm . ' | Hora Inicio: ' . ($this->batch->fermentacion_hora_inicio ?: '-') . '</p>
        <p>Temperatura: ' . ($this->batch->fermentacion_temperatura ?: '-') . ' C | ';
      $html .= 'pH: ' . ($this->batch->fermentacion_ph ?: '-') . ' | ';
      $html .= 'Densidad: ' . ($this->batch->fermentacion_densidad ?: '-') . ' ' . ($this->batch->fermentacion_tipo_de_densidad ?: '') . '</p>';
      if($this->batch->fermentacion_finalizada) {
        $html .= '<p class="finalizada">Fermentacion Finalizada</p>';
      }
      $html .= '</div>';
    }

    // Maduración
    if($this->batch->maduracion_date || $this->batch->maduracion_temperatura_inicio > 0) {
      $fechaMad = $this->batch->maduracion_date && $this->batch->maduracion_date != '0000-00-00'
        ? date('d/m/Y', strtotime($this->batch->maduracion_date))
        : '-';
      $html .= '<div class="etapa-box">
        <h3>Maduracion</h3>
        <p>Fecha: ' . $fechaMad . '</p>
        <p>Temp. Inicio: ' . ($this->batch->maduracion_temperatura_inicio ?: '-') . ' C | ';
      $html .= 'Temp. Fin: ' . ($this->batch->maduracion_temperatura_finalizacion ?: '-') . ' C</p>
      </div>';
    }

    // Finalización
    if($this->batch->datetime_finalizacion && $this->batch->datetime_finalizacion != '0000-00-00 00:00:00') {
      $fechaFin = date('d/m/Y H:i', strtotime($this->batch->datetime_finalizacion));
      $html .= '<div class="etapa-box etapa-finalizada">
        <h3>Finalizacion</h3>
        <p>Fecha/Hora: ' . $fechaFin . '</p>
      </div>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarLupulizaciones() {
    $lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY seq_index");
    if(empty($lupulizaciones)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Lupulizaciones</h2>
      <table class="traspasos-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Tipo</th>
            <th>Fecha</th>
            <th>Hora</th>
          </tr>
        </thead>
        <tbody>';

    foreach($lupulizaciones as $l) {
      $fecha = $l->date && $l->date != '0000-00-00' ? date('d/m/Y', strtotime($l->date)) : '-';
      $html .= '<tr>
          <td>' . ($l->seq_index + 1) . '</td>
          <td>' . htmlspecialchars($l->tipo ?: '-') . '</td>
          <td>' . $fecha . '</td>
          <td>' . ($l->hora ?: '-') . '</td>
        </tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
  }

  private function generarEnfriados() {
    $enfriados = BatchEnfriado::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY seq_index");
    if(empty($enfriados)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Enfriados</h2>
      <table class="traspasos-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Temp. Inicio</th>
            <th>pH</th>
            <th>Densidad</th>
            <th>pH Enfriado</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>';

    foreach($enfriados as $e) {
      $fecha = $e->date && $e->date != '0000-00-00' ? date('d/m/Y', strtotime($e->date)) : '-';
      $html .= '<tr>
          <td>' . ($e->seq_index + 1) . '</td>
          <td>' . ($e->temperatura_inicio ?: '-') . ' C</td>
          <td>' . ($e->ph ?: '-') . '</td>
          <td>' . ($e->densidad ?: '-') . '</td>
          <td>' . ($e->ph_enfriado ?: '-') . '</td>
          <td>' . $fecha . '</td>
        </tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
  }

  private function generarFermentadores() {
    $activos = BatchActivo::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "'");
    if(empty($activos)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Fermentadores Asignados</h2>
      <table class="traspasos-table">
        <thead>
          <tr>
            <th>Activo</th>
            <th>Litraje</th>
            <th>Linea</th>
          </tr>
        </thead>
        <tbody>';

    foreach($activos as $ba) {
      $activo = new Activo($ba->id_activos);
      $linea = method_exists($activo, 'getLineaProductivaLabel') ? $activo->getLineaProductivaLabel() : '-';
      $html .= '<tr>
          <td>' . htmlspecialchars($activo->nombre ?: '-') . '</td>
          <td>' . ($ba->litraje ?: $activo->litraje ?: '-') . ' L</td>
          <td>' . $linea . '</td>
        </tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
  }

  private function generarTraspasos() {
    $traspasos = BatchTraspaso::getAll("WHERE id_batches='" . addslashes($this->batch->id) . "' ORDER BY seq_index");
    if(empty($traspasos)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Traspasos</h2>
      <table class="traspasos-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Cantidad</th>
          </tr>
        </thead>
        <tbody>';

    foreach($traspasos as $t) {
      $origen = $t->id_fermentadores_inicio ? new Activo($t->id_fermentadores_inicio) : null;
      $destino = $t->id_fermentadores_final ? new Activo($t->id_fermentadores_final) : null;
      $fecha = $t->date && $t->date != '0000-00-00' ? date('d/m/Y', strtotime($t->date)) : '-';

      $html .= '<tr>
          <td>' . ($t->seq_index + 1) . '</td>
          <td>' . $fecha . '</td>
          <td>' . ($t->hora ?: '-') . '</td>
          <td>' . ($origen ? htmlspecialchars($origen->nombre) : '-') . '</td>
          <td>' . ($destino ? htmlspecialchars($destino->nombre) : '-') . '</td>
          <td>' . ($t->cantidad ?: '-') . ' L</td>
        </tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
  }

  private function generarMetricasFinales() {
    // Solo mostrar si hay métricas
    $tieneMetricas = $this->batch->abv_final || $this->batch->ibu_final ||
                     $this->batch->color_ebc || $this->batch->rendimiento_litros_final ||
                     $this->batch->calificacion_sensorial;

    if(!$tieneMetricas) {
      return '';
    }

    $html = '<div class="section">
      <h2>Metricas Finales</h2>
      <table class="params-table">
        <tr>
          <th>Parametro</th>
          <th>Valor</th>
          <th>Parametro</th>
          <th>Valor</th>
        </tr>';

    // Fila 1: ABV e IBU
    $html .= '<tr>
          <td>ABV Final</td>
          <td>' . ($this->batch->abv_final ? $this->batch->abv_final . '%' : '-') . '</td>
          <td>IBU</td>
          <td>' . ($this->batch->ibu_final ?: '-') . '</td>
        </tr>';

    // Fila 2: Color y Densidad
    $html .= '<tr>
          <td>Color EBC</td>
          <td>' . ($this->batch->color_ebc ?: '-') . '</td>
          <td>Densidad Final</td>
          <td>' . ($this->batch->densidad_final_verificada ?: '-') . '</td>
        </tr>';

    // Fila 3: Rendimiento y Merma
    $html .= '<tr>
          <td>Rendimiento</td>
          <td>' . ($this->batch->rendimiento_litros_final ? $this->batch->rendimiento_litros_final . ' L' : '-') . '</td>
          <td>Merma Total</td>
          <td>' . ($this->batch->merma_total_litros ? $this->batch->merma_total_litros . ' L' : '-') . '</td>
        </tr>';

    // Fila 4: Calificación
    if($this->batch->calificacion_sensorial) {
      $html .= '<tr>
          <td>Calificacion Sensorial</td>
          <td colspan="3">' . $this->batch->calificacion_sensorial . '/10</td>
        </tr>';
    }

    $html .= '</table>';

    // Notas de cata
    if(!empty($this->batch->notas_cata)) {
      $html .= '<div class="observaciones-box" style="margin-top: 8px;">
        <strong>Notas de Cata:</strong><br>
        ' . nl2br(htmlspecialchars($this->batch->notas_cata)) . '
      </div>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarObservaciones() {
    if(empty($this->batch->observaciones)) {
      return '';
    }

    return '<div class="section">
      <h2>Observaciones</h2>
      <div class="observaciones-box">
        ' . nl2br(htmlspecialchars($this->batch->observaciones)) . '
      </div>
    </div>';
  }

  private function generarPie() {
    return '<div class="footer">
      <p>Cerveza Cocholgue - Informe de Batch #' . htmlspecialchars($this->batch->batch_nombre) . '</p>
      <p class="small">Documento generado automaticamente. ' . date('d/m/Y H:i:s') . '</p>
    </div>';
  }

  private function getCSS() {
    return '
      body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10px;
        line-height: 1.4;
        color: #333;
        margin: 15px;
      }
      .header {
        overflow: hidden;
        margin-bottom: 10px;
      }
      .header-left {
        float: left;
        width: 55%;
      }
      .header-right {
        float: right;
        width: 45%;
        text-align: right;
      }
      .header h1 {
        font-size: 18px;
        margin: 0 0 5px 0;
        color: #c0392b;
      }
      .header .batch-nombre {
        font-size: 14px;
        font-weight: bold;
        margin: 0;
      }
      .header .batch-fecha {
        font-size: 10px;
        color: #7f8c8d;
        margin: 3px 0 0 0;
      }
      .header .receta {
        font-size: 12px;
        color: #16a085;
        font-weight: bold;
        margin: 0;
      }
      .header .linea {
        font-size: 10px;
        color: #8e44ad;
        margin: 3px 0 0 0;
      }
      .header .fecha {
        font-size: 9px;
        color: #95a5a6;
        margin: 5px 0 0 0;
      }
      .separator {
        border-bottom: 2px solid #c0392b;
        margin: 10px 0;
      }
      .section {
        margin: 12px 0;
        page-break-inside: avoid;
      }
      .section h2 {
        font-size: 12px;
        color: #2c3e50;
        border-bottom: 1px solid #bdc3c7;
        padding-bottom: 3px;
        margin: 0 0 8px 0;
      }
      .info-table, .params-table, .insumos-table, .traspasos-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
      }
      .info-table td {
        padding: 4px 6px;
        border: 1px solid #ecf0f1;
      }
      .params-table td, .params-table th {
        padding: 4px 6px;
        border: 1px solid #ecf0f1;
      }
      .params-table th {
        background: #34495e;
        color: white;
        font-size: 9px;
      }
      .insumos-table th, .traspasos-table th {
        background: #2c3e50;
        color: white;
        padding: 5px;
        text-align: left;
        font-size: 9px;
      }
      .insumos-table td, .traspasos-table td {
        padding: 4px 5px;
        border-bottom: 1px solid #ecf0f1;
      }
      .etapa-box {
        background: #f8f9fa;
        border-left: 3px solid #3498db;
        padding: 8px;
        margin: 6px 0;
      }
      .etapa-box h3 {
        margin: 0 0 5px 0;
        font-size: 11px;
        color: #2980b9;
      }
      .etapa-box p {
        margin: 3px 0;
      }
      .etapa-box .finalizada {
        color: #27ae60;
        font-weight: bold;
      }
      .etapa-finalizada {
        border-left-color: #27ae60;
        background: #e8f8f5;
      }
      .estado {
        background: #3498db;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 9px;
      }
      .observaciones-box {
        background: #fef9e7;
        padding: 8px;
        border: 1px solid #f1c40f;
      }
      .footer {
        margin-top: 20px;
        text-align: center;
        color: #95a5a6;
        font-size: 8px;
        border-top: 1px solid #ecf0f1;
        padding-top: 8px;
      }
      .footer .small {
        font-size: 7px;
      }
    ';
  }
}
