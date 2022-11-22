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
    include "../vendor/autoload.php";
    use Smalot\PdfParser\Page;

    $parseador = new \Smalot\PdfParser\Parser();
    $PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
    $pdf = 'Resolucion.pdf';
    $documento = $parseador->parseFile($PathDirAbsolute.$pdf);
    $obj = $documento->getObjects();
    #foreach($obj as $item){
    #    echo "<p>Desarollo de sistemas</p>";
    #    #var_dump($item);
    #    echo "<p>Desarollo de sistemas</p>";
    #}
?>