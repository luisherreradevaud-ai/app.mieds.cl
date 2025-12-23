<?php

/**
 * Clase para generar PDF de Trazabilidad por Entrega
 * Genera un certificado de trazabilidad del producto entregado
 */
class TrazabilidadPDF {

  private $entrega;
  private $entrega_producto;
  private $cliente;
  private $datos = array();
  private $es_halal = false;
  private $normas = array();

  /**
   * Constructor
   * @param int $id_entregas_productos ID del producto entregado
   */
  public function __construct($id_entregas_productos) {
    $this->entrega_producto = new EntregaProducto($id_entregas_productos);
    $this->entrega = new Entrega($this->entrega_producto->id_entregas);
    $this->cliente = new Cliente($this->entrega->id_clientes);
    $this->recopilarDatos();
  }

  /**
   * Recopila todos los datos necesarios para el PDF
   */
  private function recopilarDatos() {
    // Datos de la entrega
    $this->datos['entrega'] = array(
      'fecha' => $this->entrega->creada,
      'cliente_nombre' => $this->cliente->nombre,
      'cliente_razon_social' => $this->cliente->RznSoc ?: $this->cliente->nombre,
      'factura' => $this->entrega->factura,
      'receptor' => $this->entrega->receptor_nombre,
      'observaciones' => $this->entrega->observaciones
    );

    // Determinar tipo de producto y recopilar trazabilidad
    if($this->entrega_producto->id_barriles > 0) {
      $this->recopilarDatosBarril();
    } elseif($this->entrega_producto->id_cajas_de_envases > 0) {
      $this->recopilarDatosCajaEnvases();
    }
  }

  /**
   * Recopila datos de trazabilidad para un barril
   */
  private function recopilarDatosBarril() {
    $barril = new Barril($this->entrega_producto->id_barriles);
    $batch = new Batch($barril->id_batches);
    $receta = new Receta($batch->id_recetas);

    // Determinar si es producto Halal
    $this->determinarLineaHalal($batch);

    $this->datos['producto'] = array(
      'tipo' => 'Barril',
      'codigo' => $barril->codigo,
      'capacidad' => $barril->litraje . 'L',
      'litros_cargados' => $barril->litros_cargados . 'L',
      'receta_nombre' => $receta->nombre,
      'receta_codigo' => $receta->codigo,
      'linea_productiva' => $batch->getLineaProductivaLabel()
    );

    // Datos de cocción
    $this->datos['coccion'] = array(
      'fecha' => $batch->batch_date,
      'batch_id' => $batch->id,
      'batch_nombre' => $batch->batch_nombre,
      'batch_fecha' => $batch->batch_date,
      'granos' => $this->obtenerGranos($batch->id)
    );

    // Obtener todos los insumos con certificación
    $this->datos['insumos'] = $this->obtenerInsumos($batch->id);

    // Datos de fermentación - usar solo el activo relacionado con este barril
    $batch_activo = null;
    $activo_id_barril = null; // ID del activo específico de este barril

    if($barril->id_batches_activos > 0) {
      $batch_activo = new BatchActivo($barril->id_batches_activos);
      $activo_id_barril = $batch_activo->id_activos;
    } elseif($barril->id_activos > 0) {
      // Si tiene id_activos directo, buscar el BatchActivo correspondiente
      $activo_id_barril = $barril->id_activos;
      $batches_activos = BatchActivo::getAll("WHERE id_batches='" . $batch->id . "' AND id_activos='" . $barril->id_activos . "' LIMIT 1");
      if(count($batches_activos) > 0) {
        $batch_activo = $batches_activos[0];
      }
    }

    // Si aún no tenemos batch_activo, buscar el primero del batch (fallback)
    if(!$batch_activo) {
      $batches_activos = BatchActivo::getAll("WHERE id_batches='" . $batch->id . "' ORDER BY creada ASC LIMIT 1");
      if(count($batches_activos) > 0) {
        $batch_activo = $batches_activos[0];
        $activo_id_barril = $batch_activo->id_activos;
      }
    }

    if($batch_activo) {
      $activo_fermentador = new Activo($batch_activo->id_activos);
      $this->datos['fermentacion'] = array(
        'fecha' => $batch->fermentacion_date ?: $batch_activo->creada,
        'hora' => $batch->fermentacion_hora_inicio,
        'activo_nombre' => $activo_fermentador->nombre,
        'activo_codigo' => $activo_fermentador->codigo,
        'activo_capacidad' => $activo_fermentador->litraje . 'L',
        'linea_productiva' => $activo_fermentador->getLineaProductivaLabel()
      );
    }

    // Si es Halal, obtener limpiezas certificadas solo del activo relacionado con este barril
    if($this->es_halal) {
      $this->datos['limpiezas_halal'] = $this->obtenerLimpiezasHalalBarril($activo_id_barril);
    }

    // Datos de traspasos/maduración - solo los relacionados con el activo del barril
    $this->datos['traspasos'] = $this->obtenerTraspasosBarril($batch->id, $activo_id_barril);

    // Datos de empaque (embarrilado)
    // Priorizar fecha_llenado si es válida y posterior a la fecha de cocción
    // Si no, buscar en traspasos o usar fecha de fermentación
    $fecha_llenado = null;
    $fecha_coccion = $batch->batch_date;

    // Verificar si fecha_llenado del barril es válida y posterior a cocción
    if($barril->fecha_llenado && $barril->fecha_llenado != '0000-00-00' && $barril->fecha_llenado != '0000-00-00 00:00:00') {
      $year_llenado = (int)substr($barril->fecha_llenado, 0, 4);
      $year_coccion = $fecha_coccion ? (int)substr($fecha_coccion, 0, 4) : 0;
      // Solo usar si el año es razonable y no es muy anterior a la cocción
      if($year_llenado >= 2020 && ($year_coccion == 0 || $year_llenado >= $year_coccion)) {
        $fecha_llenado = $barril->fecha_llenado;
      }
    }

    // Si no hay fecha válida, buscar el último traspaso del batch
    if(!$fecha_llenado && !empty($this->datos['traspasos'])) {
      $ultimo_traspaso = end($this->datos['traspasos']);
      if($ultimo_traspaso && $ultimo_traspaso['fecha']) {
        $fecha_llenado = $ultimo_traspaso['fecha'];
      }
    }

    // Si aún no hay fecha, usar la fecha de maduración o fermentación del batch
    if(!$fecha_llenado) {
      $fecha_llenado = $batch->maduracion_date ?: $batch->fermentacion_date ?: $batch->batch_date;
    }

    $activo_barril = null;
    if($barril->id_activos > 0) {
      $activo_barril = new Activo($barril->id_activos);
    }

    $this->datos['empaque'] = array(
      'tipo' => 'Embarrilado',
      'fecha' => $fecha_llenado,
      'codigo' => $barril->codigo,
      'capacidad' => $barril->litraje . 'L',
      'litros' => $barril->litros_cargados . 'L',
      'linea_productiva' => $activo_barril ? $activo_barril->getLineaProductivaLabel() : 'N/A'
    );

    // Calcular tiempos
    $this->calcularTiempos($batch->batch_date, $fecha_llenado, $this->entrega->creada);
  }

