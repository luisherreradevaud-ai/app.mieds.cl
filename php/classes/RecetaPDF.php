<?php
/**
 * RecetaPDF - Generador de PDF de instrucciones de receta
 * Genera un PDF con las instrucciones paso a paso para producción
 */

require_once($GLOBALS['base_dir'] . '/vendor_php/dompdf_autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

class RecetaPDF {

  private $receta;
  private $dompdf;

  // Etiquetas de etapas
  private static $etapasLabels = [
    'preparacion' => 'Preparación',
    'licor' => 'Licor de Maceración',
    'maceracion' => 'Maceración',
    'lavado' => 'Lavado',
    'coccion' => 'Cocción',
    'lupulizacion' => 'Lupulización',
    'enfriado' => 'Enfriado',
    'inoculacion' => 'Inoculación',
    'fermentacion' => 'Fermentación',
    'maduracion' => 'Maduración',
    'traspasos' => 'Traspasos',
    'envasado' => 'Envasado',
    'general' => 'General'
  ];

  public function __construct(Receta $receta) {
    $this->receta = $receta;

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'Helvetica');
    $options->set('isFontSubsettingEnabled', true);

    $this->dompdf = new Dompdf($options);
    $this->dompdf->setPaper('Letter', 'portrait');
  }

  /**
   * Genera el PDF completo
   * @return string PDF content
   */
  public function generar() {
    $html = $this->generarHTML();
    $this->dompdf->loadHtml($html);
    $this->dompdf->render();
    return $this->dompdf->output();
  }

  /**
   * Descarga el PDF directamente
   * @param string $filename
   */
  public function descargar($filename = null) {
    if(!$filename) {
      $filename = 'Receta_' . $this->receta->codigo . '_' . date('Ymd') . '.pdf';
    }
    $this->dompdf->loadHtml($this->generarHTML());
    $this->dompdf->render();
    $this->dompdf->stream($filename, ['Attachment' => true]);
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

    // Parámetros objetivo
    $html .= $this->generarParametrosObjetivo();

    // Lista de insumos
    $html .= $this->generarListaInsumos();

    // Instrucciones por etapa
    $html .= $this->generarInstrucciones();

    // Instrucciones generales
    if(!empty($this->receta->instrucciones_generales)) {
      $html .= $this->generarInstruccionesGenerales();
    }

    // Pie de página
    $html .= $this->generarPie();

    $html .= '</body></html>';
    return $html;
  }

  private function generarEncabezado() {
    return '
    <div class="header">
      <div class="header-left">
        <h1>' . htmlspecialchars($this->receta->nombre) . '</h1>
        <p class="codigo">Código: ' . htmlspecialchars($this->receta->codigo) . '</p>
      </div>
      <div class="header-right">
        <p class="clasificacion">' . htmlspecialchars($this->receta->clasificacion) . '</p>
        <p class="fecha">Generado: ' . date('d/m/Y H:i') . '</p>
      </div>
    </div>
    <div class="separator"></div>';
  }

  private function generarInfoGeneral() {
    $tiempoTotal = $this->receta->getTiempoTotalDias();

    $html = '<div class="section">
      <h2>Información General</h2>
      <table class="info-table">
        <tr>
          <td><strong>Volumen:</strong></td>
          <td>' . number_format($this->receta->litros, 0) . ' litros</td>
          <td><strong>Clasificación:</strong></td>
          <td>' . htmlspecialchars($this->receta->clasificacion) . '</td>
        </tr>';

    if(!empty($this->receta->tiempo_fermentacion_dias) || !empty($this->receta->tiempo_maduracion_dias)) {
      $html .= '<tr>
          <td><strong>Fermentación:</strong></td>
          <td>' . ($this->receta->tiempo_fermentacion_dias ?? '-') . ' días</td>
          <td><strong>Maduración:</strong></td>
          <td>' . ($this->receta->tiempo_maduracion_dias ?? '-') . ' días</td>
        </tr>';
    }

    if($tiempoTotal) {
      $html .= '<tr>
          <td><strong>Tiempo Total:</strong></td>
          <td colspan="3">' . $tiempoTotal . ' días</td>
        </tr>';
    }

    $html .= '</table></div>';
    return $html;
  }

  private function generarParametrosObjetivo() {
    $params = $this->receta->getParametrosObjetivo();
    if(empty($params)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Parámetros Objetivo</h2>
      <table class="params-table">';

    $count = 0;
    $html .= '<tr>';
    foreach($params as $label => $valor) {
      $html .= '<td class="param-cell"><strong>' . $label . ':</strong> ' . $valor . '</td>';
      $count++;
      if($count % 3 == 0) {
        $html .= '</tr><tr>';
      }
    }
    $html .= '</tr></table></div>';

    return $html;
  }

  private function generarListaInsumos() {
    $insumosPorEtapa = $this->receta->getInsumosPorEtapa();
    if(empty($insumosPorEtapa)) {
      return '';
    }

    $html = '<div class="section">
      <h2>Insumos</h2>';

    foreach($insumosPorEtapa as $etapa => $insumos) {
      $etapaLabel = self::$etapasLabels[$etapa] ?? ucfirst($etapa);
      $html .= '<h3 class="etapa-label">' . $etapaLabel . '</h3>
        <table class="insumos-table">
          <thead>
            <tr>
              <th>Insumo</th>
              <th>Cantidad</th>
              <th>Momento</th>
            </tr>
          </thead>
          <tbody>';

      foreach($insumos as $insumo) {
        $html .= '<tr>
            <td>' . htmlspecialchars($insumo['insumo_nombre']) . '</td>
            <td>' . $insumo['cantidad'] . ' ' . htmlspecialchars($insumo['unidad_de_medida']) . '</td>
            <td>' . htmlspecialchars($insumo['momento'] ?? '-') . '</td>
          </tr>';
      }

      $html .= '</tbody></table>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarInstrucciones() {
    $pasosAgrupados = $this->receta->getPasosAgrupados();
    if(empty($pasosAgrupados)) {
      return '<div class="section no-instructions">
        <p><em>Esta receta aún no tiene instrucciones detalladas configuradas.</em></p>
      </div>';
    }

    $html = '<div class="section">
      <h2>Instrucciones de Producción</h2>';

    foreach($pasosAgrupados as $etapa => $pasos) {
      $etapaLabel = self::$etapasLabels[$etapa] ?? ucfirst($etapa);
      $html .= '<div class="etapa-section">
        <h3 class="etapa-header">' . $etapaLabel . '</h3>';

      $pasoNum = 1;
      foreach($pasos as $paso) {
        $html .= '<div class="paso">
          <div class="paso-header">
            <span class="paso-num">' . $pasoNum . '</span>
            <span class="paso-titulo">' . htmlspecialchars($paso->titulo) . '</span>';

        if($paso->duracion_minutos > 0) {
          $html .= '<span class="paso-duracion">' . $paso->getDuracionFormateada() . '</span>';
        }

        $html .= '</div>
          <div class="paso-instruccion">' . nl2br(htmlspecialchars($paso->instruccion)) . '</div>';

        // Parámetros objetivo del paso
        $paramsObj = $paso->getParametrosObjetivo();
        if(!empty($paramsObj)) {
          $html .= '<div class="paso-params">';
          foreach($paramsObj as $label => $valor) {
            $html .= '<span class="paso-param"><strong>' . $label . ':</strong> ' . $valor . '</span>';
          }
          $html .= '</div>';
        }

        // Notas del paso
        if(!empty($paso->notas)) {
          $html .= '<div class="paso-notas"><strong>Nota:</strong> ' . nl2br(htmlspecialchars($paso->notas)) . '</div>';
        }

        $html .= '</div>';
        $pasoNum++;
      }

      $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
  }

  private function generarInstruccionesGenerales() {
    return '<div class="section">
      <h2>Instrucciones Generales</h2>
      <div class="instrucciones-generales">
        ' . nl2br(htmlspecialchars($this->receta->instrucciones_generales)) . '
      </div>
    </div>';
  }

  private function generarPie() {
    return '<div class="footer">
      <p>Cerveza Cocholgue - Documento generado automáticamente</p>
      <p class="small">Este documento es para uso interno. ' . date('d/m/Y H:i:s') . '</p>
    </div>';
  }

  private function getCSS() {
    return '
      body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #333;
        margin: 20px;
      }
      .header {
        overflow: hidden;
        margin-bottom: 10px;
      }
      .header-left {
        float: left;
        width: 70%;
      }
      .header-right {
        float: right;
        width: 30%;
        text-align: right;
      }
      .header h1 {
        font-size: 20px;
        margin: 0 0 5px 0;
        color: #2c3e50;
      }
      .header .codigo {
        font-size: 12px;
        color: #7f8c8d;
        margin: 0;
      }
      .header .clasificacion {
        font-size: 14px;
        font-weight: bold;
        color: #16a085;
        margin: 0;
      }
      .header .fecha {
        font-size: 9px;
        color: #95a5a6;
        margin: 5px 0 0 0;
      }
      .separator {
        border-bottom: 2px solid #2c3e50;
        margin: 10px 0;
      }
      .section {
        margin: 15px 0;
        page-break-inside: avoid;
      }
      .section h2 {
        font-size: 14px;
        color: #2c3e50;
        border-bottom: 1px solid #bdc3c7;
        padding-bottom: 5px;
        margin: 0 0 10px 0;
      }
      .info-table {
        width: 100%;
        border-collapse: collapse;
      }
      .info-table td {
        padding: 4px 8px;
        border: 1px solid #ecf0f1;
      }
      .params-table {
        width: 100%;
      }
      .param-cell {
        padding: 5px 10px;
        background: #ecf0f1;
        border-radius: 3px;
        margin: 2px;
      }
      .etapa-label {
        font-size: 12px;
        color: #16a085;
        margin: 10px 0 5px 0;
      }
      .insumos-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
      }
      .insumos-table th {
        background: #34495e;
        color: white;
        padding: 6px;
        text-align: left;
        font-size: 10px;
      }
      .insumos-table td {
        padding: 5px 6px;
        border-bottom: 1px solid #ecf0f1;
      }
      .etapa-section {
        margin: 15px 0;
        page-break-inside: avoid;
      }
      .etapa-header {
        background: #2c3e50;
        color: white;
        padding: 8px 12px;
        margin: 0 0 10px 0;
        font-size: 13px;
      }
      .paso {
        background: #f8f9fa;
        border-left: 3px solid #3498db;
        padding: 10px;
        margin: 8px 0;
      }
      .paso-header {
        margin-bottom: 8px;
      }
      .paso-num {
        display: inline-block;
        width: 20px;
        height: 20px;
        background: #3498db;
        color: white;
        text-align: center;
        border-radius: 50%;
        font-size: 11px;
        line-height: 20px;
        margin-right: 8px;
      }
      .paso-titulo {
        font-weight: bold;
        font-size: 12px;
      }
      .paso-duracion {
        float: right;
        background: #95a5a6;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 10px;
      }
      .paso-instruccion {
        margin: 8px 0;
        padding-left: 28px;
      }
      .paso-params {
        padding-left: 28px;
        margin-top: 8px;
      }
      .paso-param {
        display: inline-block;
        background: #d5f5e3;
        padding: 2px 6px;
        margin-right: 10px;
        border-radius: 3px;
        font-size: 10px;
      }
      .paso-notas {
        padding-left: 28px;
        margin-top: 8px;
        font-style: italic;
        color: #7f8c8d;
        font-size: 10px;
      }
      .instrucciones-generales {
        background: #fef9e7;
        padding: 10px;
        border: 1px solid #f1c40f;
      }
      .no-instructions {
        text-align: center;
        padding: 20px;
        color: #7f8c8d;
      }
      .footer {
        margin-top: 30px;
        text-align: center;
        color: #95a5a6;
        font-size: 9px;
        border-top: 1px solid #ecf0f1;
        padding-top: 10px;
      }
      .footer .small {
        font-size: 8px;
      }
    ';
  }
}

?>
