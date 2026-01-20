<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Planosv extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $datos_sesion = array(
      'ultima_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2),
    );
    $this->session->set_userdata($datos_sesion);
    if (!$this->session->userdata('usuario_id')) {
      redirect(base_url() . 'admin/login');
    }

    $this->data['titulo'] = 'Plano Saldos y valores';
    $this->modulo = 'gestion-Saldos-Valores';
    $this->tabla = 'act_cartera_tardigitalV3';
    $this->descripcion = 'Saldos_Valores';
    $this->load->model('Modelo_general');
    $this->load->library('formato');
    $this->load->helper('download');
    $this->load->library('general');
    $this->load->library('correo');
    $this->general->inic($this->Modelo_general);
    $this->data['empresas'] = $this->general->empresas();
    $this->data['empresa'] = $this->general->empresa();
    $this->data['menu_empresas'] = $this->general->empresas_menu($this->session->userdata('usuario_perfil'));
    $this->data['menus'] = $this->general->menu($this->session->userdata('usuario_perfil'), $this->session->userdata('usuario_empresa'));
    $this->permiso = $this->general->permiso($this->uri->segment(1) . '/' . $this->uri->segment(2), $this->session->userdata('usuario_perfil'));


    //Javascript
    $this->data['script_adicional'] = "
      $('#descripcion').focus();
    ";
  }

  public function index()
  {
    if (!$this->permiso['ver']) {
      $this->session->set_flashdata('flash_message', 'No tiene permiso para realizar esta acción!');
      redirect(base_url() . 'admin/panel');
    }
    $this->general->log(
      $this->session->userdata('usuario_id'),
      $this->input->ip_address(),
      $this->modulo,
      'Ingreso a Módulo'
    );
    $datos_sesion['filtro'] = '';
    $datos_sesion['filtro2'] = date("Y-m-d");
    $this->session->set_userdata($datos_sesion);

    if ($this->session->flashdata('flash_message')) {
      $this->session->set_flashdata('flash_message', $this->session->flashdata('flash_message'));
    }
    redirect(base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/adicionar_plano/1');
  }

  public function adicionar_plano()
  {
    if (!$this->permiso['adicionar']) {
      $this->session->set_flashdata('flash_message', 'No tiene permiso para realizar esta acción!');
      redirect(base_url() . 'admin/panel');
    }
    $this->data['cols_container'] = 12;
    $this->data['cols_skip'] = 0;
    //$this->data['texto_submit'] = 'Cambiar Contraseña';
    //$this->data['accion_submit'] = $this->uri->segment(1).'/'.$this->uri->segment(2).'/cambiar/';

    $this->data['campos']['archivo'] = array(
      'tipo'      => 'file',
      'label'     => 'Archivo Plano',
      'colsform'  => '6',
      'colslist'  => '0',
      'rules'     => 'trim',
      'formato'   => 'minusculas',
      'clase'     => 'form-control noenter',
    );

    $this->data['campos']['fecha_archivo'] = array(
      'tipo'      => 'date',
      'label'     => 'Fecha Vigencia Plano',
      'colsform'  => '6',
      'colslist'  => '0',
      'rules'     => 'trim',
      'formato'   => 'minusculas',
      'clase'     => 'form-control noenter',
    );
    /*
    $this->data['campos']['tag']=array(
      'label'     => 'Etiqueta',
      'colslist'  => '1',
    );*/

    $this->data['campos']['fecadi'] = array(
      'label'     => 'Creado',
      'colslist'  => '1',
    );

    $this->data['campos']['visto'] = array(
      'label'     => 'Visto',
      'colslist'  => '0',
    );

    foreach ($this->data['campos'] as $campo => $atributos) {
      if (isset($atributos['rules'])) {
        $this->form_validation->set_rules($campo, $atributos['label'], $atributos['rules']);
      }
    }
    /*fin formulario*/

    $this->data['error_accion'] = '';
    if ($this->input->server('REQUEST_METHOD') === 'POST') {

      if ($this->form_validation->run()) {
        /*ini validar carga*/
        $ruta = 'cargas/';
        @mkdir(FCPATH . $ruta);
        $ruta .= 'plano_tarjetas';
        @mkdir(FCPATH . $ruta);

        $config = array(
          'upload_path' => FCPATH . $ruta,
          'allowed_types' => 'txt|',
          'overwrite' => false,
        );
        //var_dump($ruta,$config);exit;

        $this->load->library('upload', $config);

        unset($this->data['campos']['archivo']);
        if ($this->upload->do_upload('archivo')) {
          $upload_data = $this->upload->data();
          $file_name = $upload_data['file_name'];
          unset($this->data['campos']['archivo']);
          foreach ($this->data['campos'] as $campo => $atributos) {
            if (isset($atributos['colsform'])) {
              $datos[$campo] = str_replace(',', '', $this->input->post($campo));
              if (isset($atributos['formato'])) {
                if ($atributos['formato'] == 'minusculas' || $atributos['formato'] == 'mayusculas' || $atributos['formato'] == 'titulo') {
                  $datos[$campo] = $datos[$campo] = $this->formato->{$atributos['formato']}($datos[$campo]);
                }
              }
            } else {
              continue;
            }
          }
          $datos['archivo'] = $ruta . '/' . $file_name;
          //var_dump($datos);exit;
          //$id = $this->Modelo_general->guardar( $this->tabla, $datos );

          $id = $this->cargar_archivo($datos['archivo'], $datos['fecha_archivo']);
        } else {
          $this->error = 'Error: ' . $this->upload->display_errors();
          //exit ('Error: '.$this->upload->display_errors());
        }
      }
    }

    $this->data['botones']['Cancelar'] = array(
      'ruta'  => $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/listado/' . $this->session->userdata('ultima_pagina'),
      'clase' => 'btn-danger',
      'icono' => 'fa-undo',
    );
    $this->load->view('templates/header', $this->data);
    $this->load->view('templates/menu', $this->data);
    $this->load->view('templates/formulario', $this->data);
    $this->load->view('templates/footer', $this->data);
    $this->general->log(
      $this->session->userdata('usuario_id'),
      $this->input->ip_address(),
      $this->modulo,
      'Ingreso a Adicionar'
    );
  }
  public function cargar_archivo($archivo, $fecha_archivo)
  {
    $timestamp = strtotime($fecha_archivo);
    $fecha_conv_archivo = date('Ymd', $timestamp);

    $contador = 0;
    $fecha_hoy = date("Y");
    $fechaManana = date('Ymd', strtotime($fecha_hoy . ' +1 day'));
    // Verificar si el archivo existe
    if (file_exists($archivo)) {
      // Leer el contenido del archivo
      $contenido = file_get_contents($archivo);

      // Dividir el contenido en líneas
      $lineas = explode("\n", $contenido);
      $contador = count($lineas);
      // Recorrer las líneas de datos

      for ($i = 1; $i < count($lineas); $i++) {
        // Obtener los valores de cada línea
        $valores = explode("|", $lineas[$i]);

        // Asignar los valores a variables
        // Variables para calcular  Valor a reportar

        //Variables necesarias para el calculo de la variable Valor vencido
        $Vven_capitali = trim($valores[4]) + trim($valores[17]);
        $Vven_capital = trim($valores[5]);
        $Vven_interes = trim($valores[6]);
        $Vven_mora = trim($valores[7]);
        $Vven_segvida = trim($valores[8]);
        $Otros_conceptos = trim($valores[18]);

        // Validacion valor vencido otros conceptos 
        if ($Otros_conceptos <= 0) {
          $Otros_conceptos = 0;
        }
        // Fin variable valor vencido            
        $ValorVencido = $Vven_capitali + $Vven_capital + $Vven_interes + $Vven_mora + $Vven_segvida + $Otros_conceptos;

        $ValorCuota = trim($valores[9]);
        $DiasMora = trim($valores[11]);
        $ValorProxVencimineto = trim($valores[9]);
        $Modalidad = trim($valores[2]);
        // Fin variables para calcular

        $SaldoCapital = trim($valores[12]);
        $fecha_couta = trim($valores[10]);
        $timestamp = strtotime($fecha_couta);
        $fecha_convertida = date('Y-m-d', $timestamp);
        $dia_cuota = $this->general->datos_fechas($fecha_convertida);
        $dia_cuota = $dia_cuota["dia"];
        $dia_actual = $this->general->datos_fechas(date("Y-m-d"));
        $dia_actual = $dia_actual["dia"];

        $observacion = '';
        $valorreporte = 0;
        //listado de las modalidades a reportar con proximo vencimiento
        $Arrmod[0] = "AAN";
        $Arrmod[1] = "AAR";
        $Arrmod[2] = "PGA";
        $Arrmod[3] = "PGM";
        $Arrmod[4] = "PGE";
        $Arrmod[5] = "RTN";
        $Arrmod[6] = "RTR";
        //var_dump(trim($valores[12]));
        if ($SaldoCapital < 1) {
          continue;
        } else {
          if ($DiasMora == 0) {
            //$valorreporte = $ValorCuota; // se cambia el 7/11/2023 hugo
            $valorreporte = $ValorProxVencimineto;
            $observacion = 'Valores 0';
          } elseif ($DiasMora > 0 && $DiasMora < 30) {
            if ($dia_cuota <= $dia_actual) {
              $valorreporte = $ValorProxVencimineto;
              $observacion = 'Vencido <30 con dias';
            } else {
              if ($ValorVencido < $ValorProxVencimineto) {
                $valorreporte = $ValorProxVencimineto + $ValorVencido;
              } else {
                $valorreporte = $ValorVencido;
              }
              $observacion = 'Vencido <30 ';
            }
          } elseif ($DiasMora > 29) {
            $valorreporte = $ValorVencido;
            $observacion = 'Vencido > 29';
          } else {
            $observacion = 'Error en diasvencidos';
          }
        } //Finif saldo capital
        if ($valorreporte == 0) {
          $valorreporte = $ValorVencido;
        } elseif ($ValorVencido == 0 && $valorreporte == 0) {
          continue;
        } elseif ($valorreporte <= 0) {
          continue;
        }

        $nombre1 = trim($valores[15]);
        $nombre2 = trim($valores[16]);
        $apellido1 = trim($valores[13]);
        $apellido2 = trim($valores[14]);
        if ($nombre1 == "" && $nombre2 == "") {
          $nombre1 = "EMPRESA";
        }
        if ($apellido1 == "" && $apellido2 == "") {
          $apellido1 = "EMPRESA";
        }
        $campos_reporte = array(
          'id_entidad' => 9,
          'id_sucursal' => 1,
          'obligacion' => trim($valores[0]),
          'nombres' => $nombre1 . ' ' . $nombre2,
          'apellidos' => $apellido1 . ' ' . $apellido2,
          'grado' => " ",
          'valor_reportar' => $valorreporte,
          'recargo' => " ",
          'periodo' => date(Ym),
          'dia_corte' => " ",
          'tipo_pago' => 3,
          'cc' => trim($valores[1]),
          'observacion' => $observacion,
        );
        $campos_gou = array(
          'obligacion' => trim($valores[0]),
          'cc' => trim($valores[1]),
          'cc1' => trim($valores[1]),
          'nombres' => $nombre1 . ' ' . $nombre2 . ' ' . $apellido1 . ' ' . $apellido2,
          'valor_reportar' => $valorreporte,
          'periodo' => $fecha_conv_archivo,
          'valor_recargo' => '00000',
          'periodofin' => $fechaManana,
          'tipo_pago' => 0,
        );
        $datos_Re[$i] = $campos_reporte;
        $datos_Gou[$i] = $campos_gou;
      } //fin recorrido lectura del archivo

      //var_dump($datos_Re);exit;
    } else { //lectura del archivo plano
      echo "Error al leer archivo plano.";
    }
    // Después de obtener y procesar los datos, generamos el archivo
    $file_name = 'archivo_reporte' . date("m-d-Y") . '.csv';
    $csv_data = $this->generate_csv_data($datos_Re); // Función para convertir el arreglo $datos_Re a formato CSV
    $csv_data_gou = $this->generate_csv_data_gou($datos_Gou, $fecha_conv_archivo);
    // Descargar el archivo usando la función force_download
    $this->load->helper('download');
    //force_download($file_name, $csv_data);
    // Crear un archivo ZIP
    $zip = new ZipArchive();
    $file_name_zip = 'archivos.zip';
    $zip->open($file_name_zip, ZipArchive::CREATE);

    // Añadir el primer archivo CSV al ZIP
    $zip->addFromString('archivo_Re.csv', $csv_data);

    // Añadir el segundo archivo CSV al ZIP
    $zip->addFromString('archivo_Gou.csv', $csv_data_gou);

    // Cerrar el ZIP
    $zip->close();

    // Descargar el archivo ZIP
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $file_name_zip . '"');
    readfile($file_name_zip);

    // Eliminar el archivo ZIP después de descargarlo (opcional)
    unlink($file_name_zip);
    // Redireccionar después de la descarga
    redirect(base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/adicionar_plano/' . $this->session->userdata('ultima_pagina'));
  }
  private function generate_csv_data($data)
  {
    $output = fopen('php://temp', 'w'); // Abrir un puntero de archivo temporal
    fwrite($output, "\xEF\xBB\xBF");
    // Encabezados CSV
    $csv_headers = array('ID_ENTIDAD', 'ID_SUCURSAL', 'A_OBLIGA', 'NOMBRE_CLIENTE', 'APELLIDO_CLIENTE', 'GRADO', 'V_CUOTA', 'RECARGO', 'PERIODO', 'DIA_CORTE', 'TIPO_PAGO', 'C.C', 'observacion');
    fputcsv($output, $csv_headers);
    // Datos CSV
    foreach ($data as $row) {
      $row = array_map(function ($value) {
        return mb_convert_encoding($value, 'UTF-8', 'auto');
      }, $row);
      fputcsv($output, $row);
    }
    rewind($output); // Retroceder el puntero de archivo al inicio
    $csv_data = stream_get_contents($output); // Obtener los datos CSV del puntero de archivo
    fclose($output); // Cerrar el puntero de archivo

    return $csv_data;
  } //fin generar csv
  private function generate_csv_data_gou($data, $fecha_conv_archivo)
  {
    $output = fopen('php://temp', 'w'); // Abrir un puntero de archivo temporal
    // Encabezados CSV
    fwrite($output, "\xEF\xBB\xBF");
    $sumatotal = 0;
    $data = array_filter($data, function ($dato) {
      return $dato['valor_reportar'] >= 0;
    });

    $cantidad = count($data);
    foreach ($data as &$dato) {
      $sumatotal += $dato['valor_reportar'];
      $dato['valor_reportar'] .= '00';
    }

    // Reemplazar caracteres especiales en los encabezados
    $csv_headers = array_map(function ($header) {
      return $this->replace_special_characters($header);
    }, array($fecha_conv_archivo, '1000', 'A', '8000803428', $cantidad, '0', 'RECAUDOS MICROSITIO CERRADO', $sumatotal . '00'));

    // Desactivar el envoltorio de comillas dobles y escribir los encabezados al CSV
    fputcsv($output, $csv_headers, ',', ' ');

    // Reemplazar caracteres especiales en los datos
    $data = array_map(function ($row) {
      return array_map(function ($value) {
        return $this->replace_special_characters($value);
      }, $row);
    }, $data);

    // Datos CSV
    foreach ($data as $row) {
      $row = array_map(function ($value) {
        return mb_convert_encoding($value, 'UTF-8', 'auto');
      }, $row);
      fputcsv($output, $row);
    }

    rewind($output); // Retroceder el puntero de archivo al inicio
    $csv_data = stream_get_contents($output); // Obtener los datos CSV del puntero de archivo
    fclose($output); // Cerrar el puntero de archivo

    return $csv_data;
  }

  private function replace_special_characters($str)
  {
    // Puedes personalizar esta función para reemplazar caracteres especiales según tus necesidades
    $str = str_replace('á', 'a', $str);
    $str = str_replace('é', 'e', $str);
    $str = str_replace('ñ', 'n', $str);
    $str = str_replace('Ñ', 'N', $str);
    // Agrega más reemplazos según sea necesario

    return $str;
  }
}/* Final del archivo */