  /**
   * Recopila datos de trazabilidad para una caja de envases
   */
  private function recopilarDatosCajaEnvases() {
    $caja = new CajaDeEnvases($this->entrega_producto->id_cajas_de_envases);
    $envases = $caja->getEnvases();

    // Obtener datos del primer envase para trazabilidad
    $batch = null;
    $batch_de_envases = null;
    $receta = null;
    $formato = null;

    if(count($envases) > 0) {
      $primer_envase = $envases[0];
      $batch_de_envases = $primer_envase->getBatchDeEnvases();
      if($batch_de_envases) {
        $batch = new Batch($batch_de_envases->id_batches);
        $receta = new Receta($batch_de_envases->id_recetas ?: $batch->id_recetas);
        $formato = $batch_de_envases->getFormatoDeEnvases();
      }
    }

    // Si es caja mixta, obtener resumen de recetas
    $resumen_recetas = $caja->getResumenRecetas();

    $this->datos['producto'] = array(
      'tipo' => 'Caja de Envases',
      'codigo' => $caja->codigo,
      'formato' => $formato ? $formato->nombre . ' (' . $formato->volumen_ml . 'ml)' : 'N/A',
      'tipo_envase' => $formato ? $formato->tipo : 'N/A',
      'cantidad' => $caja->cantidad_envases . ' unidades',
      'receta_nombre' => $receta ? $receta->nombre : 'Mixta',
      'receta_codigo' => $receta ? $receta->codigo : '-',
      'es_mixta' => count($resumen_recetas) > 1,
      'resumen_recetas' => $resumen_recetas
    );

    if($batch) {
      // Datos de cocción
      $this->datos['coccion'] = array(
        'fecha' => $batch->batch_date,
        'batch_nombre' => $batch->batch_nombre,
        'granos' => $this->obtenerGranos($batch->id)
      );

      // Datos de fermentación
      $batches_activos = BatchActivo::getAll("WHERE id_batches='" . $batch->id . "' ORDER BY creada ASC LIMIT 1");
      if(count($batches_activos) > 0) {
        $batch_activo = $batches_activos[0];
        $activo_fermentador = new Activo($batch_activo->id_activos);
        $this->datos['fermentacion'] = array(
          'fecha' => $batch->fermentacion_date ?: $batch_activo->creada,
          'hora' => $batch->fermentacion_hora_inicio,
          'activo_nombre' => $activo_fermentador->nombre,
          'activo_codigo' => $activo_fermentador->codigo,
          'activo_capacidad' => $activo_fermentador->litraje . 'L',
          'linea_productiva' => $activo_fermentador->getLineaProductivaLabel()
        );
      }

      // Datos de traspasos/maduración
      $this->datos['traspasos'] = $this->obtenerTraspasos($batch->id);
    }

    // Datos de empaque (envasado)
    $fecha_envasado = $batch_de_envases ? $batch_de_envases->creada : $caja->creada;
    $activo_origen = null;
    if($batch_de_envases && $batch_de_envases->id_activos > 0) {
      $activo_origen = new Activo($batch_de_envases->id_activos);
    }

    $this->datos['empaque'] = array(
      'tipo' => $formato ? $formato->tipo : 'Envasado',
      'fecha' => $fecha_envasado,
      'codigo' => $caja->codigo,
      'formato' => $formato ? $formato->nombre : 'N/A',
      'cantidad' => $caja->cantidad_envases,
      'linea_productiva' => $activo_origen ? $activo_origen->getLineaProductivaLabel() : 'N/A'
    );

    // Calcular tiempos
    $fecha_coccion = $batch ? $batch->batch_date : null;
    $this->calcularTiempos($fecha_coccion, $fecha_envasado, $this->entrega->creada);
  }

