<?php

//tiempo de espera en caso de tardar mas de 30 segundos una consulta grande
set_time_limit(3600);
//sin limite me memoria 
ini_set('memory_limit', '-1');
//ocultar los errores
error_reporting(0);

date_default_timezone_set('America/Mexico_City'); //Ajustando zona horaria


function conectarMySQL(){

    $dbhost="www.medicavial.net";
    //$dbhost="localhost";
    $dbuser="medica_webusr";
    $dbpass="tosnav50";
    // $dbuser="root";
    // $dbpass="";
    $dbname="medica_registromv";
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;

}

function generar_clave(){ 

    $pares = '24680';
    $nones = '13579';
    $vocales = 'AEIOU';
    $consonantes = "BCDEFGHIJKLMNOPQRSTUVWXYZ";
    $todos = $vocales . $pares . $consonantes . $nones;
    $valor = "";

    $valor .= substr($vocales,rand(0,4),1);
    $valor .= substr($consonantes,rand(0,23),1);
    $valor .= substr($pares,rand(0,4),1);
    $valor .= substr($nones,rand(0,4),1);
    $valor .= substr($todos,rand(0,34),1);

    return $valor;

} 

function ultimodiadelmes($mes) { 
      $month = date($mes);
      $year = date('Y');
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));

}
 
function primerdiadelmes($mes) {

  $month = date($mes);
  $year = date('Y');
  return date('Y-m-d', mktime(0,0,0, $month, 1, $year));

}

function ultimodiade($mes,$ano) { 
      $month = date($mes);
      $year = date($ano);
      $day = date("d", mktime(0,0,0, $month+1, 0, $year));
 
      return date('Y-m-d', mktime(0,0,0, $month, $day, $year));

}
 
function primerdiade($mes,$ano) {
  $month = date($mes);
  $year = date($ano);
  return date('Y-m-d', mktime(0,0,0, $month, 1, $year));

}


//Obtenemos la funcion que necesitamos y yo tengo que mandar 
//la URL de la siguiente forma api/api.php?funcion=login

$funcion = $_REQUEST['funcion'];
$unidad = $_REQUEST['unidad'];


if($funcion == 'buscaExpedientes'){
    
    //Obtenemos los datos que mandamos de angular
    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);
    $conexion = conectarMySQL();
    
    //Obtenemos los valores de usuario y contraseña 
    $fechaini = $data->search->fechaini;
    $fechafin = $data->search->fechafin;
    $folio = $data->search->folio;
    $lesionado = $data->search->lesionado;
    $poliza = $data->search->poliza;
    $reporte = $data->search->reporte;
    $dia = $data->search->dia;
    $mes = $data->search->mes;
    $ano = $data->search->ano;

    $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
            ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
            Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
            ClasL_tipo,
            CASE 
                WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                ELSE 'Registrado'
            END AS EXP_estatus,
            CASE 
                WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                ELSE ''
            END as EXP_estatusFac
            FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
                left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            WHERE Unidad.Uni_clave = $unidad and EXP_cancelado = 0 ";

    if ($dia) {
        
        $fechaini = date('Y-m-d');

        $sql .= " AND Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechaini 23:59:59'";

    }elseif ($mes) {
        

        $fechaini = date('Y-m-01');
        $fechafin = date('Y-m-d');


        $sql .= " AND Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";

    }elseif ($ano) {
        
        $fechaini = date('Y-01-01');
        $fechafin = date('Y-m-d');

        $sql .= " AND Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";

    }else{

        if ($folio != '') {
            
            $criterio1 .= " AND Expediente.Exp_folio = '$folio'";

        }else{
            $criterio1 = "";
        }

        if ($fechaini != '' && $fechafin != '') {

           $criterio2 .= " AND Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";
           
        }else{
            $criterio2 = "";
        }

        if ($lesionado != '') {


            $criterio3 .= " AND Exp_completo like '%$lesionado%' ";

        }else{
            $criterio3 = "";
        }

        if ($poliza != '') {


            $criterio4 .= " AND Exp_poliza = $poliza ";

        }else{

            $criterio4 = "";

        }

        if ($reporte != '') {


            $criterio5 .= " AND EXP_reporte = $reporte ";

        }else{
            $criterio5 = "";
        }


        if ($siniestro != '') {

            $criterio6 .= " AND Exp_siniestro = $siniestro ";

        }else{
            $criterio6 = "";
        }


        $sql .= $criterio1 . $criterio2 . $criterio3 . $criterio4 . $criterio5 . $criterio6;
        
        if($criterio1 == '' && $criterio2 == '' && $criterio3 == '' && $criterio4 == '' && $criterio5 == '' && $criterio6 == ''){

            $sql .= " ORDER BY Exp_fecreg DESC LIMIT 0,100";
        }

    }
    
    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'buscaExpedientesCategoria'){
    
    //Obtenemos los datos que mandamos de angular
    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);
    $conexion = conectarMySQL();

    $categoria = $data->search->categoria;
    
    //Obtenemos los valores de usuario y contraseña 

    $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
            ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
            Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
            ClasL_tipo,
            CASE 
                WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                ELSE 'Registrado'
            END AS EXP_estatus,
            CASE 
                WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                ELSE ''
            END as EXP_estatusFac,
            CASE 
                     WHEN DATEDIFF(now(),Exp_fecreg) >=  0 AND DATEDIFF(now(),Exp_fecreg) <= 15 THEN '0-15'
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 16 AND DATEDIFF(now(),Exp_fecreg) <= 30 THEN '16-30'    
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 31 AND DATEDIFF(now(),Exp_fecreg) <= 60 THEN '31-60'    
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 61 AND DATEDIFF(now(),Exp_fecreg) <= 90 THEN '61-90'    
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 91 AND DATEDIFF(now(),Exp_fecreg) <= 120 THEN '91-120' 
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 121 AND DATEDIFF(now(),Exp_fecreg) <= 150 THEN '121-150'     
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 151 AND DATEDIFF(now(),Exp_fecreg) <= 180 THEN '151-180'     
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 181 AND DATEDIFF(now(),Exp_fecreg) <= 360 THEN '181-360'     
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 360 THEN '+ de 360' END as Periodo
            FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                left join Documento on Documento.DOC_folio = Expediente.Exp_folio 
                left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            WHERE Expediente.Exp_folio not in (SELECT DOC_folio FROM Documento WHERE DOC_etapa = 1) and Exp_cancelado = 0 and Exp_fecreg >= '2014-01-01'and  Unidad.Uni_clave = $unidad  ";

    if($categoria == '0-15'){
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >=  0 AND DATEDIFF(now(),Exp_fecreg) <= 15';
    }elseif ($categoria == '16-30') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 16 AND DATEDIFF(now(),Exp_fecreg) <= 30';
    }elseif ($categoria == '31-60') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 31 AND DATEDIFF(now(),Exp_fecreg) <= 60';
    }elseif ($categoria == '61-90') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 61 AND DATEDIFF(now(),Exp_fecreg) <= 90';
    }elseif ($categoria == '91-120') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 91 AND DATEDIFF(now(),Exp_fecreg) <= 120';
    }elseif ($categoria == '121-150') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 121 AND DATEDIFF(now(),Exp_fecreg) <= 150';
    }elseif ($categoria == '151-180') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 151 AND DATEDIFF(now(),Exp_fecreg) <= 180';
    }elseif ($categoria == '181-360') {
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 181 AND DATEDIFF(now(),Exp_fecreg) <= 360';
    }else{
        $sql .= 'and DATEDIFF(now(),Exp_fecreg) >= 360';
    }


    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'buscaExpedientesXUnidad'){
    
    //Obtenemos los datos que mandamos de angular
    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);
    $conexion = conectarMySQL();
        
    //Obtenemos los valores 
    $mes = $data->search->mes;

    $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
            ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
            Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
            ClasL_tipo,
            CASE 
                WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                ELSE 'Registrado'
            END AS EXP_estatus,
            CASE 
                WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                ELSE ''
            END as EXP_estatusFac
            FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null)
                left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            WHERE Unidad.Uni_clave = $unidad and EXP_cancelado = 0 ";

    if ($mes == 0) {
        $fechaini = date('Y-m-01');
        $fechafin = date('Y-m-d');
    }else{
        $fechaini = primerdiadelmes($mes);
        $fechafin = ultimodiadelmes($mes);
    }

    // $fechaini = date('Y-m-01');
    // $fechafin = date('Y-m-d');


    $sql .= " AND Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'"; 

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'correo'){

    $conexion = conectarMySQL();

    $sql = "SELECT * FROM UsuarioInfoWeb";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;
    
}

