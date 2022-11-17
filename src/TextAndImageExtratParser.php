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
    use Com\Tecnick\Pdf\Parser\Parser;
    use thiagoalessio\TesseractOCR\TesseractOCR;

    $documento="Resolucion.pdf";
    $parseador = new \Smalot\PdfParser\Parser();
    $PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
    $doc = $parseador->parseFile($PathDirAbsolute . $documento);
    $images = $doc->getObjectsByType("XObject",'Image');
    $ocr = new TesseractOCR();
    foreach( $images as $image ) {
        echo $image->getContent();
        echo '<img src="data:image/jpg;base64,'. base64_encode($image->getContent()) .'" />';
        echo "Extraendo el Texto";
        #$ocr->run();       
    }

?>