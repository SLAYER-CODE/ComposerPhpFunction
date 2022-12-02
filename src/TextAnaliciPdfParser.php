<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<!--  -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
<?php

    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 'On');

    include "./Controller/ParserPdf/GetHeader.php";
    include "./Controller/ParserPdf/ParserPdf.php";
    include "./Controller/ParserPdf/PdfParser.php";
    include "./Controller/ParserPdf/ParserData.php";
    $PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
    #$PathDirAbsolute="/home/slayer/Practicas/ArchivosPrueva/"; #Linux
    
    $file = "Resolucion.pdf";
 
    $Parser = new PdfParser($PathDirAbsolute.$file);
    $ParserData=new ParserData();
    $ParserData->Parser($Parser->openPDf());
?>