<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
<?php
    echo "<h1>Documentos para pruevas </h1>";
    $string= "Esta es una funcion de un binario es";
    $esdespues=strpos($string,"es");
    echo  $esdespues;
    echo substr($string,$esdespues);
?>