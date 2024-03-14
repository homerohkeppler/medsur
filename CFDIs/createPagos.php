<?php

/*
define("USERNAME", "salesforce@teleton-sistemas.org.mx");
	define("PASSWORD", "MneUPGeP2304");
	define("SECURITY_TOKEN", "7iopkkvZdng4NNmew1N3VLN4");
	
	define("SOAP_CLIENT_BASEDIR", "Toolkit20/soapclient");
	require_once (SOAP_CLIENT_BASEDIR.'/SforcePartnerClient.php');

	$mySforceConnection = new SforcePartnerClient();
	$mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR.'/partner.wsdl.xml');
	$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);

*/
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


date_default_timezone_set("America/Mexico_City");
$script_tz = date_default_timezone_get();

if (strcmp($script_tz, ini_get('date.timezone'))){
    echo 'La zona horaria del script difiere de la zona horaria de la configuracion ini.';
} else {
    echo 'La zona horaria del script y la zona horaria de la configuración ini coinciden.';
}


require_once ('parametros.conf');
		

				
				
			date_default_timezone_set('GMT');
			$fecha=date("Y-m-d");

			
			if(   date("l",strtotime($fecha)) =='Friday')
			{	
				$monthViernes = date("m",strtotime($fecha));
				$sabado= date('Y-m-d', strtotime($fecha. ' + 1 days'));
				$monthSabado = date("m",strtotime($sabado));
				if($monthViernes == $monthSabado ){
					$domingo= date('Y-m-d', strtotime($fecha. ' + 2 days'));
					$monthDomingo = date("m",strtotime($domingo));
						if($monthViernes == $monthDomingo ){
							$fecha= date('Y-m-d', strtotime($fecha. ' + 2 days'));
						}else{
							$fecha= date('Y-m-d', strtotime($fecha. ' + 1 days'));						
						}
				}else{
					$fecha= date('Y-m-d', strtotime($fecha. ' + 0 days'));	
				}

			}
			
echo "<pre>";			
			
		echo $queryCuentas = "
					SELECT Id, 
					Name,
					Monto_recurrente__c,
					Tarjeta_activa1__c,
					Tarjeta_activa2__c,
					Vencimiento_mes1__c,
					Vencimiento_mes2__c,
					Vencimiento_anio1__c,
					Vencimiento_anio2__c,
					Numero_de_tarjeta1__c,
					Numero_de_tarjeta2__c,
					Banco_emisor_1__c,
					Banco_emisor_2__c,
					Banco_emisor_1__r.Name,
					Banco_emisor_2__r.Name,
					Codigo_de_seguridad1__c,
					Codigo_de_seguridad2__c,
					Tipo_de_tarjeta1__c,
					Tipo_de_tarjeta2__c,
					Forma_de_pago__c
					FROM Padrino__c
					WHERE 
					(Estatus__c = 'Activo' OR Estatus__c = 'Muy interesado') 
					
					AND 
					(Forma_de_pago__c = 'Tarjeta de Crédito' OR Forma_de_pago__c = 'Tarjeta de Débito' OR Forma_de_pago__c = 'Tarjeta de Credito' OR Forma_de_pago__c = 'Tarjeta de Debito' OR Forma_de_pago__c = 'Tarjeta Crédito' OR Forma_de_pago__c = 'Tarjeta Débito' OR Forma_de_pago__c = 'Tarjeta Credito' OR Forma_de_pago__c = 'Tarjeta Debito') AND 
					(Aportacion__c = 'Mensual' OR Aportacion__c = 'Bimestral' OR Aportacion__c = 'Trimestral' OR Aportacion__c = 'Semestral' OR Aportacion__c = 'Anual') AND 
					( Fecha_de_proximo_pago__c = NULL OR Fecha_de_proximo_pago__c <= ". $fecha . ")
					Order BY Name				";
					
	$responseCuentas = $mySforceConnection->query($queryCuentas);
	$resultCuentas = new QueryResult($responseCuentas );
	echo "<pre>
	
	<br>ResultCuentas";
	
		
	foreach ($resultCuentas->records as $registroQueryNoObj) 
		{		
			 $registroQuery = new sObject($registroQueryNoObj);
			 print_r($registroQuery);
			 
			$idCFDI = $registroQuery->Id;
			echo "<br>idCFDI:".$idCFDI;
		}
	print_r($resultCuentas);
	echo "<br><br>Size".$resultCuentas ->size ."<br>";
	
	
	
	
	$arrayPagos = array();	
	foreach ($resultCuentas ->records as $registroQueryNoObj ) {
$unaCuenta = new sObject($registroQueryNoObj);
			echo "<br><br><br><br><br>///////////importe".$importe=0 +  $unaCuenta->fields->Monto_recurrente__c;

			$fieldsCuenta = array (
			'Estatus__c' => "Pendiente",
	 		'Padrino__c' => $unaCuenta->Id,
			'Importe__c' => $importe,
			'FP__c' => $fecha,

			
	 		'Banco_emisor_b__c' => $unaCuenta->fields->Banco_emisor_1__c,
	 		'Nombre_del_banco_emisor__c' => $unaCuenta->fields->Banco_emisor_1__r->fields->Name,
	 		'Nombre_tarjetahabiente__c' => $unaCuenta->fields->Name,
	 		//'Cuenta_Bancaria__c' => $unaCuenta->Id,
	 		'Numero_de_la_tarjeta__c' => $unaCuenta->fields->Numero_de_tarjeta1__c,
	 		'Codigo_de_seguridad__c' => $unaCuenta->fields->Codigo_de_seguridad1__c,
	 		'Vencimiento_anio__c' => $unaCuenta->fields->Vencimiento_anio1__c,
	 		'Vencimiento_mes__c' => $unaCuenta->fields->Vencimiento_mes1__c,
	 		//'Fecha_de_pago__c' => $unaCuenta->Id,
	 		'Tipo_de_tarjeta__c' => $unaCuenta->fields->Tipo_de_tarjeta1__c,
	 		'Forma_de_pago__c' => $unaCuenta->fields->Forma_de_pago__c,

							/*
						  Cuenta__r.Name,
						  Cuenta__r.Nip__c,
						  Tipo_de_tarjeta__c,
						  
						  Forma_de_pago__c,
						  Estatus_p__c*/
			);
			 
			$sObject = new SObject();
			$sObject->fields = $fieldsCuenta;
			$sObject->type = 'Pagos__c';
					/*echo "<pre>";
					print_r($sObject);
					echo "</pre>";*/
			array_push($arrayPagos, $sObject);
			if(count($arrayPagos)==200){
				print_r($arrayPagos);
				$upsertResponse = $mySforceConnection->create($arrayPagos);
				print_r($upsertResponse);
				unset($arrayPagos);
				$arrayPagos = array();		

				}
		
	}
						
echo "<pre>";
print_r($arrayPagos);
			print_r($arrayPagos);
			$upsertResponse = $mySforceConnection->create($arrayPagos);
			print_r($upsertResponse);

	
			
			





exit();



} catch (Exception $e) {
  echo $e->faultstring;
}
?>