if($funcion == 'detalleExpediente'){
    
    //expediente = folio 
    $expediente = $_REQUEST['expediente'];

    $conexion = conectarMySQL();

    $sql = "SELECT  Expediente.Exp_folio as folio,Exp_completo as lesionado, UNI_nombreMV as unidad, 
                    ExpedienteInfo.Exp_poliza as poliza, ExpedienteInfo.Exp_siniestro as siniestro ,ExpedienteInfo.EXP_reporte as reporte,
                    DATE(Exp_fecreg) as fechaatencion , Expediente.EXP_edad as edad, Expediente.EXP_sexo as sexo,EXP_fechaCaptura as fechacaptura,
                    ExpedienteInfo.EXP_fechaExpedicion as fechaexpedicion, ExpedienteInfo.EXP_orden as orden,  RIE_nombre as riesgo, 
                    POS_nombre as posicion, EXP_ajustador as ajustador, EXP_obsAjustador as observaciones, TLE_nombre as lesion,
                    EXP_diagnostico as descripcion, FAC_folioFiscal as sat, CONCAT(FAC_serie,FAC_folio) as foliointerno,
                    FAC_fecha as fechafactura, FAC_importe as importe, FAC_iva as iva, FAC_total as total, Cia_rfc as rfc,
                    Cia_nombrecorto as empresa, LesE_clave as clasificacion
            FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
                left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                left join ExpedienteInfo on ExpedienteInfo.EXP_folio = Expediente.EXP_folio
                left join Posicion on Posicion.POS_clave = ExpedienteInfo.POS_claveint
                left join LesionMV on LesionMV.LES_clave = ExpedienteInfo.LES_empresa
                left join TipoLesion on TipoLesion.TLE_claveint = LesionMV.TLE_claveint 
            WHERE Unidad.Uni_clave = $unidad and Expediente.EXP_folio = '$expediente' and EXP_cancelado = 0 ";

    $result = $conexion->query($sql);
    $datos = $result->fetch(PDO::FETCH_OBJ);

    $documentos = array();

    $sqlEtapas ="SELECT DOC_clave,DOC_folio,DOC_etapa,DOC_entrega,UNI_clave,DATE(DOC_fecha) as DOC_fecha ,DATE(DOC_fechaReg) as DOC_fechaReg ,DATE(DOC_fechaImp) as DOC_fechaImp FROM Documento
                WHERE DOC_folio = '$expediente' ORDER BY DOC_etapa,DOC_entrega";

    foreach ($conexion->query($sqlEtapas) as $item) {

        $dato = array();

        $clave = $item['DOC_clave'];
        $dato['documento'] = $item['DOC_clave'];
        $dato['etapa'] = $item['DOC_etapa'];
        $dato['entrega'] = $item['DOC_entrega'];
        $dato['unidad'] = $item['UNI_clave'];
        $dato['original'] = $item['DOC_fecha'];
        $dato['originalRegistro'] = $item['DOC_fechaReg'];
        // fecha en que se subio a esta tabla
        $dato['fechasubio'] = $item['DOC_fechaImp'];

        //Obtener pagos de ese folio y etapa
        $queryPagos="SELECT *
        FROM PagoUnidad
        where DOC_clave = $clave";

        $result = $conexion->query($queryPagos);
        $pagos = $result->fetchAll(PDO::FETCH_OBJ);
        $dato['pagos'] = $pagos;

        array_push($documentos,$dato);

    }          

    $sqlTick = "SELECT TSeg_clave as clave, TSeg_etapa as etapa, Tseg_fechaactualizacion as fecha, TSub_nombre as subcategoria, TCat_nombre as categoria, TStatus_nombre as estatus, TSeg_obs as observa, Usu_nombre 
                FROM TicketSeguimiento 
                LEFT JOIN TicketSubcat ON TicketSubcat.TSub_clave = TicketSeguimiento.TSub_clave
                LEFT JOIN TicketCat ON TicketCat.TCat_clave = TicketSeguimiento.TCat_clave
                LEFT JOIN TicketStatus ON TicketStatus.TStatus_clave = TicketSeguimiento.TStatus_clave
                LEFT JOIN Usuario on Usuario.Usu_login=TicketSeguimiento.Usu_registro
                WHERE Exp_folio = '$expediente' and TCat_clave <> 4 ";

    $tickets = array();

    //obtenemos datos para la busqueda de la factura
    foreach ($conexion->query($sqlTick) as $item) {

        $dato = array();

        $id = $item['clave'];
        $dato['id'] = $item['clave'];
        $dato['etapa'] = $item['etapa'];
        $dato['fecha'] = $item['fecha'];
        $dato['subcategoria'] = $item['subcategoria'];
        $dato['categoria'] = $item['categoria'];
        $dato['estatus'] = $item['estatus'];
        $dato['observaciones'] = $item['observa'];
        $dato['usuario'] = $item['Usu_nombre'];

        //Obtener comunicacion
        $querycomunicacion="SELECT TC_descripcion as Descripcion, TC_fechareg as Fecha, Usuario.Usu_nombre as Usuario
        FROM TicketComunicacion
        inner join Usuario on Usuario.Usu_login=TicketComunicacion.Usu_registro
        where TSeg_clave=$id And Exp_folio='$expediente'";

        $result = $conexion->query($querycomunicacion);
        $comunicacion = $result->fetchAll(PDO::FETCH_OBJ);
        $dato['comunicacion'] = $comunicacion;

        //Obtener notas
        $querynotas="SELECT TN_descripcion as Descripcion, TN_fechareg as Fecha, Usuario.Usu_nombre as Usuario
        FROM TicketNotas 
        inner join Usuario on Usuario.Usu_login=TicketNotas.Usu_registro
        where TSeg_clave=$id And Exp_folio='$expediente'";

        $result = $conexion->query($querynotas);
        $notas = $result->fetchAll(PDO::FETCH_OBJ);
        $dato['notas'] = $notas;

        array_push($tickets,$dato);

    }


    $sqlAut = "SELECT * FROM AutorizacionMedica
            INNER JOIN Unidad ON Unidad.Uni_clave = AutorizacionMedica.UNI_claveint
            INNER JOIN Compania ON Compania.Cia_clave =  AutorizacionMedica.EMP_claveint
            where AUM_folioMV = '$expediente'";

    $result = $conexion->query($sqlAut);
    $autorizacion = $result->fetch();

    foreach ($conexion->query($sqlAut) as $item) {
        $numeroautorizacion = $item['AUM_clave'];
    }
    
    $sqlMov = "SELECT * FROM MovimientoAut 
            INNER JOIN TipoMovimiento ON MovimientoAut.TIM_claveint = TipoMovimiento.TIM_claveint 
            INNER JOIN Usuario ON MovimientoAut.USU_registro = Usuario.USU_claveMV
            WHERE AUM_clave = '$numeroautorizacion' ";

    $result = $conexion->query($sqlMov);
    $movimientos = $result->fetchAll(PDO::FETCH_OBJ);


    $sqlHos = "SELECT * FROM Hospitalario where Exp_folio = '$expediente'";
    $result = $conexion->query($sqlHos);
    $hospitalario = $result->fetch();

    $resultado['detalle'] = $datos;
    $resultado['tickets'] = $tickets;
    $resultado['autorizacion'] = $autorizacion;
    $resultado['movimientos'] = $movimientos;
    $resultado['hospitalario'] = $hospitalario;
    $resultado['documentos'] = $documentos;
    
    echo json_encode($resultado);

    $conexion = null;

}

