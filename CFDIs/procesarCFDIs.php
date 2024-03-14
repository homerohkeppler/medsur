<?php 

$USERNAME='sistemas@medsur.mx';
$PASSWORD='Noviembre2023P9d5RVAswYC8w7qTeuMp1Hze';
$TOKEN='P9d5RVAswYC8w7qTeuMp1Hze';
/*
sistemas@medsur.mx 
Agosto2023

TOKEN:
HXMMK8sPcUSMPCKBEZPVKt3XO


*/
echo "hola angel sin exith";

//exit();

define("SOAP_CLIENT_BASEDIR", "Toolkit20/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');
echo "<pre>";
try {
	$mySforceConnection = new SforcePartnerClient();
	//$mySforceConnection->setEndpoint('https://test.salesforce.com/services/Soap/c/52.0');

	$mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
	$loginResult = $mySforceConnection->login($USERNAME, $PASSWORD);

	$maxDocSize = 5 * 1024 * 1024;
	$ourDir = "origen/";
	$procesadosDir = "procesados/";

	$arrayToOrder= array();
	$dates= array();
	$names= array();
	//$document= array();
	$ourDirList = @opendir($ourDir);
	while ($CFDI = readdir($ourDirList)){
		//echo ">>>: $ourDir.$CFDI <br />";
		$nombreSinExtension = substr($CFDI, 0, strlen($CFDI)-4);
		$mensaje = "";

		if (is_dir($ourDir.$CFDI)){
			//echo "directory: $CFDI <br />";
		}elseif (is_file($ourDir.$CFDI)){
				$ContentVersion = new stdClass();
				$ContentVersion->success = TRUE;

				$ContentVersion->type = 'ContentVersion';
				$ContentVersion->fields->Title = $nombreSinExtension;
    			$ContentVersion->fields->PathOnClient = $CFDI;
    			$ContentVersion->fields->Description = 'Upload of '.$CFDI;
    			$ContentVersion->fields->ContentLocation = "S";
    			//$ContentVersion->fields->SharingOption = "A";
    			$ContentVersion->fields->FirstPublishLocationId = "0014W00002GU6SFQA1"; 
					//"0017h00000g2aqsAAA";//cuenta de sbx	 
				//"0014W00002Jep2uQAB"; // id de la cuenta Amasalud
				//"0014W00002GU6SFQA1";// id de la cuenta Medsur

				$ContentVersion->fields->VersionData = base64_encode(file_get_contents( $ourDir.$CFDI));
			 	//echo "<br>***V. sin cuenta / Esto es el content version";
				//print_r($ContentVersion) ;
				//echo "***Termina content version<br>";

				$responseContentVersion = $mySforceConnection->create(array($ContentVersion));
				
				//echo "<br>***Esto es el responseContentVersion ";
				//print_r($responseContentVersion) ;
				//echo "***Termina responseContentVersion<br>";

			
			
			
			  	if (isset($responseContentVersion[0]->success) && $responseContentVersion[0]->success) {
	       				$mensaje .= '<br>Success - created ContentVersion with Id '.$responseContentVersion[0]->id;
			    } else {
			        $mensaje .=  '<br>Error ContentVersion: '.$responseContentVersion[0]->errors[0]->message;
 			  			print_r($responseContentVersion) ;

		       		$mensaje .=  '<br>fin error: ';

		       	//	exit();

			    }
			    $fileMoved = rename($ourDir.$CFDI, $procesadosDir.$CFDI);
				
				?>
				<html>
					<head>
					<meta http-equiv="Refresh" content="1;url=http://infinitymedialab.com/clientes/CFDIs/procesarCFDIs.php">

					</head>

					<body>
						Ok
					<?php

					echo "<pre>";
					print_r($ContentVersion) ;
				    //print_r($ContentDocumentLink) ;
					echo $mensaje;

					if($fileMoved){
						

				    	echo '<br>Archivo movido con exito a procesados!
				    		  <br>Continuaremos procesando los archivos...';

					}

					?>
					</body>
					</html>
				<?php
				exit();

		}	
	}
	exit();





} catch (Exception $e) {
  echo $e->faultstring;
}
?>
