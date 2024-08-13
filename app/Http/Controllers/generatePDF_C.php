<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class generatePDF_C extends BaseController
{



  //variable donde se definen los reportes
  protected $reportes = array(
    'Reporte_Calidad' => '/WEBU/Reporte Calidad v2',
    'Calidad_prueba' => '/WEBU/Reporte Calidad',
    'Foto_Preoperacional' => '/WEBU/preoperacional',
    'Informacion_preoperacional' => '/WEBU/INFORME DETALLADO PREOPERACIONAL',
    'No_preoperacional' => '/WEBU/PR - Equipos sin preoperacional',
    'Liberacion' => '/WEBU/Reporte Liberaciones',
    'Viajes_no_aprobados' => '/TIMESCAN/INFORMES VIAJES/INFORME VIAJES NO APROBADOS',
    'Viajes' => '/TIMESCAN/INFORMES VIAJES/INFORME VIAJES REGISTRADOS',
    'Inspeccion_calidad' => '/WEBU/Inspeccion de calidad',
    'Reporte_hallazgo' => '/WEBU/Reporte Informe de hallazgos',
    'Reporte_hallazgo_individual' => '/WEBU/Proyecto 1/FM-ARI-PRD-033-INSPECCIÓN MANTENIMIENTO Y SEÑALIZACIÓN VIAL',
    'Seguridad_sitio' => '/WEBU/Proyecto 1/SEGURIDAD EN SITIO',
  );

  /**
   * @param string[] $reportes
   */
  public function __construct()
  {
    if (Request::capture()->query->get('proyecto') == 2) {
      $this->reportes = array(
        'Reporte_Calidad' => '',
        'Calidad_prueba' => '',
        'Foto_Preoperacional' => '/WEBU/proyecto2/preoperacional',
        'Informacion_preoperacional' => '/WEBU/proyecto2/INFORME DETALLADO PREOPERACIONAL',
        'No_preoperacional' => '/WEBU/proyecto2/PR - Equipos sin preoperacional',
        'Liberacion' => '',
        'Viajes_no_aprobados' => '',
        'Viajes' => '',
        'Inspeccion_calidad' => ''
      );
      
    }
     if (Request::capture()->query->get('proyecto') == 3) {
      $this->reportes = array(
        'Reporte_Calidad' => '',
        'Calidad_prueba' => '',
        'Foto_Preoperacional' => '/WEBU/proyecto 3/preoperacional',
        'Informacion_preoperacional' => '/WEBU/proyecto 3/INFORME DETALLADO PREOPERACIONAL',
        'No_preoperacional' => '/WEBU/proyecto 3/PR - Equipos sin preoperacional',
        'Liberacion' => '',
        'Viajes_no_aprobados' => '',
        'Viajes' => '',
        'Inspeccion_calidad' => ''
      );
    }
  }

  public function informe($solicitud)
  {
    $options = array(
      'username' => env('INF_USER'),
      'password' => env('INF_PASS')
    );

    $ssrs = new \SSRS\Report(env('INF_SERVER'), $options);
    $result = $ssrs->loadReport('/WEBU/LIBERACION2');

    $reportParameters = array(
      'id_solicitud_liberaciones' => $solicitud
    );


    $ssrs->setSessionId($result->executionInfo->ExecutionID)
      ->setExecutionParameters($reportParameters);

    $output = $ssrs->render('PDF'); // PDF | XML | CSV
    return $output->download('liberacion_' . $solicitud . '.pdf');
  }

  public function informe2($solicitud)
  {
    $options = array(
      'username' => env('INF_USER'),
      'password' => env('INF_PASS')
    );

    $ssrs = new \SSRS\Report(env('INF_SERVER'), $options);
    $result = $ssrs->loadReport('/WEBU/LIBERACION');

    $reportParameters = array(
      'id_solicitud_liberaciones' => $solicitud,
      //'date'=>date("Y-m-d H:i:s")
    );


    $ssrs->setSessionId($result->executionInfo->ExecutionID)
      ->setExecutionParameters($reportParameters);

    $output = $ssrs->render('PDF'); // PDF | XML | CSV
    return $output->download('liberacion_' . $solicitud . '.pdf');
  }

  public function InformeExcel($ids)
  {
    $options = array(
      'username' => env('INF_USER'),
      'password' => env('INF_PASS')
    );


    if (substr($ids, -1) === ',') {
      $valores = explode(',', substr($ids, 0, strlen($ids) - 1));
    } else {

      $valores = explode(',', $ids);
    }



    $ssrs = new \SSRS\Report(env('INF_SERVER'), $options);
    $result = $ssrs->loadReport('/WEBU/Reporte Calidad');

    $reportParameters = array(
      'id' => $valores
      //'date'=>date("Y-m-d H:i:s")
    );


    $ssrs->setSessionId($result->executionInfo->ExecutionID)
      ->setExecutionParameters($reportParameters);

    $output = $ssrs->render('EXCEL'); // PDF | XML | CSV |EXCEL |WORD
    return $output->download('Inspeccion_de_calidad_' . date("YmdH:i") . '.xls');
  }
  //version mejorada de la conexion de reportes con srss, descarga de informe de excel dinamica
  public function InformeExcel2($report, Request $request)
  {

    $options1 = array(
      'username' => env('INF_USER'),
      'password' => env('INF_PASS')
    );
    //extrae los parametros para realizar la consulta
    $parametros1 = collect($request->all());
    $parametros = collect();
    foreach ($parametros1 as $key => $value) {
      if (substr($value, -1) == ',') {
        $value = substr($value, 0, strlen($value) - 1);
        //return $value;
      }
      $values = explode(',', $value);
      if (!(strcmp($key, 'proyecto') == 0 || strcmp($key, 'vrg') == 0)) {
        $parametros->put($key, $values);
      }
    }
    //return $parametros;
    //conexion al reporte
    $ssrs = new \SSRS\Report(env('INF_SERVER'), $options1);
    //valida que el reporte exista
    if (array_key_exists($report, $this->reportes)) {
      //inicia la instancia del reporte
      $result = $ssrs->loadReport($this->reportes[$report]);
      //envia los datos de la session de conexion al reporte
      $ssrs->setSessionId($result->executionInfo->ExecutionID);
      //valida si existen parametros a enviar
      if (!empty($parametros)) {
        //envia los parametro de consulta del reporte
        $ssrs->setExecutionParameters($parametros->toArray());
      }
      //devuelve el reporte en formato excel al servidor
      $output = $ssrs->render('EXCELOPENXML'); // PDF | XML | CSV |EXCEL |WORD |EXCELOPENXML (xlsx) , WORDOPENXML (docx)
      //el servidor lo descarga en formato para el equipo local
      return $output->download($report . date("YmdH:i") . '.xlsx');

    } else {
      return 'Error con los parametros del reporte [001]';
    }
  }

  public function InformePdf2($report, Request $request)
  {
    $options1 = array(
      'username' => env('INF_USER'),
      'password' => env('INF_PASS')
    );
    //extrae los parametros para realizar la consulta
    $parametros1 = collect($request->all());
    $parametros = collect();
    foreach ($parametros1 as $key => $value) {
      if (substr($value, -1) == ',') {
        $value = substr($value, 0, strlen($value) - 1);
        //return $value;
      }
      $values = explode(',', $value);
      if (!(strcmp($key, 'proyecto') == 0 || strcmp($key, 'vrg') == 0)) {

        $parametros->put($key, $values);
      }
    } //conexion al reporte
    $ssrs = new \SSRS\Report(env('INF_SERVER'), $options1);
    //valida que el reporte exista
    if (array_key_exists($report, $this->reportes)) {
      //inicia la instancia del reporte
      $result = $ssrs->loadReport($this->reportes[$report]);
      //envia los datos de la session de conexion al reporte
      $ssrs->setSessionId($result->executionInfo->ExecutionID);
      //valida si existen parametros a enviar
      if (!empty($parametros)) {
        var_dump($parametros);
        //envia los parametro de consulta del reporte
        $ssrs->setExecutionParameters($parametros->toArray());
      }
      //devuelve el reporte en formato excel al servidor
      $output = $ssrs->render('PDF'); // PDF | XML | CSV |EXCEL |WORD
      //el servidor lo descarga en formato para el equipo local
      return $output->download($report . date("YmdH:i") . '.pdf');

    } else {
      return 'Error con los parametros del reporte [001]';
    }

  }
}