if($funcion == 'expedientesdia'){

    $conexion = conectarMySQL();
    
    $fechaini = date('Y-m-d');

    $sql = "SELECT count(Exp_folio) as Folios FROM Expediente
            WHERE Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechaini 23:59:59' and UNI_clave = $unidad  and EXP_cancelado = 0";

    $result = $conexion->query($sql);

    $datos = $result->fetch();
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'expedientesmes'){

    $conexion = conectarMySQL();
    
    $fechaini = date('Y-m-01');
    $fechafin = date('Y-m-d');

    $sql = "SELECT count(Exp_folio) as Folios FROM Expediente
    WHERE Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and UNI_clave = $unidad  and EXP_cancelado = 0";

    $result = $conexion->query($sql);

    $datos = $result->fetch();
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'expedientesano'){

    $conexion = conectarMySQL();
    
    $fechaini = date('Y-m-d', strtotime('-11 month'));
    $fechafin = date('Y-m-d');

    $sql = "SELECT count(Exp_folio) as Folios FROM Expediente
    WHERE Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and UNI_clave = $unidad  and EXP_cancelado = 0";
    
    $result = $conexion->query($sql);

    $datos = $result->fetch();
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'ExpedientesXfecha'){

    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);
    $conexion = conectarMySQL();

    //Obtenemos los valores de usuario y contraseña 
    $mes = $data->search->mes;
    $ano = $data->search->ano;

    $fechaini = primerdiade($mes,$ano);
    $fechafin = ultimodiade($mes,$ano);

    $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
            ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
            Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
            ClasL_tipo,
            CASE 
                WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                ELSE 'Registrado'
            END AS EXP_estatus,
            CASE 
                WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                ELSE ''
            END as EXP_estatusFac
            FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
                left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            WHERE Unidad.Uni_clave = $unidad and EXP_cancelado = 0 and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' ";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'estadisticaAtencionesXmes'){

    $conexion = conectarMySQL();
    $ano = $_REQUEST['ano'];

    $fechaini = date('Y-m-d', strtotime('-11 month'));
    $fechafin = date('Y-m-d');

    $sql = "SELECT MONTH(Exp_fecreg) as clave, YEAR(Exp_fecreg) as ano,
            CASE WHEN MONTH(Exp_fecreg) = 1 THEN concat('Enero ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 2 THEN concat('Febrero ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 3 THEN concat('Marzo ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 4 THEN concat('Abril ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 5 THEN concat('Mayo ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 6 THEN concat('Junio ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 7 THEN concat('Julio ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 8 THEN concat('Agosto ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 9 THEN concat('Sseptiembre ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 10 THEN concat('Octubre ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 11 THEN concat('Noviembre ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 12 THEN concat('Diciembre ',YEAR(Exp_fecreg))
            ELSE 'esto no es un mes' END AS MES,
            count(Exp_folio) as Cantidad  FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
    WHERE Unidad.Uni_clave = $unidad and Exp_fecreg between '$fechaini 00:00:00' and '$fechafin 23:59:59'  and EXP_cancelado = 0 group by MONTH(Exp_fecreg) order by YEAR(Exp_fecreg),MONTH(Exp_fecreg)";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'estadisticaAtencionesXsemana'){

    $conexion = conectarMySQL();
    $mes = $_REQUEST['mes'];
    $ano = $_REQUEST['ano'];

    $mesactual = date('m');
    $anoactual = date('Y');

    if ($mesactual == $mes && $anoactual == $ano) {

        $fechaini = date('Y-m-01');
        $fechafin = date('Y-m-d');

    }else{

        $fechaini = primerdiade($mes,$ano);
        $fechafin = ultimodiade($mes,$ano);
    }


    $sql = "SELECT concat('Semana ', WEEK(Exp_fecreg) ) as Semana,
            count(Exp_folio) as Cantidad  FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
    WHERE Unidad.Uni_clave = $unidad and Exp_fecreg between '$fechaini 00:00:00' and '$fechafin 23:59:59'  and EXP_cancelado = 0 group by WEEK(Exp_fecreg)";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    //echo $sql;
    $conexion = null;

}

if($funcion == 'estadisticaAtencionesXciudad'){

    $conexion = conectarMySQL();
    
    $fechaini = date('Y-01-01');
    $fechafin = date('Y-m-d');


    $sql = "SELECT EST_nombre  as Zona , count(EXP_folio) as Cantidad  FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Localidad on Unidad.LOC_claveint = Localidad.LOC_claveint
                inner join Estado on Estado.EST_claveint = Localidad.EST_claveint
            WHERE Unidad.Uni_clave = $unidad and Exp_fecreg between '$fechaini 00:00:00' and '$fechafin 23:59:59'  and EXP_cancelado = 0 group by zona order by Cantidad DESC limit 5";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'estadisticaAtencionesXunidad'){

    $conexion = conectarMySQL();
    
    $fechaini = date('Y-01-01');
    $fechafin = date('Y-m-d');


    $sql = "SELECT UNI_nombreMV as Unidad, count(EXP_folio) as Cantidad  FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            WHERE Unidad.Uni_clave = $unidad and Exp_fecreg between '$fechaini 00:00:00' and '$fechafin 23:59:59'  and EXP_cancelado = 0 group by Uni_nombre order by Cantidad DESC limit 5";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'estadisticaAtencionesXunidadXmes'){
    
    $conexion = conectarMySQL();
    
    $mes = $_REQUEST['mes'];

    if ($mes == 0){
        $fechaini = date('Y-m-01');
        $fechafin = date('Y-m-d');
    }else{
        $fechaini = primerdiadelmes($mes);
        $fechafin = ultimodiadelmes($mes);
    }

    
    $sql = "SELECT Unidad.UNI_clave as clave, UNI_nombreMV  as Unidad, count(EXP_folio) as Cantidad, '#0D8ECF' as color FROM Expediente
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            WHERE Unidad.Uni_clave = $unidad and Exp_fecreg between '$fechaini 00:00:00' and '$fechafin 23:59:59'  and EXP_cancelado = 0 group by Uni_nombre order by Cantidad DESC limit 20";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'estadocuenta'){

    //Obtenemos los datos que mandamos de angular
    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);

    $resultado = array();

    $ano1 = $data->inicio->ano;   
    $mes1 = $data->inicio->mes;

    $ano2 = $data->fin->ano;   
    $mes2 = $data->fin->mes;

    $fechaini = primerdiade($mes1, $ano1);
    $fechafin = ultimodiade($mes2, $ano2);

    $conexion = conectarMySQL();

    // cuenta el total de atenciones
    $sqlTot = "SELECT count(Exp_folio) as folios FROM Expediente where Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";
    // cuenta el total de atenciones correctas
    $sqlok = "SELECT count(Exp_folio) as folios FROM Expediente where Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Exp_cancelado = 0";
    // cuenta los cancelados
    $sqlCan = "SELECT count(Exp_folio) as folios FROM Expediente where Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Exp_cancelado = 1";
    // cuenta los folios sin documentacion
    $sqlSnDoc ="SELECT count(Exp_folio) as folios FROM Expediente 
                WHERE Exp_folio not in (SELECT DOC_folio FROM Documento WHERE DOC_etapa = 1) and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Exp_cancelado = 0 and Expediente.Uni_clave = $unidad";
    
    // cuenta los folios con documentos o en proceso
    $sqlDoc ="SELECT count(Exp_folio) as folios FROM Expediente 
            inner join Documento on Documento.DOC_folio = Expediente.Exp_folio
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            WHERE Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Exp_cancelado = 0 and Expediente.Uni_clave = $unidad and PAU_fecharel is null and DOC_etapa = 1";
    
    // cuenta los folios que tienen tickets sin resolucion final
    $sqlCnT = "SELECT count(Expediente.Exp_folio) as folios FROM Expediente 
                INNER JOIN TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                WHERE Expediente.Uni_clave = $unidad and Exp_cancelado = 0 and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Tstatus_clave <> 7 and TCat_clave <> 4";
    // Cuenta folios que estan pagaos
    $sqlPag = "SELECT count(Expediente.Exp_folio) as folios FROM Expediente 
                INNER JOIN Documento on Documento.DOC_folio = Expediente.Exp_folio
                INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                WHERE PagoUnidad.Uni_clave = $unidad and Exp_cancelado = 0 and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 1";

    $sqlSubsecuencia = "SELECT count(DOC_folio) as folios FROM Documento where DOC_fecha between '$fechaini 00:00:00' and '$fechafin 23:59:59' and UNI_clave = $unidad and DOC_etapa = 2";  
    $sqlRehabilitacion = "SELECT count(DOC_folio) as folios FROM Documento where DOC_fecha between '$fechaini 00:00:00' and '$fechafin 23:59:59' and UNI_clave = $unidad and DOC_etapa = 3";
    
    $sqlTotalE1 = "SELECT sum(PAU_pago) as total FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    WHERE PagoUnidad.Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 1 and Exp_cancelado = 0";
    
    $sqlTotalE2 = "SELECT sum(PAU_pago) as total FROM Documento
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    WHERE PagoUnidad.Uni_clave = $unidad and DOC_fecha BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 2 ";
    
    $sqlTotalE3 = "SELECT sum(PAU_pago) as total FROM Documento 
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    WHERE PagoUnidad.Uni_clave = $unidad and DOC_fecha BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 3 ";


    $sqlFechaPago = "SELECT date_format(PAU_fechaRel,'%Y/%m/%d') as fecha FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    WHERE PagoUnidad.Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' ORDER BY PAU_fechaRel DESC LIMIT 1";

    $result = $conexion->query($sqlTot);
    $total = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlok);
    $buenos = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlCan);
    $cancelados = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlSnDoc);
    $sindocumentos = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlDoc);
    $proceso = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlCnT);
    $problema = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlPag);
    $pagados = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlSubsecuencia);
    $etapa2 = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlRehabilitacion);
    $etapa3 = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlTotalE1);
    $pagosEt1 = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlTotalE2);
    $pagosEt2 = $result->fetch(PDO::FETCH_OBJ);

    $result = $conexion->query($sqlTotalE3);
    $pagosEt3 = $result->fetch(PDO::FETCH_OBJ);


    $result = $conexion->query($sqlFechaPago);
    $fechaPago = $result->fetch(PDO::FETCH_OBJ);


    $resultado['total'] = $total;
    $resultado['buenos'] = $buenos;
    $resultado['cancelados'] = $cancelados;
    $resultado['sindocumentos'] = $sindocumentos;
    $resultado['proceso'] = $proceso;
    $resultado['problema'] = $problema;
    $resultado['pagados'] = $pagados;
    $resultado['etapa2'] = $etapa2;
    $resultado['etapa3'] = $etapa3;
    $resultado['pagosEt1'] = $pagosEt1;
    $resultado['pagosEt2'] = $pagosEt2;
    $resultado['pagosEt3'] = $pagosEt3;
    $resultado['fechaPago'] = $fechaPago;
    
    echo json_encode($resultado);

    $conexion = null;

}

