<?php

define("SOAP_CLIENT_BASEDIR", "phptoolkit-13_1/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');
require_once ('phptoolkit-13_1/userAuth.php');

try {
	$mySforceConnection = new SforcePartnerClient();
	$mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
	$loginResult = $mySforceConnection->login($USERNAME, $PASSWORD);
	
	date_default_timezone_set("America/Mexico_City");
	$script_tz = date_default_timezone_get();
	
	if (strcmp($script_tz, ini_get('date.timezone'))) {
		//echo 'La zona horaria del script difiere de la zona horaria de la configuracion ini.';
	} else {
		//echo 'La zona horaria del script y la zona horaria de la configuración ini coinciden.';
	}
	
	require_once ('parametros.conf');
	
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
	$conteoArchivos = 1;
	$y = 0;
	$Cantidad= 0;
	$MontoTotal = 0;
	
	/*
	Nombre del archivo
	*/
	$NombreArchivo = "CAR" . date('y') . date('m') . date('d') . "58860185";
	// & Format(Secuencial, "00") "c:\CAR" + nano + mes + Dia + "58860185" + num_tc_banamex + ".bca"
	//chdir($raiz);
	$id_d=opendir($c_destino);
	
	while(false!==($fichero=readdir($id_d))) {
		if(($fichero!=".") && ($fichero!="..") && (!is_dir($fichero))) {
			if(strstr($fichero,$NombreArchivo)) {
				//if(ereg(".csv",$fichero) || ereg(".CSV",$fichero)  ){
				$listado_ficheros[]=$fichero;
				$conteoArchivos++;
			}
		}
	}
	
	closedir($id_d);
	
	$NombreArchivo = $NombreArchivo . str_pad($conteoArchivos, 2, '0', STR_PAD_LEFT);
	
	if (!$handle = fopen($c_destino."/".$NombreArchivo.".bca", 'a')) {
		echo "<br>No se puede abrir el archivo ($filename)";
		exit;
	}
	
	/*	
	'''''''''''''''Registro Header del Archivo
	*/
	$strCadena = "CAB2.10" . date('Y') . date('m') . date('d') . "A000058860185" . "                                                                                            " . ".";
	
	/*
	'''''''''''''''Registro Detalle del Archivo
	*/
	echo "<pre>";
	echo $queryPagos = "SELECT Id,
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
						  Padrino__r.NIP__c,
						  Tipo_de_tarjeta__c,
						  Forma_de_pago__c,
						  Estatus_p__c
					FROM Pagos__c
					
					WHERE (Estatus_p__c= 'Activo' 
						OR Estatus_p__c= 'Muy interesado') 
						AND Tipo_de_tarjeta__c != 'AMEX' 
						AND  ( Estatus_final__c = 'Pendiente' 
						OR Estatus_final__c = 'Rechazado' 
						OR Estatus_final__c = 'Hard Decline' 
						OR Estatus_final__c = 'Soft Decline (A)') 
						AND ( Forma_de_pago__c='Tarjeta de Credito'  
						Or Forma_de_pago__c='Tarjeta de Crédito' 
						OR Forma_de_pago__c='Tarjeta Credito'  
						Or Forma_de_pago__c='Tarjeta Crédito')
						AND (Padrino__r.Name  ='10114'
						OR Padrino__r.Name  ='13159' 
						OR Padrino__r.Name  ='13104')";
	
	$responsePagos = $mySforceConnection->query($queryPagos);
	$resultPagos = new QueryResult($responsePagos);
	
	echo "<br>Registros a procesar:  ".$resultPagos ->size;
		
	foreach ($resultPagos ->records as $unPago) {
		$Cantidad++;
		
		$strCadena .= "
02153484"
			. str_pad( limpiarTarjeta($unPago->fields->Numero_de_la_tarjeta__c), 16, ' ', STR_PAD_RIGHT)
			. "80" . "484";
		$pieces = explode(".", $unPago->fields->Importe__c);
		$strCadena .= str_pad( $pieces[0], 8, '0', STR_PAD_LEFT)."";
		$strCadena .= str_pad( $pieces[1], 2, '0', STR_PAD_RIGHT);
		//$strCadena .= date('y') . date('m') . date('d');
		
		/*
		'''''''''''aqui inicia la referencia a 23 posiciones
		*/
		if(substr(limpiarTarjeta($unPago->fields->Numero_de_la_tarjeta__c),0,1)==4) {
			$referencia = "7454061";
		}else{
			$referencia = "7543006";
		}
        
		$diadelaño = substr(date('Y'), 3, 1) . str_pad(date('z')+1, 3, '0', STR_PAD_LEFT);
		
		echo "<br>" . $referencia = $referencia . $diadelaño . "80185"
			. str_pad( $unPago->fields->Cuenta__r->fields->NIP__c, 6, '0', STR_PAD_LEFT);
		
		//Proceso Dígito Verificador
		$dv = 0;
        $x = 0;
        $i = 0;
        $J = 0;
		$arrdig = array();
		$arrdsumdig = array();
		for($i = 0; $i <= 21; $i++) {
			//echo (int)substr($referencia,$i,1);
			$arrdig[$i][0]= (int)substr($referencia,$i,1);
			if((($i+1) % 2 ) == 0) {
				$arrdig[$i][1] = 2;
			} else {
				$arrdig[$i][1] = 1;
			}
			$arrdig[$i][2] = $arrdig[$i][0] * $arrdig[$i][1];
			
			if(strlen(strval($arrdig[$i][2]))>1) {
				$arrdsumdig[$J] = substr($arrdig[$i][2],0,1);
				$arrdsumdig[$J+1] = substr($arrdig[$i][2],1,1);
				$J=$J+2;
			} else {
				$arrdsumdig[$J] = $arrdig[$i][2];
				$J=$J+1;
			}
			
		}
				
		for ($i = 0; $i <= count($arrdsumdig); $i++) {
			$dv = $dv + $arrdsumdig[$i];
		}
		
		$x = 0;

		//Verifica el múltiplo de 10 inmediato superior al resultado
		while($dv>$x):
			$x = $x + 10;
		endwhile;
		
		//Crea el Dígito Verificador
		$dv = $x - $dv;
		
		//Si el dígito Verificador es 10 (múltiplo exacto) lo convierte en 0
		if($dv == 10) {
			$dv = 0;
		}
		
		$referencia = $referencia . strval($dv);
		//'''''''Termina Proceso Dígito Verificador
				
		$strCadena .= $referencia;
		//Aqui termina la referencia a 23 posiciones	
		
		$strCadena .=  "P" . $unPago->Id . "";
		//Autorización de la Venta, Diferimiento, Número de pagos, Tipo de plan, Bandera de Respuesta, Número de Contrato
		$strCadena .= "000000" . "00" . "00" . "00" . " " . "PT" . $unPago->fields->Padrino__c . "      " . ".";
		/*
		'''''''Termina Registro Detalle del Archivo
		*/
		
		$MontoTotal = $MontoTotal + $unPago->fields->Importe__c;
	}// final del foreach
	/*
	'''''''''''Registro Trailer del Archivo	
	*/
	
	$strCadena .= "
F" . str_pad($Cantidad, 6, '0', STR_PAD_LEFT)
		. str_pad(($MontoTotal*100), 15, '0', STR_PAD_LEFT)
		. "                                                                                                  " . ".";
		
	if (fwrite($handle, $strCadena) === FALSE) {
		echo "No se puede escribir en el archivo ($handle)";
		exit;
	} else {
		echo "<br>Salida exitosa";	
	}
	
	exit();
} catch(Exception $e) {
	echo $e->faultstring;
}
?>