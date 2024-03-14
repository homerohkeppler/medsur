<?php

//Output Debito

$USERNAME='salesforce@teleton-sistemas.org.mx';
$PASSWORD='MneUPGeP23047iopkkvZdng4NNmew1N3VLN4';
$TOKEN='7iopkkvZdng4NNmew1N3VLN4';

define("SOAP_CLIENT_BASEDIR", "Toolkit20/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');
//require_once ('Toolkit20/userAuth.php');

try {
	$mySforceConnection = new SforcePartnerClient();
	$mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
	$loginResult = $mySforceConnection->login($USERNAME, $PASSWORD);
	$divisor=";";
	$reg_MalaEstructura=0;
	$reg_NoCliente=0;
	$listado_ficheros=array();
	$rTotales=0;
	$routput=0;
	$rInsertados=0;
	$rCreados=0;
	$rActualizados=0;
	$k=0;
	$l=0;
	$lineasTotal=0;
	$somecontent="ok";

	date_default_timezone_set("America/Mexico_City");
	$script_tz = date_default_timezone_get();

	if (strcmp($script_tz, ini_get('date.timezone'))) {
		//echo 'La zona horaria del script difiere de la zona horaria de la configuracion ini.';
	} else {
		//echo 'La zona horaria del script y la zona horaria de la configuración ini coinciden.';
	}

	require_once ('parametros.conf');

	if(isset($_REQUEST["week"] ) ) {
		$semanaMes=$_REQUEST["week"];
		$semanaMesString=" AND Semana_de_cobro__c = '".$semanaMes." semana'";
		if($semanaMes=='all' || $semanaMes=='All' || $semanaMes=='ALL'  ) {
			$semanaMesString='';
		}
	} else {
		$dia  = date("j");
		$semanaMes = 0;
		while($dia >= 1) {
			$semanaMes++;
			$dia -= 7;
		}
		$semanaMesStringunida='';
		for($i=1;$i<=$semanaMes;$i++) {
			$semanaMesStringunida.=" Semana_de_cobro__c = '".$i." semana'";
			if($i<$semanaMes) {
				$semanaMesStringunida.=" OR ";
			}
		}
		$semanaMesString="AND ( ".$semanaMesStringunida.")";
	}

	function output($motivo,$LineaDeIncidencia,$pizza2) {
		global $handle, $divisor;
		$somecontent = $motivo.$divisor.";".$LineaDeIncidencia.$divisor.";".$pizza2;
		if (fwrite($handle, $somecontent) === FALSE) {
			echo "No se puede escribir en el archivo ($filename)";
			exit;
		}
	}// de la funcion output

	function cambiodeCaracteres($fecha) {
		$caracteresRaros = array("&");
		$caracteresCorrectos  = array("&amp;");
		return $fecha = str_replace($caracteresRaros, $caracteresCorrectos,$fecha);
	}

	function acentos($fecha) {
		$caracteresRaros = array("?", "?", "?","?","?","?", "?", "?","?","?","?","?","&","?");
		$caracteresCorrectos  = array("a", "e", "i","o", "u","A", "E", "I","O","U","n","N"," ","e"); 
		return $fecha = str_replace($caracteresRaros, $caracteresCorrectos,$fecha);
	}

	function cambioFormatoFecha($fecha) {
		$ano=substr($fecha, 6, 4);
		if($ano==""){$ano="2009";}
		return  $ano."-".substr($fecha, 3, 2)."-".substr($fecha, 0, 2);
	}// fin de la funcion cambioFormatoFecha

	function acentos_sitios($fecha) {
		$caracteresRaros = array("?", "?", "?","?","?","?", "?", "?","?","?","?","?","&","?",'"',"?","?","?");
		$caracteresCorrectos  = array("a", "e", "i","o", "u","A", "E", "I","O","U","n","N"," ","e",'',"","",""); 
		return $fecha = str_replace($caracteresRaros, $caracteresCorrectos,$fecha);
	}

	function limpiarTarjeta($tarjeta) {
		$caracteresRaros = array(" ", "_", "-");
		$caracteresCorrectos  = array( "" , "" , ""); 
		return $tarjeta = str_replace($caracteresRaros, $caracteresCorrectos,$tarjeta);
	}

	function limpiarNombre($tarjeta) {
		$caracteresRaros = array(".", "_", "-");
		$caracteresCorrectos  = array( "" , "" , "");
		return $tarjeta = str_replace($caracteresRaros, $caracteresCorrectos,$tarjeta);
	}

	/*
	Declaración de variables
	*/
	$Consecutivo = 1;
	$Secuencial= 1	;
	$Cargos = 0;
	$monto = 0;
	$conteoArchivos = 1;

	/*
	Nombre del archivo
	*/
	$NombreArchivo = "dcb" . date('y') . date('m') . date('d') . "000058860185";
	// & Format(Secuencial, "00")
	//chdir($raiz);
	$id_d=opendir($c_destino);

	while(false!==($fichero=readdir($id_d))) {
		if(  ($fichero!=".") && ($fichero!="..") && (!is_dir($fichero)) ) {
			if(strstr($fichero,$NombreArchivo)) {
				//if(ereg(".csv",$fichero) || ereg(".CSV",$fichero)  ){
				$listado_ficheros[]=$fichero;
				$conteoArchivos++;
			}
		}
	}

	closedir($id_d);

	$NombreArchivo = $NombreArchivo . str_pad($conteoArchivos, 2, '0', STR_PAD_LEFT);

	if (!$handle = fopen($c_destino."/".$NombreArchivo.".dom", 'a')) {
		echo "<br>No se puede abrir el archivo ($filename)";
		exit;
	}

	/*
	Registro Ecabezado de bloque
	*/
	$strCadena = "01" . str_pad($Consecutivo, 7, '0', STR_PAD_LEFT) . "30" . "002" . "E" . "2" . date('d') . str_pad($conteoArchivos, 5, '0', STR_PAD_LEFT) . date('Y') . date('m') . date('d') . "01" . "00" . "                         " . "FUNDACION TELETON MEXICO A C            " . "FTM981104540      " . "                                                                                                                                                                                      " . "000058860185" . str_pad($conteoArchivos, 2, '0', STR_PAD_LEFT) . "                                                                                      ";

	/*
	COmienca Extraccion de cuentas
	*/
	echo "<pre>";
$queryPagos = "SELECT Id,
Estatus__c,
Padrino__c,
Importe__c,	

Banco_emisor__c,
Nombre_del_banco_emisor__c,

Nombre_tarjetahabiente__c,
Cuenta_Bancaria__c,
Numero_de_la_tarjeta__c,
Codigo_de_seguridad__c,
Vencimiento_anio__c,
Vencimiento_mes__c,


Padrino__r.Name,
Padrino__r.Nip__c,
Banco_emisor_b__r.Codigo_Banco__c,
Forma_de_pago__c,
Estatus_p__c
from Pagos__c

WHERE (Estatus_p__c = 'Activo' OR Estatus_p__c = 'Muy Interesado') AND  Tipo_de_tarjeta__c != 'AMEX' AND ( Estatus_final__c = 'Pendiente' OR Estatus_final__c = 'Rechazado' ) AND (Forma_de_pago__c='Tarjeta de Debito' OR Forma_de_pago__c='Tarjeta de Débito')";

	//$queryPagos .= $semanaMesString;
	echo $queryPagos;

	$responsePagos = $mySforceConnection->query($queryPagos);
	$resultPagos = new QueryResult($responsePagos );

	echo "<br>Registros a procesar:  ".$resultPagos ->size;

	foreach ($resultPagos ->records as $registroQueryNoObj ) {
									 $unPago = new sObject($registroQueryNoObj);

		/*
		Registro Detalle
		*/ 
		$Consecutivo ++;
		$strCadena .= "\r\n" . "02" . str_pad($Consecutivo, 7, '0', STR_PAD_LEFT) . "30" . "01" 
			. str_pad(($unPago->fields->Importe__c *100), 15, '0', STR_PAD_LEFT) 
			. date('Y') . date('m') . date('d')
			. "000000" . "                  " . "51" 
			. date('Y') . date('m') . date('d')
			. str_pad(substr ($unPago->fields->Banco_emisor_b__r->fields->Codigo_Banco__c,0,3), 3, '0', STR_PAD_LEFT)
			.  "03"
			. str_pad( limpiarTarjeta($unPago->fields->Numero_de_la_tarjeta__c), 20, '0', STR_PAD_LEFT)
			. str_pad( limpiarNombre($unPago->fields->Nombre_tarjetahabiente__c), 40, ' ', STR_PAD_RIGHT)
			//. str_pad( limpiarNombre($unPago->Id), 40, ' ', STR_PAD_RIGHT)
			//. $unPago->fields->Numero_de_la_tarjeta__c//carg_Tarjetahabiente
			. str_pad( $unPago->fields->Padrino__r->fields->Name, 40, '0', STR_PAD_LEFT)// cambiar por Folio del padrino padr_folio
			//. str_pad( limpiarNombre( $unPago->fields->Cuenta__r->fields->Name), 40, ' ', STR_PAD_RIGHT) // hace falta aplicar efuncion AumentaEspacio y poner la variable nombpad 
			. str_pad( limpiarNombre($unPago->Id), 40, ' ', STR_PAD_RIGHT)
			. "000000000000000" . "0000000" . "FUNDACION TELETON MEXICO A C            "
			.  "00" . "                     " . "00" . "00" . "1" . "000000" 
			. date('Y') . date('m') . date('d') 
			. str_pad( $conteoArchivos, 2, '0', STR_PAD_LEFT)
			. "                    " . "                    " . "                    " . "                   ";
		$Cargos ++;
		$monto = $monto +  $unPago->fields->Importe__c;
	}// final del foreach

	/*
	registro sumario de bloque
	*/
	$Consecutivo ++;
	$strCadena .= "\r\n" . "09" 
		. str_pad( $Consecutivo, 7, '0', STR_PAD_LEFT)
		. "30" . date('d') 
		. str_pad( $conteoArchivos, 5, '0', STR_PAD_LEFT)
		. str_pad( $Cargos, 7, '0', STR_PAD_LEFT)
		. str_pad( ($monto * 100), 18, '0', STR_PAD_LEFT)
		. "                                                                                                                                                                                                                                                                                                                                                                     ";

	if (fwrite($handle, $strCadena) === FALSE) {
		echo "No se puede escribir en el archivo ($handle)";
		exit;
	} else {
		echo "<br>Salida exitosa";	
	}

	exit();

} catch (Exception $e) {
	echo $e->faultstring;
}

?>
