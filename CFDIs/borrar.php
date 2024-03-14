<?php
// directorio donde estan los archivos a borrar:
// no te olvides de poner la barra final
$dir = "/padrinos/borrar.php/";

$handle = opendir($directorio);
while ($file = readdir($handle))
{
   if (is_file($dir.$file))
   {
       unlink($dir.$file);
   }
}
?>