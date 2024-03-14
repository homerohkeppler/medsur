<?php

//output Amex
$USERNAME='salesforce@teleton-sistemas.org.mx';
$PASSWORD='MneUPGeP23047iopkkvZdng4NNmew1N3VLN4';
$TOKEN='7iopkkvZdng4NNmew1N3VLN4';

define("SOAP_CLIENT_BASEDIR", "Toolkit20/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');
	
try {
	$mySforceConnection = new SforcePartnerClient();
	$mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
	$loginResult = $mySforceConnection->login($USERNAME, $PASSWORD);
	
	date_default_timezone_set("America/Mexico_City");
	$script_tz = date_default_timezone_get();
	
	if (strcmp($script_tz, ini_get('date.timezone'))){
		//echo 'La zona horaria del script difiere de la zona horaria de la configuracion ini.';
	} else {
		//echo 'La zona horaria del script y la zona horaria de la configuración ini coinciden.';
	}
	
	require_once ('parametros.conf');
	
	if(isset($_REQUEST["week"])) {
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
		
		$semanaMesString=" AND ( ".$semanaMesStringunida.")";
	}
	
	function limpiarTarjeta($tarjeta) {
		$caracteresRaros = array(" ", "_", "-");
		$caracteresCorrectos  = array( "" , "" , ""); 
		
		return $tarjeta = str_replace($caracteresRaros, $caracteresCorrectos,$tarjeta);
	}
	
	/*
	Declaración de variables
	*/
	$Consecutivo = 1;
	$Secuencial= 1;
	$Cargos = 0;
	$monto = 0;
	$conteoArchivos = 0;
	$y = 0;
	$Cantidad= 0;
	$MontoTotal = 0;	
	$fech = "";
	
	/*
	Nombre del archivo
	*/
	$NombreArchivo = "RB_INPUT_FILE_ATGMXP" ;
	
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
	
	$NombreArchivo2 = $c_destino."/".$NombreArchivo . str_pad($conteoArchivos, 3, '0', STR_PAD_LEFT).".txt";
	$NombreArchivo  = $c_destino."/".$NombreArchivo . ".txt";
	
	if (file_exists ($NombreArchivo)) {
		rename($NombreArchivo, $NombreArchivo2);
	}
	
	echo '<a href="'.$NombreArchivo.'">Archivo final que se utiliza:'.$NombreArchivo.'</a><br>';
	if (!$handle = fopen($NombreArchivo, 'a')) {
		echo "<br>No se puede abrir el archivo ($filename)";
		exit;
	}
	
	/*
	''''''''''Transaction File Header (TFH) Record '''''''''''''''''''''''''''''''''''
	*/
	$cons = 0;
	$cons2 = 1;
	
	$fech = date('Y') . date('m') . date('d') . date('H') . date('i') . date('s');
	
	$strCadena = "TFH" . "00000001" . "ATGMXP     " . "                     " . "000000001" . "000000000" . $fech . "09010000" . "                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         ";
	
	$queryPagos = "SELECT Id,
						  Name,
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
						  Tipo_de_tarjeta__c,
						  
						  Forma_de_pago__c,
						  Estatus_p__c
				FROM Pagos__c
				WHERE (Estatus_p__c = 'Activo' OR Estatus_p__c = 'Muy Interesado') AND Tipo_de_tarjeta__c = 'Amex' AND (Estatus_final__c = 'Pendiente' OR Estatus_final__c = 'Rechazado') AND (Forma_de_pago__c = 'Tarjeta de Credito' OR Forma_de_pago__c = 'Tarjeta de Crédito' OR Forma_de_pago__c = 'Tarjeta Credito' OR Forma_de_pago__c = 'Tarjeta Crédito')";
					
	echo '<br>Query AMEX:<font size="1px">'.$queryPagos;
	//echo '<br>Query:<font size="1px">'.$queryPagos .= $semanaMesString;
	echo '</font>';
	$responsePagos = $mySforceConnection->query($queryPagos);
	$resultPagos = new QueryResult($responsePagos );
	echo '<br>Registros a procesar:<font size="1px">'.$resultPagos ->size;
	echo '</font>';

	foreach ($resultPagos ->records as $registroQueryNoObj ) {
							 $unPago = new sObject($registroQueryNoObj);

		$cons = $cons + 2;
		$cons2 = $cons2 + 2;
		
		echo "<br><pre>";
		echo '<br>Pago por pago:<font size="1px">';
		print_r($unPago);
		echo '</font>';
		//echo $unPago->Id;
		
		/*
        ''''''''''Transaction Advice Basic (TAB) Record'''''''''''''''''''''''''''''''''''
		*/
		$strCadena .= "
TAB" 
			. str_pad( $cons , 8, '0', STR_PAD_LEFT)
  			. "000000000000000" . "02" . "05" . "  " . "          " . "000000"
			
			. str_pad( limpiarTarjeta($unPago->fields->Numero_de_la_tarjeta__c), 15, '0', STR_PAD_LEFT) . "    " . "    "
			
			. $fech . "000" 
			. str_pad( $unPago->fields->Importe__c * 100 , 12, '0', STR_PAD_LEFT)
			. "000000" . "484" . "01"
            . "9350558517     " . "               " . "                                        " . "        "

            . "000000000000" . "000" . "000000000000" . "   "
			/*
            . str_pad( $unPago->fields->Padrino__r->fields->Name, 7, '0', STR_PAD_LEFT) . "NIP Padrino  " . "          " . "               " . "                                                                                                                                                                                                                                                                                                                                                                                                                                                    "
			*/
			//. str_pad( $unPago->Id, 30, ' ', STR_PAD_RIGHT) 
			. $unPago->Id . "            " 
			. "               " . "                                                                                                                                                                                                                                                                                                                                                                                                                                                    "
			
			/*
				SEGUNDO BLOQUE
			*/
			. "
TAA" . str_pad( $cons2, 8, '0', STR_PAD_LEFT) . "000000000000000" . "00" . "99" . "PADRINO TELETON                       "
            . "COPERNICO 51 ANZURES MIGUEL HIDALGO   " . "DISTRITO FEDERAL     " . "DIF" . "MEX" . "11590" . str_pad( ' ', 552, ' ', STR_PAD_RIGHT) ;
			//$CADENADEESPACIO="";
			//$CADENADEESPACIO=str_pad( $strCadenaCADENADEESPACIO, 552, ' ', STR_PAD_RIGHT);	
		$strCadena.=$CADENADEESPACIO;	
		$Cantidad++;
		$MontoTotal = $MontoTotal +  $unPago->fields->Importe__c;
	}
	/*
		''''''''''Transaction Batch TRailer (TBT) Record '''''''''''''''''''''''''''''''''''
	*/
	$cons3 = $cons + 2;
	$cons4 = $cons2 + 2;
	$strCadena .= "
TBT" 
		. str_pad( $cons3, 8, '0', STR_PAD_LEFT)
		. "9350558517     " . "               " . "000000000000001" 
		. date('Y') . date('m') . date('d')
		. str_pad( $Cantidad, 8, '0', STR_PAD_LEFT)
		
		. "000" 
		. str_pad( $MontoTotal * 100, 20, '0', STR_PAD_LEFT)
		. "+" . "484" . "000" . "00000000000000000000" . "   "
        . "                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               "
        
		//. str_pad( $Cantidad, 6, '0', STR_PAD_LEFT) 
		//. str_pad( $MontoTotal * 100, 11, '0', STR_PAD_LEFT)
		//. "000000" . "0000000000000" . "             "
        
	/*        
		''''''''''Transaction File Summary (TFS) Record'''''''''''''''''''''''''''''''''''''
	*/
	. "
TFS" 
		. str_pad( $cons4, 8, '0', STR_PAD_LEFT)
		. str_pad( $Cantidad, 8, '0', STR_PAD_LEFT) 
		. "000" 
		. str_pad( $MontoTotal * 100, 20, '0', STR_PAD_LEFT)
        . "00000000" . "000" . "00000000000000000000" . "000" 
		. str_pad( $MontoTotal * 100, 20, '0', STR_PAD_LEFT)
        . "                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ";
		
	if (fwrite($handle, $strCadena) === FALSE) {
		echo "No se puede escribir en el archivo ($handle)";
		exit;
	} else {
		echo "<br>Salida exitosa";
		echo '<br>Contenido del archivo:<font size="1px"><br>'.$strCadena;
		echo '</font>';
		echo '<br>Contenido del archivo utf8_encode:<font size="1px"><br>'.utf8_encode ($strCadena);
		echo '</font>';
		echo '<br>Contenido del archivo utf8_decode:<font size="1px"><br>'.utf8_decode ($strCadena);
		echo '</font>';
	}
	
	exit();
} catch (Exception $e) {
	echo $e->faultstring;
}

?>