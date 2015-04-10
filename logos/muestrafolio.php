<?php
require('class/BCGFont.php'); 	//Codigo de barras
require('class/BCGColor.php');	//Codigo de barras
require('class/BCGDrawing.php');//Codigo de barras
include('class/BCGcode39.barcode.php'); // Including the barcode technology

require "validaUsuario.php";
//Verifica el permiso para esta secci�n
$btnRegistro = $_SESSION["perRegistro"];
if ($btnRegistro !='S' ) header("Location:lanzador.php?sinpermiso=1");



	$query= "Select Exp_folio, Exp_nombre, Exp_paterno, Exp_materno, Exp_siniestro, Exp_poliza, Exp_reporte, Exp_fecreg, Expediente.Cia_clave, Usu_registro, Exp_fecreg, USU_registro, Uni_nombre, Uni_propia, Cia_nombrecorto, RIE_clave, Exp_RegCompania,
Pro_clave
			From Expediente inner join Unidad on Expediente.UNI_clave=Unidad.UNI_clave inner join Compania on Expediente.Cia_clave=Compania.Cia_clave
			where Exp_cancelado=0 and Exp_folio='".$fol."';";

	$rs = mysql_query($query,$conn);
	$row=mysql_fetch_array($rs);

		$compania	= $row["Cia_nombrecorto"];
		$nombre		= $row["Exp_nombre"];
		$paterno	= $row["Exp_paterno"];
		$materno	= $row["Exp_materno"];
		$siniestro	= $row["Exp_siniestro"];
		$poliza		= $row["Exp_poliza"];
		$reporte	= $row["Exp_reporte"];
		$obs		= $row["Exp_obs"];
		$folio		= $row["Exp_folio"];
		$unidad		= $row["Uni_nombre"];
		$fechahora	= $row["Exp_fecreg"];
		$usuario	= $row["Usu_registro"];
                $riesgoC        =$row["RIE_clave"];
                $CiaClave       =$row["Cia_clave"];
                $RegCia         =$row["Exp_RegCompania"];
                $propia         =$row["Uni_propia"];
		$producto	=$row["Pro_clave"];	//identificador de acuerdo a el producto ofrecido
		/****************************validación de identificador de prodcuto ofrecido   EEGR  **///////
		if($producto == 1) $imgProd = 'av';
		else if($producto == 2) $imgProd = 'ap';
		else if($producto == 3) $imgProd = 'es';
		else if($producto == 4) $imgProd = 'rh';
		else if($producto == 5) $imgProd = 'rh';
		else if($producto == 6) $imgProd = 'sq';
		else if($producto == 7) $imgProd = 'sn';
		else $imgProd = 'av';
		/******************************* fin de la validacion del producto*//////////////////////
	
        $dir=file_exists('codigos/'.$fol.'.png');

        if($dir != 1)
        {
           echo '<a href="generaCDB.php?fol='.$fol.'">Genera Folio</a>';
        }
        else
        {
           $dir='codigos/'.$fol.'.png';
        }


		$cadena=$compania.$nombre.$paterno.$materno.$siniestro.$poliza.$reporte.$obs.$folio.$unidad.$fechahora.$usuario.'MV';
		$cadena=md5($cadena);

 if($CiaClave==19){
     $query="Select Fol_ZIMA from RegMVZM where Fol_MedicaVial='".$fol."'";
     $rs=mysql_query($query,$conn);
     $row=mysql_fetch_array($rs);
     $folZima= $row["Fol_ZIMA"];
     }

$query="Select RIE_nombre From RiesgoAfectado Where RIE_clave=".$riesgoC;
$rs= mysql_query($query,$conn);
$row=mysql_fetch_array($rs);
$riesgo= $row["RIE_nombre"];



