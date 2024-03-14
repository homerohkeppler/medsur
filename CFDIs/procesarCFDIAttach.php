<?php 
$USERNAME='sistemas@medsur.mx';
$PASSWORD='keppler098g5u1tle7Wi8ueUUhCF0ZVyX';
$TOKEN='8g5u1tle7Wi8ueUUhCF0ZVyX';

define("SOAP_CLIENT_BASEDIR", "Toolkit20/soapclient");
require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');

try {
	$mySforceConnection = new SforcePartnerClient();
	$mySoapClient = $mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
	$loginResult = $mySforceConnection->login($USERNAME, $PASSWORD);
	
	$maxDocSize = 5 * 1024 * 1024;
	$ourDir = "origen/";
	$procesadosDir = "procesados/";

	$arrayToOrder= array();
	$dates= array();
	$names= array();

/*
	$query = "SELECT Id, Name, Body, ContentType from Attachment Where Id ='0694W00000OtrSaQAJ' ";
    $queryResult = $mySforceConnection->query($query);

    $records = $queryResult->records;
    echo "<pre>";
    	print_r($queryResult) ;

	print_r($records) ;


    print_r(base64_decode($records[0]->fields->Body));

*/


	//$document= array();
	$ourDirList = @opendir($ourDir);
	while ($CFDI = readdir($ourDirList)){
		//echo ">>>: $ourDir.$CFDI <br />";

		if (is_dir($ourDir.$CFDI)){
			//echo "directory: $CFDI <br />";
		}elseif (is_file($ourDir.$CFDI)){
				$document = new stdClass();
				




				$document->type = 'Attachment';
				$nombreSinExtension = substr($CFDI, 0, strlen($CFDI)-4); // f

				$document->fields->Name = $nombreSinExtension;
    			$document->fields->ParentId = "0014W00002GU6SFQA1";
    			//echo filesize($ourDir.$CFDI);
    			//$document->fields->BodyLength = filesize($ourDir.$CFDI);
    			//$document->fields->ContentType ="text/xml; charset=utf-8";// ".xml";
    			$document->fields->ContentType ="text/xml";// ".xml";
				//$document->fields->FileExtension = ".xml";

   				//$document->type = 'Attachment';

	    		//$document->FolderId = $ourDir; 
	    		$document->fields->Description = 'Upload of '.$CFDI;
	    		$document->fields->Body = base64_encode(file_get_contents( $ourDir.$CFDI));
	    		$response = $mySforceConnection->create(array($document), 'Document');

			  //  $response = $mySforceConnection->create(array($document));

			  	//print_r($response) ;
			  	if (isset($response[0]->success) && $response[0]->success) {
       				$mensaje = 'Success - created Document with Id '.$response[0]->id;
			    } else {
			        $mensaje =  'Error: '.$response[0]->errors[0]->message;
			    }
			    $fileMoved = rename($ourDir.$CFDI, $procesadosDir.$CFDI);
				
				?>
				<html>
					<head>
						<meta http-equiv="Refresh" content="1;url=http://infinitymedialab.com/clientes/CFDIs/procesarCFDIAttach.php">
					</head>

					<body>
					<?php
					if($fileMoved){
						echo "<pre> version con document";
						print_r($document) ;

						echo $mensaje;
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

	if ($gestor = readdir($ourDirList)) {
    	echo "Leyendo directorio...<pre>";
		while ($ourItem2 = readdir($ourDirList)){
			$dates[]=filectime($ourDir.$ourItem2);
			echo filectime($ourDir.$ourItem2);
			$names[]=$ourItem2;
			echo $ourItem2;
		}/**/
		foreach ($names as $ourItem){
			if (is_dir($ourDir.$ourItem)){
				echo "directory: $ourItem <br />";
			}elseif (is_file($ourDir.$ourItem)){
				//echo "<br> Es archivo";
			  	//$document = new stdclass();
	    		$document->fields->Name = $ourItem;
    			$document->fields->ParentId = "0014W00002GU6SFQA1";
	    		$document->FolderId = $ourDir; 
	    		//print_r($document) ;
	    		$document->fields->Description = 'Upload of '.$ourItem;
	    		//print_r($document) ;
	    		$document->fields->Body = base64_encode(file_get_contents( $ourDir.$ourItem));
	    		$document->type = 'Attachment';

			  	echo "<br>";
			  	print_r($document) ;
			  	
			    $response = $mySforceConnection->create(array($document));
			  	echo "<br>Reponse:";
			  	print_r($response) ;
			  	if (isset($response[0]->success) && $response[0]->success) {
       				echo 'Success - created Document with Id '.$response[0]->id;
			    } else {
			        echo 'Error: '.$response[0]->errors[0]->message;
			    }

	    		//closedir($gestor);
			}//elseif    
		}//foreach
	}else{
 		echo "No puede abrirse el directorio: $gestor\n";
		array_multisort($dates,SORT_DESC, $names);
	}



} catch (Exception $e) {
  echo $e->faultstring;
}
?>