  /**
   * Obtiene los granos/maltas utilizados en un batch
   * @param int $batch_id
   * @return array
   */
  private function obtenerGranos($batch_id) {
    $granos = array();
    $batch_insumos = BatchInsumo::getAll("WHERE id_batches='" . $batch_id . "'");

    foreach($batch_insumos as $bi) {
      $insumo = new Insumo($bi->id_insumos);
      $tipo_insumo = new TipoDeInsumo($insumo->id_tipos_de_insumos);

      // Filtrar solo granos/maltas (ajustar según nomenclatura del sistema)
      $tipos_grano = array('Malta', 'Grano', 'Cereal', 'malta', 'grano');
      $es_grano = false;
      foreach($tipos_grano as $tipo) {
        if(stripos($tipo_insumo->nombre, $tipo) !== false) {
          $es_grano = true;
          break;
        }
      }

      if($es_grano) {
        $granos[] = $insumo->nombre;
      }
    }

    return array_unique($granos);
  }

  /**
   * Obtiene todos los insumos con información de certificación
   * @param int $batch_id
   * @return array
   */
  private function obtenerInsumos($batch_id) {
    $insumos = array();
    $todos_halal = true;
    $batch_insumos = BatchInsumo::getAll("WHERE id_batches='" . $batch_id . "'");

    foreach($batch_insumos as $bi) {
      $insumo = new Insumo($bi->id_insumos);
      $tipo_insumo = new TipoDeInsumo($insumo->id_tipos_de_insumos);

      $es_halal_vigente = $insumo->tieneCertificadoHalalVigente();
      if(!$es_halal_vigente) {
        $todos_halal = false;
      }

      $insumos[] = array(
        'nombre' => $insumo->nombre,
        'tipo' => $tipo_insumo->nombre,
        'cantidad' => $bi->cantidad,
        'unidad' => $insumo->unidad_de_medida,
        'etapa' => $bi->etapa,
        'url_ficha' => $insumo->url_ficha_tecnica,
        'es_halal' => $es_halal_vigente,
        'certificado_halal' => $insumo->certificado_halal_numero,
        'certificado_emisor' => $insumo->certificado_halal_emisor
      );
    }

    $this->datos['insumos_todos_halal'] = $todos_halal;
    return $insumos;
  }

  /**
   * Obtiene información de limpiezas Halal de los activos usados
   * @param int $batch_id
   * @return array
   */
  private function obtenerLimpiezasHalal($batch_id) {
    $limpiezas = array();
    $batches_activos = BatchActivo::getAll("WHERE id_batches='" . $batch_id . "'");

    foreach($batches_activos as $ba) {
      $activo = new Activo($ba->id_activos);
      $ultima_limpieza_halal = $activo->getUltimaLimpiezaHalal();

      if($ultima_limpieza_halal) {
        $limpiezas[] = array(
          'activo_nombre' => $activo->nombre,
          'activo_codigo' => $activo->codigo,
          'fecha' => $ultima_limpieza_halal->fecha,
          'certificado' => $ultima_limpieza_halal->certificado_numero,
          'emisor' => $ultima_limpieza_halal->certificado_emisor,
          'procedimiento' => $ultima_limpieza_halal->procedimiento_utilizado
        );
      }
    }

    return $limpiezas;
  }

