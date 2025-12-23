<?php

  class Batch extends Base {

    public $batch_date;
    public $batch_id_usuarios_cocinero = 0;
    public $id_recetas = 0;
    public $batch_nombre = '';
    public $batch_litros = 0;

    public $licor_temperatura = 0;
    public $licor_ph = 0;
    public $licor_litros = 0;

    public $maceracion_hora_inicio;
    public $maceracion_temperatura = 0;
    public $maceracion_litros = 0;
    public $maceracion_ph = 0;
    public $maceracion_hora_finalizacion;

    public $lavado_de_granos_hora_inicio;
    public $lavado_de_granos_mosto = 0;
    public $lavado_de_granos_densidad = 0;
    public $lavado_de_granos_tipo_de_densidad = '';
    public $lavado_de_granos_hora_termino;

    public $coccion_ph_inicial = 0;
    public $coccion_ph_final = 0;
    public $coccion_recilar = 0;

    public $combustible_gas = 0;

    public $inoculacion_temperatura = 0;

    public $fermentacion_date;
    public $fermentacion_hora_inicio;
    public $inoculacion_temperatura_inicio = 0;

    public $fermentacion_id_activos = 0;
    public $fermentacion_temperatura = 0;
    public $fermentacion_hora_finalizacion;
    public $fermentacion_ph = 0;
    public $fermentacion_densidad = 0;
    public $fermentacion_tipo_de_densidad = '';
    public $fermentacion_finalizada = 0;
    public $fermentacion_finalizada_datetime;

    public $traspaso_datetime;

    public $maduracion_date;
    public $maduracion_temperatura_inicio = 0;
    public $maduracion_hora_inicio;
    public $maduracion_temperatura_finalizacion = 0;
    public $maduracion_hora_finalizacion;

    public $datetime_finalizacion;
    
    public $observaciones = "";
    public $creada;
    public $etapa_seleccionada = 'batch';
    public $tipo = 'Batch';

    public $finalizacion_date = '';

    // Campos ML - Métricas de producto final
    public $abv_final = null;
    public $ibu_final = null;
    public $color_ebc = null;

    // Campos ML - Rendimiento
    public $rendimiento_litros_final = null;
    public $merma_total_litros = null;
    public $densidad_final_verificada = null;

    // Campos ML - Calidad sensorial
    public $calificacion_sensorial = null;
    public $notas_cata = "";

    // Campos ML - Condiciones ambientales
    public $temperatura_ambiente_promedio = null;
    public $humedad_relativa_promedio = null;

    // Campos ML - Cocción
    public $tiempo_hervido_total_min = null;
    public $densidad_pre_hervor = null;
    public $densidad_pre_hervor_unidad = 'SG';
    public $densidad_post_hervor = null;
    public $densidad_post_hervor_unidad = 'SG';
    public $volumen_pre_hervor_l = null;
    public $volumen_post_hervor_l = null;
    public $evaporacion_pct = null;
    public $eficiencia_coccion_pct = null;
    public $energia_coccion_kwh = null;
    public $sensor_temp_id = null;
    public $resumen_json = null;

    public function __construct($id = null) {
      $this->tableName("batches");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date("Y-m-d H:i:s");
        $this->batch_date = date('Y-m-d'); 
      }
    }

    public function setPropertiesNoId($fields) {
      parent::setPropertiesNoId($fields);

      // Limpiar campos que deben ser NULL cuando están vacíos
      $nullableFields = [
        'sensor_temp_id', 'resumen_json', 'tiempo_hervido_total_min',
        'densidad_pre_hervor', 'densidad_post_hervor', 'volumen_pre_hervor_l',
        'volumen_post_hervor_l', 'evaporacion_pct', 'eficiencia_coccion_pct',
        'energia_coccion_kwh', 'temperatura_ambiente_promedio', 'humedad_relativa_promedio',
        'densidad_final_verificada'
      ];
      foreach($nullableFields as $field) {
        if(property_exists($this, $field) && $this->{$field} === '') {
          $this->{$field} = null;
        }
      }
    }

    public function setSpecifics($post) {

      // Decodificar JSON strings si vienen como strings (desde AJAX)
      $jsonFields = ['insumos', 'lupulizaciones', 'enfriados', 'traspasos', 'fermentacion_fermentadores'];
      foreach($jsonFields as $field) {
        if(isset($post[$field]) && is_string($post[$field])) {
          $decoded = json_decode($post[$field], true);
          if($decoded !== null) {
            $post[$field] = $decoded;
          }
        }
      }

      if($this->id == "") {
        $this->save();
        //NotificacionControl::trigger('Nuevo Batch',$obj);
      } else {
        $batches_insumos = BatchInsumo::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_insumos as $bi) {
          $insumo = new Insumo($bi->id_insumos);
          $insumo->bodega += $bi->cantidad;
          $insumo->save();
          $bi->delete();
        }
        $batches_lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_lupulizaciones as $bl) {
          $bl->delete();
        }
        $batches_enfriados = BatchEnfriado::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_enfriados as $be) {
          $be->delete();
        }
        $batches_traspasos = BatchTraspaso::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_traspasos as $bt) {
          $bt->delete();
        }

        $batches_activos = BatchActivo::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_activos as $bt) {
          $fermentador = new Activo($bt->id_activos);
          $fermentador->id_batches = 0;
          $fermentador->save();
          $bt->delete();
        }
      }

      if(isset($post['insumos']) && is_array($post['insumos'])) {
        // Eliminar registros de levadura anteriores de este batch
        $levaduras_anteriores = BatchLevadura::getAll("WHERE id_batches='" . $this->id . "'");
        foreach($levaduras_anteriores as $lev) {
          $lev->delete();
        }

        foreach($post['insumos'] as $etapa_key => $etapa) {
          foreach($etapa as $insumo_data) {

            $batch_insumo = new BatchInsumo;
            $batch_insumo->id_batches = $this->id;
            $batch_insumo->id_insumos = $insumo_data['id'];
            $batch_insumo->cantidad = $insumo_data['cantidad'];
            $batch_insumo->tipo = "Receta";
            $batch_insumo->etapa = $etapa_key;
            $batch_insumo->etapa_index = $insumo_data['etapa_index'];
            $batch_insumo->date = date('Y-m-d');
            $batch_insumo->save();

            // Si tiene datos de levadura, crear registro de BatchLevadura
            if(isset($insumo_data['es_levadura']) && $insumo_data['es_levadura'] && isset($insumo_data['levadura_data'])) {
              $lev_data = $insumo_data['levadura_data'];
              $batch_levadura = new BatchLevadura;
              $batch_levadura->id_batches = $this->id;
              $batch_levadura->id_batches_insumos = $batch_insumo->id;
              $batch_levadura->generacion = isset($lev_data['generacion']) ? intval($lev_data['generacion']) : 1;
              $batch_levadura->origen_batch = isset($lev_data['origen_batch']) ? $lev_data['origen_batch'] : '';
              $batch_levadura->cantidad_gramos = floatval($insumo_data['cantidad']); // Usar la cantidad del insumo
              $batch_levadura->tasa_inoculacion = isset($lev_data['tasa_inoculacion']) ? floatval($lev_data['tasa_inoculacion']) : 0;
              $batch_levadura->viabilidad_medida = isset($lev_data['viabilidad_medida']) ? floatval($lev_data['viabilidad_medida']) : 0;
              $batch_levadura->vitalidad_medida = isset($lev_data['vitalidad_medida']) ? floatval($lev_data['vitalidad_medida']) : 0;
              $batch_levadura->uso_starter = isset($lev_data['uso_starter']) ? intval($lev_data['uso_starter']) : 0;
              $batch_levadura->volumen_starter_ml = isset($lev_data['volumen_starter_ml']) ? intval($lev_data['volumen_starter_ml']) : 0;
              $batch_levadura->atenuacion_real = isset($lev_data['atenuacion_real']) ? floatval($lev_data['atenuacion_real']) : 0;
              $batch_levadura->tiempo_lag_h = isset($lev_data['tiempo_lag_h']) ? floatval($lev_data['tiempo_lag_h']) : 0;
              $batch_levadura->observaciones = isset($lev_data['observaciones']) ? $lev_data['observaciones'] : '';
              $batch_levadura->save();
            }

            $insumo_obj = new Insumo($batch_insumo->id_insumos);
            $insumo_obj->bodega -= $batch_insumo->cantidad;
            $insumo_obj->save();

          }
        }
      }

      if(isset($post['lupulizaciones']) && is_array($post['lupulizaciones'])) {
        foreach($post['lupulizaciones'] as $l_key => $lupulizacion) {

          $batch_lupulizacion = new BatchLupulizacion;
          $batch_lupulizacion->id_batches = $this->id;
          $batch_lupulizacion->seq_index = $l_key;
          $batch_lupulizacion->tipo = $lupulizacion['tipo'];
          $batch_lupulizacion->date = $lupulizacion['date'];
          $batch_lupulizacion->hora = $lupulizacion['hora'];
          $batch_lupulizacion->save();
        }
      }


      if(isset($post['enfriados']) && is_array($post['enfriados'])) {
        foreach($post['enfriados'] as $l_key => $enfriado) {

          $batch_enfriado = new BatchEnfriado;
          $batch_enfriado->id_batches = $this->id;
          $batch_enfriado->seq_index = $l_key;
          $batch_enfriado->temperatura_inicio = $enfriado['temperatura_inicio'];
          $batch_enfriado->ph = $enfriado['ph'];
          $batch_enfriado->densidad = $enfriado['densidad'];
          $batch_enfriado->ph_enfriado = $enfriado['ph_enfriado'];
          $batch_enfriado->date = $enfriado['date'];
          $batch_enfriado->hora_inicio = $enfriado['hora_inicio'];
          $batch_enfriado->save();

        }
      }

      if(isset($post['traspasos']) && is_array($post['traspasos'])) {
        foreach($post['traspasos'] as $l_key => $traspaso) {



          $batch_traspaso = new BatchTraspaso;
          $batch_traspaso->id_batches = $this->id;
          $batch_traspaso->seq_index = $l_key;
          $batch_traspaso->id_fermentadores_inicio = $traspaso['id_fermentadores_inicio'];
          $batch_traspaso->id_fermentadores_final = $traspaso['id_fermentadores_final'];
          $batch_traspaso->cantidad = $traspaso['cantidad'];
          $batch_traspaso->date = $traspaso['date'];
          $batch_traspaso->hora = $traspaso['hora'];
          $batch_traspaso->save();
          //print_r($batch_traspaso);


        }
      }

      if(isset($post['fermentacion_fermentadores']) && is_array($post['fermentacion_fermentadores'])) {
        foreach($post['fermentacion_fermentadores'] as $l_key => $traspaso) {
          $batch_traspaso = new BatchActivo;
          $batch_traspaso->setPropertiesNoId($traspaso);
          $batch_traspaso->save();
          $fermentador = new Activo($batch_traspaso->id_activos);
          $fermentador->id_batches = $this->id;
          $fermentador->save();
        }
      }



      /*$receta = new Receta($this->id_recetas);
      foreach($receta->insumos_arr as $ri) {

        $insumo = new Insumo($ri->id_insumos);
        $insumo->bodega -= $ri->cantidad;
        $insumo->save();

        $batch_insumo = new BatchInsumo;
        $batch_insumo->id_batches = $this->id;
        $batch_insumo->id_insumos = $ri->id_insumos;
        $batch_insumo->cantidad = $ri->cantidad;
        $batch_insumo->tipo = "Receta";
        $batch_insumo->save();

      }*/

      /*if(isset($post['dryhop'])) {
        if(is_array($post['dryhop'])) {
          foreach($post['dryhop'] as $dh) {
            $insumo = new Insumo($dh['id']);
            $insumo->bodega -= $dh['cantidad'];
            $insumo->save();
            $batch_insumo = new BatchInsumo;
            $batch_insumo->id_batches = $this->id;
            $batch_insumo->id_insumos = $dh['id'];
            $batch_insumo->cantidad = $dh['cantidad'];
            $batch_insumo->tipo = "Dryhop";
            $batch_insumo->date = $dh['date'];
            $batch_insumo->save();
          }
        }
      }*/

      /*$batches_barriles_anteriores = $this->getRelations("barriles");
      foreach($batches_barriles_anteriores as $bba) {
        $barril = new Barril($bba);
        $barril->id_batches = 0;
        $barril->estado = "En planta";
        $barril->save();
        $this->deleteRelation($barril);
      }

      $batches_cajas_anteriores = BatchCaja::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_cajas_anteriores as $bca) {
        $bca->delete();
      }

      if(!isset($post['productos'])) {
        return false;
      }

      if(!is_array($post['productos'])) {
        return false;
      }

      if(count($post['productos']) == 0) {
        return false;
      }

      foreach($post['productos'] as $producto) {
        if($producto['tipo'] == "Barril") {
          $barril = new Barril($producto['id_barriles']);
          $barril->id_batches = $this->id;
          $barril->estado = "En sala de frio";
          $barril->save();
          $this->createRelation($barril);
        } else
        if($producto['tipo'] == "Caja") {
          $batch_caja = new BatchCaja;
          $batch_caja->id_batches = $this->id;
          $batch_caja->cantidad = $producto['cantidad'];
          $batch_caja->save();
        }
      }

      $recetas = Recetas::getAll();
      foreach($recetas as $receta) {
        foreach($receta->insumos_arr as $ri) {

          $insumo = new Insumo($ri->id_insumos);
          if($insumo->bodega < $ri->cantidad) {
            NotificacionControl::trigger('Insumos insuficientes para Batches',$receta);
            break;
          }

        }

      }*/

    }

    public function deleteSpecifics($values) {

      $batches_insumos = BatchInsumo::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_insumos as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $insumo->bodega += $bi->cantidad;
        $insumo->save();
        $bi->delete();
      }
      
      $batches_lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_lupulizaciones as $bl) {
        $bl->delete();
      }
      $batches_enfriados = BatchEnfriado::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_enfriados as $be) {
        $be->delete();
      }
      $batches_traspasos = BatchTraspaso::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_traspasos as $bt) {
        $bt->delete();
      }

    }

    public function agregarActivo($data) {

      $fermentador = new Activo($data['id_activos']);

      $batch_activo = new BatchActivo;
      $batch_activo->setProperties($data);
      $batch_activo->id_batches = $this->id;
      $batch_activo->litraje = $fermentador->litraje;
      $batch_activo->save();

      $fermentador->id_batches = $this->id;
      $fermentador->save();

      return $batch_activo;

    }

    public function editarActivo($data) {

      if(empty($data['id'])) return;
      
      $batch_activo = new BatchActivo($data['id']);

      if($data['id_activos'] != $batch_activo->id_activos) {
        $fermentador_anterior = new Activo($batch_activo->id_activos);
        $fermentador_anterior->id_batches = 0;
        $fermentador_anterior->save();
      }

      $fermentador = new Activo($data['id_activos']);

      $batch_activo->setProperties($data);
      $batch_activo->id_batches = $this->id;
      $batch_activo->litraje = $fermentador->litraje;
      $batch_activo->save();
      
      $fermentador->id_batches = $this->id;
      $fermentador->save();

    }

    public function eliminarActivo($data) {

      if(!isset($data['id_batches_activos'])) {
        return false;
      }

      $batch_activo = new BatchActivo($data['id_batches_activos']);

      $fermentador_anterior = new Activo($batch_activo->id_activos);
      $fermentador_anterior->id_batches = 0;
      $fermentador_anterior->save();

      $batch_activo->delete();


    }

    /**
     * Calcula la eficiencia del batch (rendimiento final / objetivo)
     * @return float|null
     */
    public function calcularEficiencia() {
      $litros = floatval($this->batch_litros);
      $rendimiento = floatval($this->rendimiento_litros_final);
      if($litros > 0 && $rendimiento > 0) {
        return round(($rendimiento / $litros) * 100, 2);
      }
      return null;
    }

    /**
     * Calcula el porcentaje de merma
     * @return float|null
     */
    public function calcularMermaPorcentual() {
      $litros = floatval($this->batch_litros);
      $merma = floatval($this->merma_total_litros);
      if($litros > 0 && $merma > 0) {
        return round(($merma / $litros) * 100, 2);
      }
      return null;
    }

    /**
     * Determina la línea productiva del batch basado en activos o receta
     * @return string 'alcoholica'|'analcoholica'|'general'
     */
    public function getLineaProductiva() {
      // Prioridad 1: Desde el primer activo asignado
      $batches_activos = BatchActivo::getAll("WHERE id_batches='" . $this->id . "' LIMIT 1");
      if(count($batches_activos) > 0) {
        $activo = new Activo($batches_activos[0]->id_activos);
        if(!empty($activo->linea_productiva)) {
          return $activo->linea_productiva;
        }
      }
      // Prioridad 2: Inferir de la clasificación de receta
      if($this->id_recetas > 0) {
        $receta = new Receta($this->id_recetas);
        if(in_array($receta->clasificacion, ['Cerveza', 'Cerveza Artesanal'])) {
          return 'alcoholica';
        }
        if(in_array($receta->clasificacion, ['Kombucha', 'Agua saborizada', 'Agua fermentada'])) {
          return 'analcoholica';
        }
      }
      return 'general';
    }

    /**
     * Obtiene el label de la línea productiva
     * @return string
     */
    public function getLineaProductivaLabel() {
      $linea = $this->getLineaProductiva();
      $lineas = [
        'alcoholica' => 'Alcohólica',
        'analcoholica' => 'Sin Alcohol',
        'general' => 'General'
      ];
      return isset($lineas[$linea]) ? $lineas[$linea] : 'General';
    }

    /**
     * Verifica si el batch es de línea sin alcohol (para Halal)
     * @return bool
     */
    public function esLineaSinAlcohol() {
      return $this->getLineaProductiva() == 'analcoholica';
    }

  }

 ?>