if($CiaClave==1) $imagen="<img src='imgs/logos/aba.jpg' width='150' height=\"60\" />";
if($CiaClave==2) $imagen="<img src='imgs/logos/afirme.jpg' width='150' height=\"60\" />";
if($CiaClave==3) $imagen="<img src='imgs/logos/aguila.jpg' width='170' height=\"60\" />";
if($CiaClave==4) $imagen="<img src='imgs/logos/chartis.jpg' width='170' height=\"60\" />";
if($CiaClave==5) $imagen="<img src='imgs/logos/ana.jpg' width='170' height=\"60\" />";
if($CiaClave==6) $imagen="<img src='imgs/logos/atlas.jpg' width='150' height=\"60\" />";
if($CiaClave==7) $imagen="<img src='imgs/logos/axa.jpg' width='180' height=\"60\" />";
if($CiaClave==8) $imagen="<img src='imgs/logos/banorte.jpg' width='170' height=\"60\" />";
if($CiaClave==9) $imagen="<img src='imgs/logos/general.jpg' width='170' height=\"60\" />";
if($CiaClave==10) $imagen="<img src='imgs/logos/gnp.jpg' width='180' height=\"60\" />";
if($CiaClave==11) $imagen="<img src='imgs/logos/goa.jpg' width='170' height=\"60\" />";
if($CiaClave==12) $imagen="<img src='imgs/logos/hdi.jpg' width='170' height=\"60\" />";
if($CiaClave==13) $imagen="<img src='imgs/logos/interacciones.jpg' width='150' height=\"60\" />";
if($CiaClave==14) $imagen="<img src='imgs/logos/latino.jpg' width='170' height=\"60\" />";
if($CiaClave==15) $imagen="<img src='imgs/logos/mapfre.jpg' width='150' height=\"60\" />";
if($CiaClave==16) $imagen="<img src='imgs/logos/metro.jpg' width='170' height=\"60\" />";
if($CiaClave==17) $imagen="<img src='imgs/logos/multiva.jpg' width='150' height=\"60\" />";
if($CiaClave==18) $imagen="<img src='imgs/logos/potosi.jpg' width='150' height=\"60\" />";
if($CiaClave==19) $imagen="<img src='imgs/logos/qualitas.jpg' width='210' height=\"60\" />";
if($CiaClave==20) $imagen="<img src='imgs/logos/rsa.jpg' width='150' height=\"60\" />";
if($CiaClave==21) $imagen="<img src='imgs/logos/zurich.jpg' width='150' height=\"60\" />";
if($CiaClave==22) $imagen="<img src='imgs/logos/primero.jpg' width='150' height=\"60\" />";
if($CiaClave==31) $imagen="<img src='imgs/logos/HIR.gif' width='65' height=\"100\" />";
if($CiaClave==32) $imagen="<img src='imgs/logos/SPT.jpg' width='80' height=\"100\" />";
if($CiaClave==33) $imagen="<img src='imgs/logos/ace.bmp' width='130' height=\"80\" />";
if($CiaClave==34) $imagen="<img src='imgs/logos/TTRAVOL.jpg' width='130' height=\"80\" />";
if($CiaClave==35) $imagen="<img src='imgs/logos/multiasistencia.jpg' width='130' height=\"80\" />";
if($CiaClave==36) $imagen="";
if($CiaClave==37) $imagen="";
if($CiaClave==43) $imagen="<img src='imgs/logos/ci.jpg' width='130' height=\"80\" />";
if($CiaClave==44) $imagen="";
if($CiaClave==45) $imagen="<img src='imgs/logos/inbursa.jpg' width='130' height=\"80\" />";
if($CiaClave==46) $imagen="<img src='imgs/logos/orthofam.jpg' width='150' height=\"70\" />";
if($CiaClave==47) $imagen="<img src='imgs/logos/thona.jpg' width='130' height=\"80\" />";
if($CiaClave==51) $imagen="<img src='imgs/logos/particulares.jpg' width='150' height=\"70\" />";
?>