  /**
   * Obtiene información de limpiezas Halal solo del activo específico del barril
   * @param int $activo_id ID del activo relacionado con el barril
   * @return array
   */
  private function obtenerLimpiezasHalalBarril($activo_id) {
    $limpiezas = array();

    if(!$activo_id) {
      return $limpiezas;
    }

    $activo = new Activo($activo_id);
    if(empty($activo->id)) {
      return $limpiezas;
    }

    $ultima_limpieza_halal = $activo->getUltimaLimpiezaHalal();

    if($ultima_limpieza_halal) {
      $limpiezas[] = array(
        'activo_nombre' => $activo->nombre,
        'activo_codigo' => $activo->codigo,
        'fecha' => $ultima_limpieza_halal->fecha,
        'certificado' => $ultima_limpieza_halal->certificado_numero,
        'emisor' => $ultima_limpieza_halal->certificado_emisor,
        'procedimiento' => $ultima_limpieza_halal->procedimiento_utilizado
      );
    }

    return $limpiezas;
  }

  /**
   * Determina si el producto es de línea Halal/Sin Alcohol
   * y establece las normas correspondientes
   * @param Batch $batch
   * @return bool
   */
  private function determinarLineaHalal($batch) {
    if($batch->esLineaSinAlcohol()) {
      // Línea SIN alcohol: Normas Halal + estándares de calidad
      $this->es_halal = true;
      $this->normas = array(
        'OIC/SMIIC 1',
        'GSO 2055-1',
        'ISO 22005',
        'ISO 22000',
        'FSSC 22000',
        'BRCGS',
        'IFS'
      );
      return true;
    } else {
      // Línea CON alcohol: Solo estándares de calidad alimentaria
      $this->normas = array(
        'ISO 22005',
        'ISO 22000',
        'FSSC 22000',
        'BRCGS',
        'IFS'
      );
    }
    return false;
  }

  /**
   * Obtiene los traspasos de un batch
   * @param int $batch_id
   * @return array
   */
  private function obtenerTraspasos($batch_id) {
    $traspasos = array();
    $batch_traspasos = BatchTraspaso::getAll("WHERE id_batches='" . $batch_id . "' ORDER BY date ASC, hora ASC");

    foreach($batch_traspasos as $bt) {
      $activo_origen = new Activo($bt->id_fermentadores_inicio);
      $activo_destino = new Activo($bt->id_fermentadores_final);

      $traspasos[] = array(
        'fecha' => $bt->date,
        'hora' => $bt->hora,
        'origen' => $activo_origen->codigo ?: $activo_origen->nombre,
        'destino' => $activo_destino->codigo ?: $activo_destino->nombre,
        'cantidad' => $bt->cantidad . 'L',
        'linea_origen' => $activo_origen->getLineaProductivaLabel(),
        'linea_destino' => $activo_destino->getLineaProductivaLabel()
      );
    }

    return $traspasos;
  }

  /**
   * Obtiene los traspasos relacionados con el activo específico del barril
   * Solo incluye traspasos donde el activo del barril sea origen o destino
   * @param int $batch_id
   * @param int $activo_id ID del activo relacionado con el barril
   * @return array
   */
  private function obtenerTraspasosBarril($batch_id, $activo_id) {
    $traspasos = array();

    if(!$activo_id) {
      return $traspasos;
    }

    // Obtener traspasos donde el activo del barril sea origen o destino
    $batch_traspasos = BatchTraspaso::getAll(
      "WHERE id_batches='" . $batch_id . "'
       AND (id_fermentadores_inicio='" . $activo_id . "' OR id_fermentadores_final='" . $activo_id . "')
       ORDER BY date ASC, hora ASC"
    );

    foreach($batch_traspasos as $bt) {
      $activo_origen = new Activo($bt->id_fermentadores_inicio);
      $activo_destino = new Activo($bt->id_fermentadores_final);

      $traspasos[] = array(
        'fecha' => $bt->date,
        'hora' => $bt->hora,
        'origen' => $activo_origen->codigo ?: $activo_origen->nombre,
        'destino' => $activo_destino->codigo ?: $activo_destino->nombre,
        'cantidad' => $bt->cantidad . 'L',
        'linea_origen' => $activo_origen->getLineaProductivaLabel(),
        'linea_destino' => $activo_destino->getLineaProductivaLabel()
      );
    }

    return $traspasos;
  }

  /**
   * Calcula los tiempos del proceso
   */
  private function calcularTiempos($fecha_coccion, $fecha_empaque, $fecha_entrega) {
    $this->datos['tiempos'] = array(
      'coccion_empaque' => $this->calcularDiferenciaTiempo($fecha_coccion, $fecha_empaque),
      'empaque_entrega' => $this->calcularDiferenciaTiempo($fecha_empaque, $fecha_entrega),
      'total' => $this->calcularDiferenciaTiempo($fecha_coccion, $fecha_entrega)
    );
  }

