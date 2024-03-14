<!DOCTYPE HTML>

<html lang="en">
<head>

<meta charset="utf-8">
<title>Medsur CFDIs</title>
<meta name="description" content=".">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap styles -->
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<!-- Generic page styles -->
<link rel="stylesheet" href="Upload/css/style.css">
<!-- blueimp Gallery styles -->
<link rel="stylesheet" href="//blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="Upload/css/jquery.fileupload.css">
<link rel="stylesheet" href="Upload/css/jquery.fileupload-ui.css">
<!-- CSS adjustments for browsers with JavaScript disabled -->
<noscript><link rel="stylesheet" href="Upload/css/jquery.fileupload-noscript.css"></noscript>
<noscript><link rel="stylesheet" href="Upload/css/jquery.fileupload-ui-noscript.css"></noscript>



    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="bootstrap-tooltip.js"></script>
    <script type="text/javascript" src="bootstrap-confirmation.js"></script>
    
    
</head>
<body>

<div class="container">
    <h1>Medsur CFDIs</h1>

   
    <br>
   

<script language="javascript">
        
       

var Loads = new Array();
 
function loL(id){

    if(typeof (Loads[id]) == "undefined"){
        Loads[id] = 1;
        //alert("si");
		var strWindowFeatures = "location=no,height=570,width=520,scrollbars=yes,status=yes";
		//var URL = "http://infinitymedialab.net/clientes/CFDIs/" + id;
				var URL = "https://cfdi-e48c292e4419.herokuapp.com/CFDIs/" + id;

		var win = window.open(URL, "_blank", strWindowFeatures);
        // Escribes esta línea si quieres q se active en 3 seg.
        //setTimeout("delete(Loads["+id+"])",3000)
    }else{
		alert("La generacion de este archivo ya fue realizada");
	 return (false);
	 }
}





        </script>




  <h2 class="lead">Paso 2 <small class="fgc1"> Crear registros de pagos en Salesforce.</small></h2>
  
           
<a onclick="loL(this.id)" id="procesarCFDIs.php" title="procesarCFDIs.php" >
            <button class="btn btn-warning cancel" type="button"><i class="glyphicon glyphicon-upload"></i>
                <span>Procesar CFDIs en Heroku</span></button>
</a>

  
   <table><tr><td width="900">
                  
             

             
		<?php


// directorio del que esta leyendo la infomacion
$ourDir = "origen/";
$arrayToOrder= array();
$dates= array();
$names= array();

// prepare to read directory contents
$ourDirList = @opendir($ourDir);

// loop atraves de los items
if ($gestor = readdir($ourDirList)) {
    //echo "Leyendo directorio...";

	
while ($ourItem2 = readdir($ourDirList)){
	$dates[]=filectime($ourDir.$ourItem2);
	$names[]=$ourItem2;
}
 
    //closedir($gestor);
}else
 echo "No puede abrirse el directorio: $gestor\n";
array_multisort($dates,SORT_DESC, $names);
?>
<br>
    <table role="presentation" class="table table-striped">
    <tbody class="files">

<?php //$salida = array_slice($entrada, 0, 3); 	
foreach ($names as $ourItem){
//while ($ourItem = readdir($ourDirList)){
	// check if it is a directory
	if (is_dir($ourDir.$ourItem)){
		// echo "directory: $ourItem <br />";
	}elseif (is_file($ourDir.$ourItem)){
		//echo "Archivo: $ourItem <br />";
		?>
        
        
        <tr>
      	<td>
        	<?     echo "" . date("F d Y H:i:s.", filectime($ourDir.$ourItem));?>
        </td>
		<td>
        	<a href="<? echo $ourDir.$ourItem;?>" title="<? echo $ourItem;?>" download="<? echo $ourDir.$ourItem;?>" ><? echo $ourItem;?></a>
        </td>
        <td>
            <span class="size"><? echo filesize ( $ourDir.$ourItem ); ?>  b</span>
        </td>
        <td>
         	<a href="<? echo $ourDir.$ourItem;?>" title="<? echo $ourItem;?>" download="<? echo $ourDir.$ourItem;?>"><button class="btn btn-success fileinput-button" type="button"><i class="glyphicon glyphicon-upload"></i>
               
            
        </td>
    </tr>    

				
		<?php
		}else{
		echo 'Archivo:<a href="'.$ourDir.$ourItem.'"> ' .$ourItem. ' </a><br />';  echo '<a href="'.$ourDir.$ourItem.'"> ' .$ourItem. ' </a><br />'; 
		
		}// del else
}//del foreach

?>
    <tbody>
  </table>

				
	</div>    
   
               
</body>
</html>  