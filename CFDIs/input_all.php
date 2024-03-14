<?php
define("SOAP_CLIENT_BASEDIR", "phptoolkit-13_1/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');
require_once ('phptoolkit-13_1/userAuth.php');

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
	$hoy = date("Y-m-d");


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
		if($ano=="") {$ano="2009";}
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
	Nombre del archivo, con el patro .dom para obtener los que son respuestas de debito.
	*/
	$extCredito = ".bca";
	$extDebito = ".dom";
	$extAmex = ".txt";
	$id_d = opendir($c_xprocesar);

	while(false !== ($fichero=readdir($id_d))) {
		if(  ($fichero!=".") && ($fichero!="..") && (!is_dir($fichero)) ) {
			if(strstr($fichero,$extCredito) || strstr($fichero,$extDebito) || strstr($fichero,$extAmex)) {

				$listado_ficheros[] = $fichero;
				$conteoArchivos++;
			}
		}
	}
//echo "Archivos a procesar k: ".$conteoArchivos;
	closedir($id_d);
	$sObjectsFile = array();
	$sObjectsResponse = array();
	$sObjectsPago = array();

	foreach($listado_ficheros as $NombreArchivo) {
		
		echo "Fichero actual: " . $NombreArchivo . "<br />";

		if (!$handle = fopen($c_xprocesar."/".$NombreArchivo, 'r ')) {
			echo "<br>No se puede abrir el archivo ($filename)";
			exit;
		}
		$complemento="";
		if(strstr($NombreArchivo,$extAmex)){
			$complemento= date('l jS \of F Y h:i:s A');
			}
		$fieldsFile = array (
			'name' => $c_xprocesar."/".$NombreArchivo,
			'Unico__c' => $NombreArchivo.$complemento,
			

		);

		$sObject = new SObject();
		$sObject->fields = $fieldsFile;
		$sObject->type = 'File__c';
		array_push($sObjectsFile, $sObject);
		$upsertResponseFile = $mySforceConnection->create($sObjectsFile);
		/*
		echo "<pre>";
		print_r( $upsertResponseFile);
		echo "</pre>";
		*/
		if($upsertResponseFile->id != ''){
			
		if(strstr($NombreArchivo,$extCredito)) {
			echo "CREDITO <br />";
			$primeraLinea = fgets($handle);
			$fechaArchivo = date(substr($primeraLinea, 7, 4)."-".substr($primeraLinea, 11, 2)."-".substr($primeraLinea, 13, 2));

			while(!feof($handle)) {
				$linea = fgets($handle);
				echo "linea: " . $linea . "<br />";
				
				if(strlen($linea) > 0 && (strpos($linea, 'CAB2.10') === false && substr($linea, 0, 1) !== "F")) {
					$idSF = substr($linea, 63, 18);
					$statusPago = "CR" . substr($linea, 99, 2);
					echo "     ID SF : " . $idSF . "<br />";
					echo "     Estatus: " . $statusPago . "<br />";

					//PAGO
					$fieldsPago = array(
						'Estatus__c' => $statusPago,
						'Fecha_de_procesamiento_bancario__c' => $hoy,
						'Fecha_de_generacion_de_archivo__c' => $fechaArchivo
					);
					$sObject = new SObject();
					$sObject->Id = $idSF;
					$sObject->type = 'Pagos__c';
					$sObject->fields = $fieldsPago;
					array_push($sObjectsPago, $sObject);

					//RESPONSE
					$fieldsResponse = array(
						'File__c' => $upsertResponseFile->id,
						'Response__c' => $linea
					);

					$sObject = new SObject();
					$sObject->type = 'Response__c';
					$sObject->fields = $fieldsResponse;
					array_push($sObjectsResponse, $sObject);
				}

				if(count($sObjectsPago) == 200) {
					$updateResponsePago = $mySforceConnection->update($sObjectsPago);
					unset($sObjectsPago);
					$sObjectsPago = array();
				}

				if(count($sObjectsResponse) == 200) {
					$updateResponseResponse = $mySforceConnection->create($sObjectsResponse);
					unset($sObjectsResponse);
					$sObjectsResponse = array();
				}
			}

			if(count($sObjectsPago) > 0) {
				$updateResponsePago = $mySforceConnection->update($sObjectsPago);
				unset($sObjectsPago);
				$sObjectsPago = array();
			}

			if(count($sObjectsResponse) > 0) {
				$updateResponseResponse = $mySforceConnection->create($sObjectsResponse);
				unset($sObjectsResponse);
				$sObjectsResponse = array();
			}
		} else if(strstr($NombreArchivo,$extDebito)) {
			echo "DEBITO <br />";
			$primeraLinea = fgets($handle);
			$fechaArchivo = date(substr($primeraLinea, 23, 4)."-".substr($primeraLinea, 27, 2)."-".substr($primeraLinea, 29, 2));
		
			while(!feof($handle)) {
				$linea = fgets($handle);
				echo "linea: " . $linea . "<br />";
				
				if(strlen($linea) > 0 && (substr($linea, 0, 2) != '01' && substr($linea, 0, 2) != '09')) {
					$idSF = substr($linea, 175, 18);
					$statusPago = substr($linea, 9, 2);
					echo "     ID SF : " . $idSF . "<br />";
					echo "     Estatus: " . $statusPago . "<br />";

					//PAGO
					$fieldsPago = array(
						'Estatus__c' => $statusPago,
						'Fecha_de_procesamiento_bancario__c' => $hoy,
						'Fecha_de_generacion_de_archivo__c' => $fechaArchivo

					);
					$sObject = new SObject();
					$sObject->Id = $idSF;
					$sObject->type = 'Pagos__c';
					$sObject->fields = $fieldsPago;
					array_push($sObjectsPago, $sObject);

					//RESPONSE
					$fieldsResponse = array(
						'File__c' => $upsertResponseFile->id,
						'Response__c' => $linea
					);

					$sObject = new SObject();
					$sObject->type = 'Response__c';
					$sObject->fields = $fieldsResponse;
					array_push($sObjectsResponse, $sObject);
				}

				if(count($sObjectsPago) == 200) {
					$updateResponsePago = $mySforceConnection->update($sObjectsPago);
					unset($sObjectsPago);
					$sObjectsPago = array();
					
					echo "updateResponsePago: " . "<br />";
					print_r( $updateResponsePago);
				}

				if(count($sObjectsResponse) == 200) {
					echo "if de response 200";
					
					echo "sObjectsResponse: " . "<br />";
					print_r( $sObjectsResponse);
					
					$updateResponseResponse = $mySforceConnection->create($sObjectsResponse);
					unset($sObjectsResponse);
					$sObjectsResponse = array();
					
					echo "updateResponseResponse: " . "<br />";
					print_r( $updateResponseResponse);
				}
			}
			
			if(count($sObjectsPago) > 0) {
				$updateResponsePago = $mySforceConnection->update($sObjectsPago);
				unset($sObjectsPago);
				$sObjectsPago = array();
			}

			if(count($sObjectsResponse) > 0) {
				$updateResponseResponse = $mySforceConnection->create($sObjectsResponse);
				unset($sObjectsResponse);
				$sObjectsResponse = array();
			}
		} else if(strstr($NombreArchivo,$extAmex)) {
			echo "AMEX <br />";
			$primeraLinea = fgets($handle);
			$fechaArchivo = date(substr($primeraLinea, 61, 4)."-".substr($primeraLinea, 65, 2)."-".substr($primeraLinea, 67, 2));
			while(!feof($handle)) {
				$linea = fgets($handle);
				echo "linea: " . $linea . "<br />";
				
				if(strlen($linea) > 0 && (strpos($linea, 'TFH') === false && strpos($linea, 'TBT') === false && strpos($linea, 'TFS') === false)) {
					$idSF = substr($linea, 219, 18);
					$statusPago = substr($linea, 264, 8);
					$statusPagoLargo = substr($linea, 264, 36);
					echo "     ID SF : " . $idSF . "<br />";
					echo "     Estatus: " . $statusPago . "<br />";

					//PAGO
					$fieldsPago = array(
						'Estatus__c' => $statusPago,
						'Comentarios_EBCC__c' => $statusPagoLargo,
						'Fecha_de_procesamiento_bancario__c' => $hoy,
						'Fecha_de_generacion_de_archivo__c' => $fechaArchivo
					);
					$sObject = new SObject();
					$sObject->Id = $idSF;
					$sObject->type = 'Pagos__c';
					$sObject->fields = $fieldsPago;
					array_push($sObjectsPago, $sObject);

					//RESPONSE
					$fieldsResponse = array(
						'File__c' => $upsertResponseFile->id,
						'Response__c' => $linea
					);

					$sObject = new SObject();
					$sObject->type = 'Response__c';
					$sObject->fields = $fieldsResponse;
					array_push($sObjectsResponse, $sObject);
				}

				if(count($sObjectsPago) == 200) {
					$updateResponsePago = $mySforceConnection->update($sObjectsPago);
					unset($sObjectsPago);
					$sObjectsPago = array();
				}

				if(count($sObjectsResponse) == 200) {
					$updateResponseResponse = $mySforceConnection->create($sObjectsResponse);
					unset($sObjectsResponse);
					$sObjectsResponse = array();
				}
			}
			
			if(count($sObjectsPago) > 0) {
				$updateResponsePago = $mySforceConnection->update($sObjectsPago);
				//echo "Response SF <br />";
				//echo "<pre>";
				//print_r( $updateResponsePago);
				unset($sObjectsPago);
				$sObjectsPago = array();
			}

			if(count($sObjectsResponse) > 0) {
				$updateResponseResponse = $mySforceConnection->create($sObjectsResponse);
				unset($sObjectsResponse);
				$sObjectsResponse = array();
			}
		}

		unset($sObjectsResponse);
		unset($sObjectsFile);
		unset($sObjectsPago);

		$sObjectsFile = array();
		$sObjectsResponse = array();
		$sObjectsPago = array();
		fclose($handle);

		$old = $c_xprocesar."/".$NombreArchivo;
		$new = $c_procesados."/".$NombreArchivo;
		rename($old, $new) or die("Unable to rename $old to $new.");
		} // del if si inserte
		else{
			unset($sObjectsResponse);
			unset($sObjectsFile);
			unset($sObjectsPago);
	
			$sObjectsFile = array();
			$sObjectsResponse = array();
			$sObjectsPago = array();
			echo "Su archivo ya habia sido procesado previamente<br><br>";
			}
	} // del foreach

	exit();
} catch (Exception $e) {
	echo $e->faultstring;
}

?>