  /**
   * Calcula la diferencia entre dos fechas
   * @return string
   */
  private function calcularDiferenciaTiempo($fecha_inicio, $fecha_fin) {
    // Validar fechas vacías o inválidas
    if(!$fecha_inicio || !$fecha_fin) {
      return 'N/A';
    }

    // Validar fechas nulas de MySQL
    $fechas_invalidas = array('0000-00-00', '0000-00-00 00:00:00', '', null);
    if(in_array($fecha_inicio, $fechas_invalidas) || in_array($fecha_fin, $fechas_invalidas)) {
      return 'N/A';
    }

    // Validar que las fechas tengan un año razonable (después de 2020)
    $year_inicio = (int)substr($fecha_inicio, 0, 4);
    $year_fin = (int)substr($fecha_fin, 0, 4);
    if($year_inicio < 2020 || $year_fin < 2020) {
      return 'N/A';
    }

    try {
      $inicio = new DateTime($fecha_inicio);
      $fin = new DateTime($fecha_fin);

      // Verificar que la fecha fin sea posterior a inicio
      if($fin < $inicio) {
        return 'N/A';
      }

      $diff = $inicio->diff($fin);

      $partes = array();
      if($diff->days > 0) {
        $partes[] = $diff->days . ' día' . ($diff->days > 1 ? 's' : '');
      }
      if($diff->h > 0) {
        $partes[] = $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
      }

      return count($partes) > 0 ? implode(', ', $partes) : 'Menos de 1 hora';
    } catch(Exception $e) {
      return 'N/A';
    }
  }

  /**
   * Formatea una fecha al formato chileno
   * @param string $fecha
   * @return string
   */
  private function formatearFecha($fecha) {
    if(!$fecha || $fecha == '0000-00-00' || $fecha == '0000-00-00 00:00:00') {
      return 'N/A';
    }
    try {
      $dt = new DateTime($fecha);
      return $dt->format('d/m/Y H:i');
    } catch(Exception $e) {
      return $fecha;
    }
  }

  /**
   * Formatea solo la fecha (sin hora)
   */
  private function formatearSoloFecha($fecha) {
    if(!$fecha || $fecha == '0000-00-00') {
      return 'N/A';
    }
    try {
      $dt = new DateTime($fecha);
      return $dt->format('d/m/Y');
    } catch(Exception $e) {
      return $fecha;
    }
  }