if($funcion == 'estadisticaNoutlizada'){

    $conexion = conectarMySQL();

    $sql = "SELECT Uni_nombre  as Unidad   FROM Unidad
            WHERE Uni_clave NOT IN( SELECT Uni_clave from Expediente where Cia_clave = $cliente) and Unidad.Uni_clave = $unidad and EXP_cancelado = 0";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if ($funcion == 'enviacorreo') {

    include_once('mail/nomad_mimemail.inc.php');

    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $datos = json_decode($postdata);

    $tema = $datos->tema;
    $folio = $datos->folio;
    $respuesta = $datos->respuesta;
    $copias = $datos->copias;
    $asunto = $datos->asunto;
    $comentarios = $datos->comentarios;
    $empresa = $datos->cliente;
    $lesionado = $datos->lesionado;

    $html="<style type='text/css'>
            .small font {
                color: #224B99;
            }
            .small font strong {
                font-size: 9px;
            }
            .clase1 {
                color: #224B99;
                font-size: 9px;
            }
            .clase1 font {
                font-family: Verdana, Geneva, sans-serif;
            }
            .clase1 font {
                font-size: 9px;
            }
            .clase1 font {
                font-size: small;
            }
            .clase1 font {
                font-weight: bold;
            }
            .clase2 {
                color: #224B99;
            }
            .clase3 {
                color: #224B99;
                font-size: 9px;
            }
            }
            </style>";


    $html.="<h3 class='clase2'><img src='file:../img/logomv.png' width='195' height='93' />Solicitud por parte de $empresa<span class=''></span></h3>
            <hr />
            <p><br />
      
          <span class='clase2'>Folio:<strong>$folio</strong><br />
          Nombre del Paciente:<strong>$lesionado</strong><br />
          Cliente:<strong>$empresa</strong><br /><br>
          Comentarios:<strong>$comentarios</strong></span>
        <p>
          
          <hr />          
          
          <FONT SIZE=2 class='clase3'>Te recordamos que la respuesta se debe de entregar en no más de dos dias hábiles</font><br />

        </p>";

    if ($copias) {

        $html.= "
            <p>
          
              <hr />          
              <h3>¡¡ Importante ¡¡</h3>
              <FONT SIZE=2 class='clase3'>Mandar respuesta con copia al/los siguiente(s) correo(s) </font><br />

            </p>
            <ul>
        ";

        foreach ($copias as $copia) {
            
            $html.= "<li>". $copia->value . "</li>";
        }

        $html.="</ul>";
    }

    $mimemail = new nomad_mimemail();
    $mimemail->set_from($respuesta);
    // $mimemail->set_to("checo_2k2@hotmail.com");
    $mimemail->set_to("salcala@medicavial.com");
    $mimemail->add_cc("agutierrez@medicavial.com.mx");


    $mimemail->set_subject("$tema - $asunto");        
    $mimemail->set_html($html);


    if ($mimemail->send()){
                 $correo = "si";
                 $respuesta = array('respuesta' => 'Tu correo se envio correctamente');
    }
    else {
                 $correo = "no";
                 $respuesta = array('respuesta' => 'Tu correo no se logro enviar intentalo nuevamente');
    }


    echo json_encode($respuesta);

}