<html>
 <script type="text/javascript" language="javascript">
            javascript:window.history.forward(1);
    </script>
	<body>

	<br>
	<br>

   	<table align="center" width="90%">
		<tr>
                      <td width="20%">
                          <?php echo $imagen;?>				
                      </td>
			<td width="13.3%">                          
				<?php echo "<img src='imgs/producto/".$imgProd.".png' width='70' height=\"70\" />";?>
                      </td>

			<td width="33.3%">
				<?php
					echo "<img src=\"".$dir."\"/>";
				?>
			</td>

                       <td width="33.3%">
                       </td>
		</tr>
	</table>


	<br>
	<br>
	<hr>
	<table width="100%" border="0">
		<tr>
			<th align="left" width="10%">
				Folio:
			</th>
			<td width="25%">
				<?php echo $folio;?>
			</td>
			<th align="left" width="10%">
				Compa&ntildeia:
			</th>
			<td width="25%">
				<?php echo $compania;?>
			</td>
			<th align="left" width="10%">
				Unidad Medica:
			</th>
			<td width="20%">
				<?php echo $unidad;?>
			</td>
		</tr>
		<tr>
			<th align="left">
				P&oacuteliza:
			</th>
			<td>
				<?php echo $poliza;?>
			</td>
			<th align="left">
				Siniestro:
			</th>
			<td>
				<?php echo $siniestro;?>
			</td>
			<th align="left">
				Reporte:
			</th>
			<td>
				<?php echo $reporte;?>
			</td>
		</tr>
                <tr>
                    <th align="left">
                          Riesgo:
                    </th>
                    <td>
                    <?php if($riesgo!=''){echo $riesgo;}
                    else{ echo "";}

                    ?>
                    </td>
                    <? if($CiaClave==10){?>
                    <th align="left">
                        Folio de Segmentaci&oacute;n:
                    </th>
                    <td>
                    <?php echo $RegCia;
                    ?>
                    </td>
                    <?}else if($CiaClave==19){?>
                    <th align="left">
                        Folio electr&oacute;nico:
                    </th>
                    <td>
                    <?php echo $RegCia;
                    ?>
                    </td>
                    <?}else{?>
                    <td>
                    </td>
                    <td>
                    </td>
                    <?}?>
                    <?if ($CiaClave==19){?>
                    <th align="left">
                        Folio ZIMA:
                    </th>
                    <td>
                        <?echo $folZima;?>
                    </td>
                    <?}?>
                </tr>
		<tr>
			<th align="left">
				Lesionado:
			</th>
			<td colspan="5">
				<?php echo $nombre." ".$paterno." ".$materno; ?>
			</td>
		</tr>
	</table>
	<br>
      <table>
		<tr>
			<th align="left">
				Usuario:
			</th>
			<td>
				<?php echo $usuario; ?>
			</td>
			<th align="left">
				Registro:
			</th>
			<td align="left" width="300px">
				<?php echo $fechahora; ?>
			</td>
                        <?if($propia=='S'){ ?>
                        <th align="center">
                            ______________________________________<br/>
                            Nombre y firma de Recepcionista en turno
                        </th>
                        <?}   ?>
		</tr>
	</table>
	<table>
		<tr>
			<th align="left">
				Verificador:
			</th>
			<td>
				<?php echo $cadena; ?>
			</td>
		</tr>
	</table>
	<hr>
        <h3>Favor de anexar al expediente la siguiente documentaci&oacuten:</h3>
	<!--
	<table width="95%">
		<tr>
			<td width="5%">

			</td>
			<td width="27%"> Documento
			</td>
			<td width="60%"> Verificar
			</td>

		</tr>
	</table>

	<table width="95%" border="1">
		<tr>
			<td width="5%">
				<input type="checkbox" />
			</td>
			<th align="left" width="27%">
				Pase Medico Original
			</th>
			<td width="60%">
					<ul>
						<li>Que esta dentro de vigencia.</li>
						<li>Que coincida con la identidad del lesionado.</li>
						<li>Que no presente alteraciones.</li>
						<li>En algunos casos se requiere llenar el informe medico impreso al reverso.</li>
					</ul>
			</td>

		</tr>
		<tr>
			<td width="5%">
				<input type="checkbox" />
			</td>
			<th align="left" width="27%">
				Nota Medica (formato M&eacutedicavial)
			</th>
			<td width="60%">
					<ul>
						<li>Llena en letra clara.</li>
						<li>Llena en su totalidad.</li>
						<li>Que detalle los suministros otorgados.</li>
						<li>Que detalle los estudios radiologicos.</li>
						<li>Que describa claramente el diagnostico.</li>
						<li>Que este firmada por el lesionado donde sea pertinente.</li>
						<li>Que este firmada por el medico tratante (incluir cedula de especialidad).</li>
						<li>Deseable:incluir numero de CPTs.</li>
					</ul>
			</td>

		</tr>
		<tr>
			<td width="5%">
				<input type="checkbox" />
			</td>
			<th align="left" width="27%">
				Cuestionario de atencion
			</th>
			<td width="60%">
					<ul>
						<li>Que esto bien identificado (nombre, medico, etc).</li>
						<li>Firmado por el lesionado.</li>
					</ul>
			</td>

		</tr>
		<tr>
			<td width="5%">
				<input type="checkbox" />
			</td>
			<th align="left" width="27%">
				Copia de identificaci&oacuten
			</th>
			<td width="60%">
					<ul>
						<li>Que pertenezca al lesionado.</li>
						<li>Que sea oficial.</li>
						<li>Fotocopiada por ambos lados.</li>
						<li>En caso necesario se puede utilizar el formato de identificacion. (verificar firmas y huella)</li>
                                                <li>Verificar parentesco</li>
					</ul>
			</td>

		</tr>
		<tr>
			<td colspan="4">
				**Favor de verificar la consistencia de las firmas en todo el expediente<br>
				**Todos los formatos deberan estar bien identificados<br>
				**Favor de imprimir este formato y anexarlo al expediente<br>
			</td>

		</tr>
	</table>
        -->
     <form method="POST" action="formatoFolio.php">
        <table align="center">
            <tr>
                <td align="center">
                    <input type="submit" value="< Imprimir >" style="width:120px;">
                </td>
                <td>
                    <input type="hidden" value="<?echo $fol;?>" name="fol" id="fol">
                </td>
            </tr>
        </table>
	<br>
     </form>
<!--<a href="http://regmv.orthofam.com.mx/lanzador.php"> Salir </a>-->
<a href="http://www.medicavial.net/registro/lanzador.php"> Salir </a>


	</body>
</html>