  /**
   * Genera el HTML del PDF
   * @return string
   */
  public function generarHTML() {
    $d = $this->datos;

    // Determinar si hay logo
    $logo_path = $GLOBALS['base_dir'] . '/media/images/logo.png';
    $logo_html = '';
    if(file_exists($logo_path)) {
      $logo_html = '<img src="' . $logo_path . '" style="max-height: 60px;">';
    }

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <style>
        body {
          font-family: DejaVu Sans, sans-serif;
          font-size: 11px;
          color: #333;
          line-height: 1.4;
          margin: 0;
          padding: 20px;
        }
        .header {
          text-align: center;
          border-bottom: 2px solid #c9a227;
          padding-bottom: 15px;
          margin-bottom: 20px;
        }
        .header h1 {
          color: #c9a227;
          font-size: 18px;
          margin: 10px 0 5px 0;
        }
        .header .subtitulo {
          color: #666;
          font-size: 12px;
        }
        .seccion {
          margin-bottom: 15px;
          page-break-inside: avoid;
        }
        .seccion-titulo {
          background: #c9a227;
          color: #fff;
          padding: 6px 10px;
          font-weight: bold;
          font-size: 12px;
          margin-bottom: 10px;
        }
        .seccion-contenido {
          padding: 0 10px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 10px;
        }
        table td {
          padding: 4px 8px;
          border-bottom: 1px solid #eee;
        }
        table td:first-child {
          width: 40%;
          color: #666;
        }
        table td:last-child {
          font-weight: 500;
        }
        .timeline {
          position: relative;
          padding-left: 25px;
        }
        .timeline-item {
          position: relative;
          padding-bottom: 15px;
          border-left: 2px solid #c9a227;
          padding-left: 15px;
          margin-left: 5px;
        }
        .timeline-item:last-child {
          border-left: 2px solid transparent;
        }
        .timeline-item::before {
          content: "";
          position: absolute;
          left: -8px;
          top: 0;
          width: 12px;
          height: 12px;
          background: #c9a227;
          border-radius: 50%;
        }
        .timeline-titulo {
          font-weight: bold;
          color: #c9a227;
          margin-bottom: 5px;
        }
        .timeline-detalle {
          font-size: 10px;
          color: #666;
        }
        .resumen-tiempos {
          background: #f9f9f9;
          padding: 10px;
          border-radius: 5px;
        }
        .resumen-tiempos table td {
          border-bottom: none;
        }
        .resumen-tiempos .total {
          font-weight: bold;
          border-top: 1px solid #c9a227;
          padding-top: 5px;
        }
        .footer {
          margin-top: 30px;
          padding-top: 10px;
          border-top: 1px solid #ddd;
          text-align: center;
          font-size: 9px;
          color: #999;
        }
        .badge {
          display: inline-block;
          padding: 2px 6px;
          border-radius: 3px;
          font-size: 9px;
          font-weight: bold;
          margin-top: 5px;
        }
        .badge-alcoholica { background: #e74c3c; color: #fff; }
        .badge-analcoholica { background: #27ae60; color: #fff; }
        .badge-general { background: #3498db; color: #fff; }
      </style>
    </head>
    <body>

    <div class="header">
      ' . $logo_html . '
      <h1>CERTIFICADO DE TRAZABILIDAD DE PRODUCTO</h1>
      <div class="subtitulo">Cerveza Cocholgue' . (!empty($this->normas) ? ' - ' . implode(' / ', $this->normas) : '') . '</div>
      ' . ($this->es_halal ? '<div style="color: #27ae60; font-weight: bold; margin-top: 5px;">Producto de Línea Sin Alcohol - Apto Halal</div>' : '') . '
    </div>

    <!-- DATOS DE LA ENTREGA -->
    <div class="seccion">
      <div class="seccion-titulo">DATOS DE LA ENTREGA</div>
      <div class="seccion-contenido">
        <table>
          <tr>
            <td>Cliente:</td>
            <td>' . htmlspecialchars($d['entrega']['cliente_razon_social']) . '</td>
          </tr>
          <tr>
            <td>Fecha de Entrega:</td>
            <td>' . $this->formatearFecha($d['entrega']['fecha']) . '</td>
          </tr>
          <tr>
            <td>Documento:</td>
            <td>' . (is_numeric($d['entrega']['factura']) ? 'Factura #' . $d['entrega']['factura'] : ($d['entrega']['factura'] ?: 'Sin documento')) . '</td>
          </tr>
          <tr>
            <td>Receptor:</td>
            <td>' . htmlspecialchars($d['entrega']['receptor'] ?: 'N/A') . '</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- DATOS DEL PRODUCTO -->
    <div class="seccion">
      <div class="seccion-titulo">DATOS DEL PRODUCTO</div>
      <div class="seccion-contenido">
        <table>
          <tr>
            <td>Tipo:</td>
            <td>' . htmlspecialchars($d['producto']['tipo']) . (isset($d['producto']['linea_productiva']) ? ' <span class="badge badge-' . str_replace('í', 'i', strtolower(explode(' ', $d['producto']['linea_productiva'])[0])) . '">' . $d['producto']['linea_productiva'] . '</span>' : '') . '</td>
          </tr>
          <tr>
            <td>Código de ' . strtolower($d['producto']['tipo']) . ':</td>
            <td><strong>' . htmlspecialchars($d['producto']['codigo']) . '</strong></td>
          </tr>';

    if($d['producto']['tipo'] == 'Barril') {
      $html .= '
          <tr>
            <td>Capacidad:</td>
            <td>' . $d['producto']['capacidad'] . ' (Cargado: ' . $d['producto']['litros_cargados'] . ')</td>
          </tr>';
    } else {
      $html .= '
          <tr>
            <td>Formato:</td>
            <td>' . htmlspecialchars($d['producto']['formato']) . '</td>
          </tr>
          <tr>
            <td>Cantidad:</td>
            <td>' . htmlspecialchars($d['producto']['cantidad']) . '</td>
          </tr>';
    }

    $html .= '
          <tr>
            <td>Receta:</td>
            <td>' . htmlspecialchars($d['producto']['receta_nombre']) . ' (' . htmlspecialchars($d['producto']['receta_codigo']) . ')</td>
          </tr>
        </table>';

    // Si es caja mixta, mostrar resumen de recetas
    if(isset($d['producto']['es_mixta']) && $d['producto']['es_mixta'] && !empty($d['producto']['resumen_recetas'])) {
      $html .= '<div style="margin-top:10px;font-size:10px;"><strong>Contenido mixto:</strong> ';
      $partes = array();
      foreach($d['producto']['resumen_recetas'] as $receta => $cantidad) {
        $partes[] = htmlspecialchars($receta) . ' x' . $cantidad;
      }
      $html .= implode(', ', $partes) . '</div>';
    }

    $html .= '
      </div>
    </div>

    <!-- LÍNEA DE TIEMPO DEL PROCESO -->
    <div class="seccion">
      <div class="seccion-titulo">LÍNEA DE TIEMPO DEL PROCESO</div>
      <div class="seccion-contenido">
        <div class="timeline">';

    // Cocción
    if(isset($d['coccion'])) {
      $batch_id_display = isset($d['coccion']['batch_id']) ? $d['coccion']['batch_id'] : '';
      $batch_fecha_display = isset($d['coccion']['batch_fecha']) ? $this->formatearSoloFecha($d['coccion']['batch_fecha']) : '';

      // Generar lista de insumos con links a fichas técnicas
      $insumos_html = '';
      if(!empty($d['insumos'])) {
        $insumos_list = array();
        foreach($d['insumos'] as $insumo) {
          $nombre = htmlspecialchars($insumo['nombre']);
          if(!empty($insumo['url_ficha'])) {
            $insumos_list[] = '<a href="' . htmlspecialchars($insumo['url_ficha']) . '" style="color: #c9a227;">' . $nombre . '</a>';
          } else {
            $insumos_list[] = $nombre;
          }
        }
        $insumos_html = implode(', ', $insumos_list);
      } else {
        $insumos_html = 'Sin información';
      }

      $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">COCCIÓN</div>
            <div class="timeline-detalle">
              <strong>Fecha:</strong> ' . $this->formatearSoloFecha($d['coccion']['fecha']) . '<br>
              <strong>Batch:</strong> ' . htmlspecialchars($d['coccion']['batch_nombre']) . ' <small style="color:#888;">(ID: ' . htmlspecialchars($batch_id_display) . ' - ' . $batch_fecha_display . ')</small><br>
              <strong>Insumos:</strong> ' . $insumos_html . '
            </div>
          </div>';
    }

    // Fermentación
    if(isset($d['fermentacion'])) {
      $linea_class = 'badge-' . str_replace('í', 'i', strtolower(explode(' ', $d['fermentacion']['linea_productiva'])[0]));
      $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">FERMENTACIÓN</div>
            <div class="timeline-detalle">
              <strong>Inicio:</strong> ' . $this->formatearFecha($d['fermentacion']['fecha']) . '<br>
              <strong>Fermentador:</strong> ' . htmlspecialchars($d['fermentacion']['activo_codigo'] ?: $d['fermentacion']['activo_nombre']) . ' (' . $d['fermentacion']['activo_capacidad'] . ')<br>
              <strong>Línea:</strong> <span class="badge ' . $linea_class . '">' . htmlspecialchars($d['fermentacion']['linea_productiva']) . '</span>
            </div>
          </div>';
    }

    // Traspasos/Maduración
    if(!empty($d['traspasos'])) {
      $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">MADURACIÓN / TRASPASOS</div>
            <div class="timeline-detalle">';
      foreach($d['traspasos'] as $t) {
        $html .= '<strong>' . $this->formatearSoloFecha($t['fecha']) . '</strong> ' .
                 htmlspecialchars($t['origen']) . ' <span style="font-size:9px;color:#666;">(' . htmlspecialchars($t['linea_origen']) . ')</span>' .
                 ' → ' . htmlspecialchars($t['destino']) . ' <span style="font-size:9px;color:#666;">(' . htmlspecialchars($t['linea_destino']) . ')</span>' .
                 ' (' . $t['cantidad'] . ')<br>';
      }
      $html .= '
            </div>
          </div>';
    }

    // Empaque
    if(isset($d['empaque'])) {
      $linea_class = 'badge-' . str_replace('í', 'i', strtolower(explode(' ', $d['empaque']['linea_productiva'])[0]));
      $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">EMPAQUE (' . strtoupper($d['empaque']['tipo']) . ')</div>
            <div class="timeline-detalle">
              <strong>Fecha:</strong> ' . $this->formatearFecha($d['empaque']['fecha']) . '<br>
              <strong>Código:</strong> ' . htmlspecialchars($d['empaque']['codigo']) . '<br>
              <strong>Línea:</strong> <span class="badge ' . $linea_class . '">' . htmlspecialchars($d['empaque']['linea_productiva']) . '</span>
            </div>
          </div>';
    }

    // Entrega
    $html .= '
          <div class="timeline-item">
            <div class="timeline-titulo">ENTREGA</div>
            <div class="timeline-detalle">
              <strong>Fecha:</strong> ' . $this->formatearFecha($d['entrega']['fecha']) . '<br>
              <strong>Cliente:</strong> ' . htmlspecialchars($d['entrega']['cliente_nombre']) . '
            </div>
          </div>
        </div>
      </div>
    </div>';

    // Resumen de tiempos
    if(isset($d['tiempos'])) {
      $html .= '
    <div class="seccion">
      <div class="seccion-titulo">RESUMEN DE TIEMPOS</div>
      <div class="seccion-contenido">
        <div class="resumen-tiempos">
          <table>
            <tr>
              <td>Cocción → Empaque:</td>
              <td>' . $d['tiempos']['coccion_empaque'] . '</td>
            </tr>
            <tr>
              <td>Empaque → Entrega:</td>
              <td>' . $d['tiempos']['empaque_entrega'] . '</td>
            </tr>
            <tr class="total">
              <td><strong>TOTAL:</strong></td>
              <td><strong>' . $d['tiempos']['total'] . '</strong></td>
            </tr>
          </table>
        </div>
      </div>
    </div>';
    }

    // Sección de Insumos (si existen)
    if(!empty($d['insumos'])) {
      $html .= '
    <div class="seccion">
      <div class="seccion-titulo">INSUMOS UTILIZADOS</div>
      <div class="seccion-contenido">
        <table style="font-size: 10px;">
          <tr style="background: #f0f0f0;">
            <td><strong>Insumo</strong></td>
            <td><strong>Tipo</strong></td>
            <td><strong>Cantidad</strong></td>
            <td><strong>Etapa</strong></td>';
      if($this->es_halal) {
        $html .= '<td><strong>Halal</strong></td>';
      }
      $html .= '
          </tr>';

      foreach($d['insumos'] as $insumo) {
        $nombre_display = htmlspecialchars($insumo['nombre']);
        if(!empty($insumo['url_ficha'])) {
          $nombre_display = '<a href="' . htmlspecialchars($insumo['url_ficha']) . '" style="color: #c9a227;">' . $nombre_display . '</a>';
        }

        $html .= '
          <tr>
            <td>' . $nombre_display . '</td>
            <td>' . htmlspecialchars($insumo['tipo']) . '</td>
            <td>' . $insumo['cantidad'] . ' ' . htmlspecialchars($insumo['unidad']) . '</td>
            <td>' . htmlspecialchars($insumo['etapa']) . '</td>';
        if($this->es_halal) {
          $html .= '<td>' . ($insumo['es_halal'] ? '<span style="color: green;">&#10003;</span>' : '<span style="color: red;">&#10007;</span>') . '</td>';
        }
        $html .= '</tr>';
      }

      $html .= '
        </table>';

      // Indicador de certificación completa
      if($this->es_halal && isset($d['insumos_todos_halal'])) {
        if($d['insumos_todos_halal']) {
          $html .= '<div style="color: green; font-weight: bold; margin-top: 10px;">&#10003; Todos los insumos tienen certificación Halal vigente</div>';
        } else {
          $html .= '<div style="color: orange; margin-top: 10px;">&#9888; Algunos insumos no tienen certificación Halal</div>';
        }
      }

      $html .= '
      </div>
    </div>';
    }

    // Sección de Limpiezas Halal (si aplica)
    if($this->es_halal && !empty($d['limpiezas_halal'])) {
      $html .= '
    <div class="seccion">
      <div class="seccion-titulo" style="background: #27ae60;">REGISTRO DE LIMPIEZAS HALAL</div>
      <div class="seccion-contenido">
        <table style="font-size: 10px;">
          <tr style="background: #f0f0f0;">
            <td><strong>Activo</strong></td>
            <td><strong>Fecha</strong></td>
            <td><strong>Certificado</strong></td>
            <td><strong>Procedimiento</strong></td>
          </tr>';

      foreach($d['limpiezas_halal'] as $limpieza) {
        $html .= '
          <tr>
            <td>' . htmlspecialchars($limpieza['activo_codigo'] ?: $limpieza['activo_nombre']) . '</td>
            <td>' . $this->formatearFecha($limpieza['fecha']) . '</td>
            <td>' . htmlspecialchars($limpieza['certificado'] ?: '-') . '</td>
            <td>' . htmlspecialchars($limpieza['procedimiento'] ?: '-') . '</td>
          </tr>';
      }

      $html .= '
        </table>
      </div>
    </div>';
    }

    // Observaciones
    if(!empty($d['entrega']['observaciones'])) {
      $html .= '
    <div class="seccion">
      <div class="seccion-titulo">OBSERVACIONES</div>
      <div class="seccion-contenido">
        <p>' . nl2br(htmlspecialchars($d['entrega']['observaciones'])) . '</p>
      </div>
    </div>';
    }

    $html .= '
    <div class="footer">
      Documento generado el ' . date('d/m/Y H:i') . ' - Sistema Barril.cl<br>
      Este certificado acredita la trazabilidad completa del producto desde su producción hasta la entrega.' . ($this->es_halal ? '<br><strong>Certificado de Trazabilidad Halal</strong>' : '') . '
    </div>

    </body>
    </html>';

    return $html;
  }

  /**
   * Genera y descarga el PDF
   * @param string $output 'D' para descargar, 'I' para mostrar en navegador, 'S' para string
   */
  public function generar($output = 'D') {
    require_once($GLOBALS['base_dir'] . '/vendor_php/dompdf_autoload.php');

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', false); // Usar parser nativo, no requiere Masterminds
    $options->set('isRemoteEnabled', false);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($this->generarHTML());
    $dompdf->setPaper('Letter', 'portrait');
    $dompdf->render();

    $filename = 'Trazabilidad_' . $this->datos['producto']['codigo'] . '_' . date('Ymd') . '.pdf';

    if($output == 'S') {
      return $dompdf->output();
    }

    $dompdf->stream($filename, array('Attachment' => ($output == 'D')));
  }

  /**
   * Obtener los datos recopilados (para debug)
   * @return array
   */
  public function getDatos() {
    return $this->datos;
  }
}