if($funcion == 'estadisticaTickets'){

    $conexion = conectarMySQL();

    $sql = "SELECT count(Tseg_clave) as numero, TCat_nombre as categoria from TicketSeguimiento
            inner join TicketCat on TicketCat.TCat_clave = TicketSeguimiento.TCat_clave
            where TStatus_clave = 1 and Uni_clave = $unidad group by TCat_nombre";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);

    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'listadosestadocuenta'){


    //Obtenemos los datos que mandamos de angular
    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);

    $mes1 = $data->search->mes1;
    $ano1 = $data->search->ano1;
    $mes2 = $data->search->mes2;
    $ano2 = $data->search->ano2;
    $categoria = $data->search->categoria;

    $data = json_decode($postdata);

    $fechaini = primerdiade($mes1, $ano1);
    $fechafin = ultimodiade($mes2, $ano2);

    $conexion = conectarMySQL();

    $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
            ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
            Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
            ClasL_tipo,
            CASE
                WHEN Exp_cancelado = 1 THEN 'Cancelado' 
                WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                ELSE 'Registrado'
            END AS EXP_estatus,
            CASE 
                WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                ELSE ''
            END as EXP_estatusFac
            FROM Expediente ";

    if ($categoria == 'registrados') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            where Unidad.Uni_clave = $unidad and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";
        
    }elseif ($categoria == 'cancelados') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            where Unidad.Uni_clave = $unidad and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Expediente.Exp_cancelado = 1";
        
    }elseif ($categoria == 'expedientes') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            where Unidad.Uni_clave = $unidad and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Expediente.Exp_cancelado = 0";
        
    }elseif ($categoria == 'sindocumentacion') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            where Expediente.Exp_folio not in (SELECT DOC_folio FROM Documento WHERE DOC_etapa = 1) and Unidad.Uni_clave = $unidad and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Expediente.Exp_cancelado = 0";
        
    }elseif ($categoria == 'problema') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            inner join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            where Unidad.Uni_clave = $unidad and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Expediente.Exp_cancelado = 0 and Tstatus_clave <> 7 and TCat_clave <> 4";
        
    }elseif ($categoria == 'pagados') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            inner join Documento on Documento.DOC_folio = Expediente.Exp_folio
            inner join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            where Expediente.Uni_clave = $unidad and Exp_cancelado = 0 and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 1";
        
    }elseif ($categoria == 'proceso') {

        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio and (DOC_etapa = 1 or DOC_etapa is null) 
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            WHERE Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and Exp_cancelado = 0 and Expediente.Uni_clave = $unidad and DOC_etapa = 1 and PAU_fecharel is null";
    
    }elseif ($categoria == 'pagoset1') {

        $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
                    ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
                    Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
                    ClasL_tipo,
                    CASE 
                        WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                        WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                        WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                        WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                        ELSE 'Registrado'
                    END AS EXP_estatus,
                    CASE 
                        WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                        ELSE ''
                    END as EXP_estatusFac 
                    FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN Unidad ON Unidad.Uni_clave = Expediente.Uni_clave
                    INNER JOIN Compania ON Compania.Cia_clave = Expediente.Cia_clave
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                    left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                    left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                    left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                    WHERE Expediente.Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 1";

    }elseif ($categoria == 'pagoset2') {

        $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
                    ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
                    Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
                    ClasL_tipo,
                    CASE 
                        WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                        WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                        WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                        WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                        ELSE 'Registrado'
                    END AS EXP_estatus,
                    CASE 
                        WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                        ELSE ''
                    END as EXP_estatusFac,
                    DOC_entrega as entrega
                    FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN Unidad ON Unidad.Uni_clave = Expediente.Uni_clave
                    INNER JOIN Compania ON Compania.Cia_clave = Expediente.Cia_clave
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                    left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                    left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                    left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                WHERE PagoUnidad.Uni_clave = $unidad and DOC_fecha BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 2";

    }elseif ($categoria == 'pagoset3') {

        $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
                    ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
                    Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
                    ClasL_tipo,
                    CASE 
                        WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                        WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                        WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                        WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                        ELSE 'Registrado'
                    END AS EXP_estatus,
                    CASE 
                        WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                        ELSE ''
                    END as EXP_estatusFac,
                    DOC_entrega as entrega
                    FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN Unidad ON Unidad.Uni_clave = Expediente.Uni_clave
                    INNER JOIN Compania ON Compania.Cia_clave = Expediente.Cia_clave
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                    left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                    left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                    left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                WHERE PagoUnidad.Uni_clave = $unidad and DOC_fecha BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 3";

    }elseif ($categoria == 'pagoset') {

        $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
                    ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
                    Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
                    ClasL_tipo,
                    CASE 
                        WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                        WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                        WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                        WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                        ELSE 'Registrado'
                    END AS EXP_estatus,
                    CASE 
                        WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                        ELSE ''
                    END as EXP_estatusFac
                    FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN Unidad ON Unidad.Uni_clave = Expediente.Uni_clave
                    INNER JOIN Compania ON Compania.Cia_clave = Expediente.Cia_clave
                    INNER JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                    left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                    left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                    left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                    WHERE PagoUnidad.Uni_clave = $unidad and Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";

    }elseif ($categoria == 'etapa2') {

        $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
                    ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
                    Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
                    ClasL_tipo,
                    CASE 
                        WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                        WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                        WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                        WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                        ELSE 'Registrado'
                    END AS EXP_estatus,
                    CASE 
                        WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                        ELSE ''
                    END as EXP_estatusFac,
                    DOC_entrega as entrega
                    FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN Unidad ON Unidad.Uni_clave = Expediente.Uni_clave
                    INNER JOIN Compania ON Compania.Cia_clave = Expediente.Cia_clave
                    left JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                    left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                    left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                    left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                WHERE Documento.Uni_clave = $unidad and DOC_fecha BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 2";

    }elseif ($categoria == 'etapa3') {

        $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
                    ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
                    Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
                    ClasL_tipo,
                    CASE 
                        WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                        WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                        WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                        WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                        ELSE 'Registrado'
                    END AS EXP_estatus,
                    CASE 
                        WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                        ELSE ''
                    END as EXP_estatusFac,
                    DOC_entrega as entrega
                    FROM Documento
                    INNER JOIN Expediente ON Expediente.Exp_folio = Documento.DOC_folio 
                    INNER JOIN Unidad ON Unidad.Uni_clave = Expediente.Uni_clave
                    INNER JOIN Compania ON Compania.Cia_clave = Expediente.Cia_clave
                    left JOIN PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                    left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                    left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                    left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                    left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
                WHERE Documento.Uni_clave = $unidad and DOC_fecha BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59' and DOC_etapa = 3";

    }else{
        $sql .= "
            inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
            inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
            left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
            left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
            left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
            left join Documento on Documento.DOC_folio = Expediente.Exp_folio
            left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave and (DOC_etapa = 1 or DOC_etapa is null) 
            left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            where Unidad.Uni_clave = $unidad and Expediente.Exp_fecreg BETWEEN '$fechaini 00:00:00' and '$fechafin 23:59:59'";
    }

    $result = $conexion->query($sql);
    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'login'){
    
    //Obtenemos los datos que mandamos de angular
    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);
    $conexion = conectarMySQL();
        
    //Obtenemos los valores de usuario y contraseña 
    $user = trim($data->user);
    $psw = trim($data->psw);
    
    $sql = "SELECT * FROM Usuario
            LEFT JOIN Unidad ON Unidad.Uni_clave = Usuario.Uni_clave
            WHERE Usu_login = '$user' and Usu_pwd = '" . md5($psw) . "'";

    $result = $conexion->query($sql);
    $numero = $result->rowCount();
    
    if ($numero>0){

        $datos = $result->fetch(PDO::FETCH_OBJ);
        
    }else{

        $datos = array('respuesta' => 'El Usuario o contraseña son inorrectos');
    }
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'periodos'){

    $conexion = conectarMySQL();

    $sql = "SELECT date_format(exp_fecreg, '%m') as mes, YEAR(exp_fecreg) as ano ,
            CASE WHEN MONTH(Exp_fecreg) = 1 THEN concat('Enero ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 2 THEN concat('Febrero ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 3 THEN concat('Marzo ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 4 THEN concat('Abril ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 5 THEN concat('Mayo ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 6 THEN concat('Junio ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 7 THEN concat('Julio ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 8 THEN concat('Agosto ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 9 THEN concat('Septiembre ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 10 THEN concat('Octubre ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 11 THEN concat('Noviembre ',YEAR(Exp_fecreg))
            WHEN MONTH(Exp_fecreg) = 12 THEN concat('Diciembre ',YEAR(Exp_fecreg))
            ELSE 'esto no es un mes' END AS periodo
            FROM Expediente WHERE Uni_clave = $unidad group by MONTH(exp_fecreg), YEAR(exp_fecreg) order by YEAR(Exp_fecreg) DESC , MONTH(Exp_fecreg) DESC";
    
    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'periodossindocumentacion'){

    $conexion = conectarMySQL();

    $sql = "SELECT count(Exp_folio) as folios,
                CASE 
                     WHEN DATEDIFF(now(),Exp_fecreg) >=  0 AND DATEDIFF(now(),Exp_fecreg) <= 15 THEN '0-15'
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 16 AND DATEDIFF(now(),Exp_fecreg) <= 30 THEN '16-30'    
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 31 AND DATEDIFF(now(),Exp_fecreg) <= 60 THEN '31-60'    
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 61 AND DATEDIFF(now(),Exp_fecreg) <= 90 THEN '61-90'    
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 91 AND DATEDIFF(now(),Exp_fecreg) <= 120 THEN '91-120' 
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 121 AND DATEDIFF(now(),Exp_fecreg) <= 150 THEN '121-150'     
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 151 AND DATEDIFF(now(),Exp_fecreg) <= 180 THEN '151-180'     
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 181 AND DATEDIFF(now(),Exp_fecreg) <= 360 THEN '181-360'     
                     WHEN DATEDIFF(now(),Exp_fecreg) >= 360 THEN '+ de 360' END as Periodo  
            From Expediente where Exp_folio not in (SELECT DOC_folio FROM Documento WHERE DOC_etapa = 1) and Uni_clave = $unidad and Exp_cancelado = 0 and Exp_fecreg >= '2014-01-01' group by Periodo order by Exp_fecreg DESC ";
    
    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'sinDocumentacion'){

    $conexion = conectarMySQL();
    $ano = $_REQUEST['ano'];

    $fechaini = date('Y-m-d', strtotime('-11 month'));
    $fechafin = date('Y-m-d');

    $sql = "SELECT Expediente.Exp_folio, UNI_nombreMV, ExpedienteInfo.Exp_poliza,
            ExpedienteInfo.Exp_siniestro,ExpedienteInfo.EXP_reporte,
            Exp_completo, Exp_fecreg, Cia_nombrecorto , RIE_nombre,  
            ClasL_tipo,
            CASE 
                WHEN FAC_fecha IS NOT NULL THEN 'Listo'
                WHEN ClasL_fechareg IS NOT NULL THEN 'Clasificado' 
                WHEN TSeg_fechaactualizacion IS NOT NULL and TCat_clave = 1 THEN 'Incompleto'
                WHEN DOC_fecha IS NOT NULL THEN 'Recibido'
                ELSE 'Registrado'
            END AS EXP_estatus,
            CASE 
                WHEN PAU_fechaRel IS NOT NULL THEN 'Pagado'
                ELSE ''
            END as EXP_estatusFac
            FROM Expediente
                inner join Unidad on Unidad.Uni_clave = Expediente.Uni_clave 
                inner join Compania on Compania.Cia_clave = Expediente.Cia_clave
                left join RiesgoAfectado on RiesgoAfectado.RIE_clave = Expediente.RIE_clave
                left join ClasificacionLes ON ClasificacionLes.Exp_folio = Expediente.Exp_folio
                left join ExpedienteInfo on ExpedienteInfo.Exp_folio = Expediente.Exp_folio
                left join Documento on Documento.DOC_folio = Expediente.Exp_folio
                left join PagoUnidad on PagoUnidad.DOC_clave = Documento.DOC_clave
                left join TicketSeguimiento on TicketSeguimiento.Exp_folio = Expediente.Exp_folio
            where Documento.DOC_fecha is null and Expediente.Uni_clave = $unidad and Expediente.Exp_fecreg between '$fechaini 00:00:00' and '$fechafin 23:59:59' limit 100 order by Exp_fecreg DESC";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'tickets'){

    $conexion = conectarMySQL();

    $sql = "SELECT TSeg_clave as Folio_Interno, TicketSeguimiento.Exp_folio as Folio_Web, TSeg_etapa as Etapa, TCat_nombre as Categoria, TSub_nombre as Subcategoria,
                TStatus_nombre as Status, TSeg_obs as Observaciones, Uni_nombre as Unidad, TSeg_Asignado as Asignado, TSeg_fechareg as Registro,
                Usu_nombre as Usuario_Registro, Tseg_fechaactualizacion as Ultima_Actualizacion,Concat(Exp_nombre,' ', Exp_paterno,' ', Exp_materno) As Lesionado,Cia_nombrecorto as Cliente,
                TicketSeguimiento.Cia_clave, Usuario.Usu_login, TicketSeguimiento.Uni_clave,TicketSeguimiento.TStatus_clave
                FROM TicketSeguimiento
                left join TicketCat on TicketCat.TCat_clave=TicketSeguimiento.TCat_clave
                left join TicketSubcat on TicketSubcat.TSub_clave=TicketSeguimiento.TSub_clave
                left join TicketStatus on TicketStatus.TStatus_clave=TicketSeguimiento.TStatus_clave
                left join Unidad on Unidad.Uni_clave=TicketSeguimiento.Uni_clave
                left join Usuario on Usuario.Usu_login=TicketSeguimiento.Usu_registro
                left join Expediente on Expediente.Exp_folio=TicketSeguimiento.Exp_folio
                left join Compania on Compania.Cia_clave=TicketSeguimiento.Cia_clave
                WHERE TicketSeguimiento.TStatus_clave = 1 and Unidad.Uni_clave = $unidad and Exp_cancelado = 0
                order by TSeg_fechareg DESC ";
    
    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);

    echo json_encode($datos);

    $conexion = null;

}

if($funcion == 'unidades'){

    // function exporta_csv($qry,$valor,$anio,$fecha){
    //     $nombre="FOTO_SIATEL_$valor.$anio.$fecha.csv";  

    //     $fp=fopen($nombre,"w");
    //     $campos = mysql_num_fields($qry); 
    //     $i=0;
    //     $encabezado=array();
    //     //Extracción de encabezados
    //     while($i<$campos){ 
    //     $r=mysql_field_name ($qry, $i); 
    //     array_push($encabezado,$r);
    //     $i++;
    // }
    //     fputcsv($fp,$encabezado);

    //     //Extracción de datos
    //     while($row=mysql_fetch_array($qry,MYSQL_NUM)){ 
    //     fputcsv($fp,$row);
    //     }
    //     fclose($fp);
    //     }

    $conexion = conectarMySQL();

    $sql = "SELECT * FROM Compania order BY Cia_nombrecorto";

    $result = $conexion->query($sql);

    $resultado = array();
    $total = array();

    //$datos = $result->fetchAll(PDO::FETCH_OBJ);
    foreach ($result as $value) {

        $clave = $value['Cia_clave'];
        $nombre = $value['Cia_nombrecorto'];
        $activa = $value['Cia_activa'];

        if ($activa == 'S') {

            $resultado['clave'] = $clave;
            $resultado['nombre'] = $nombre;

            array_push($total, $resultado);

        }elseif ($activa == 'N' && $clave == '52') {

            $resultado['clave'] = $clave; 
            $resultado['nombre'] = $nombre;

            array_push($total, $resultado);

        }

        

    } 

    echo json_encode($total);

    $conexion = null;

}

if($funcion == 'usuario'){

    $postdata = file_get_contents("php://input");
    //aplicacmos json_decode para manejar los datos como arreglos de php
    //En este caso lo que mando es este objeto JSON {user:username,psw:password}
    $data = json_decode($postdata);
    $conexion = conectarMySQL();
    
    $nombre = $data->nombre;
    $usuario = $data->usuario;
    $psw = md5($data->psw);
    $admin = $data->admin;
    $correo = $data->correo;
    $empresa = $data->empresa;


    $sqlDet = "SELECT * FROM UsuarioInfoWeb 
                WHERE USU_login = '$usuario'";
    $result = $conexion->query($sqlDet);
    $numero = $result->rowCount();
    
    if ($numero>0){

        $respuesta = array('respuesta' => 'El Usuario ya existe');
        
    }else{

        $sql = "INSERT INTO UsuarioInfoWeb (
                        USU_nombre
                        ,USU_login
                        ,USU_password
                        ,USU_fechaReg
                        ,USU_activo
                        ,USU_administrador
                        ,USU_correo
                        ,Cia_clave
                ) VALUES (:nombre,:usuario,:psw,now(),1,:admin,:correo,:empresa)";

        $temporal = $conexion->prepare($sql);

        // $temporal->bindParam("clave", $clave, PDO::PARAM_INT);
        // $temporal->bindParam("nombre", $nombre, PDO::PARAM_STR);

        $temporal->bindParam("nombre", $nombre);
        $temporal->bindParam("usuario", $usuario);
        $temporal->bindParam("psw", $psw);
        $temporal->bindParam("admin", $admin);
        $temporal->bindParam("correo", $correo);
        $temporal->bindParam("empresa", $empresa);
        
        if ($temporal->execute()){
            $respuesta = array('respuesta' => "Los Datos se guardaron Correctamente");
        }else{
            $respuesta = array('respuesta' => "Los Datos No se Guardaron Verifique su Información");
        }
        
    }


    
    echo json_encode($respuesta);

    $conexion = null;

}

if($funcion == 'usuarios'){

    $conexion = conectarMySQL();

    $sql = "SELECT * FROM UsuarioInfoWeb";

    $result = $conexion->query($sql);

    $datos = $result->fetchAll(PDO::FETCH_OBJ);
    
    echo json_encode($datos);

    $conexion = null;

}

?>