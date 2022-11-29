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
    include "./getHeader.php";
    include "./parserData.php";
    $PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
    #$PathDirAbsolute="/home/slayer/Practicas/ArchivosPrueva/"; #Linux
    
    
    $file = "Resolucion.pdf";
    $fileBinary=file_get_contents($PathDirAbsolute.$file);
    // echo $fileBinary
    

?>