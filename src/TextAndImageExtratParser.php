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
    
    function secondsToTime($s)
    {
        $hours = (int)($s/60/60);
        $minutes = (int)($s/60)-$hours*60;
        $seconds = (int)$s-$hours*60*60-$minutes*60; 
        return "TIME: <strong>" . $hours.' : '.$minutes.' : '.$seconds.' :</strong>';
    }
    $start_time = microtime(true);
    $documento="odajup.pdf";
    $PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
    $parseador = new \Smalot\PdfParser\Parser();
    $doc = $parseador->parseFile($PathDirAbsolute . $documento);
    $images = $doc->getObjectsByType("XObject",'Image');
    $ocr = new TesseractOCR();
    echo "<div style='display:flex;flex-direction:horizontal'>";
    foreach( $images as $image ) {
        $imageDat= $image->getContent();
        echo '<img style="width:50%;height:50%" src="data:image/png;base64,'. base64_encode($imageDat) .'" />';
        try{    
            $ocr->imageData($imageDat,strlen($imageDat));
            echo $ocr->run();       
        }catch(Exception $e){
            echo "<p>Error:</p>";
            echo $e;
        }
    }
    echo "</div>";
    $end_time = microtime(true);  
    $duration = secondsToTime($start_time - $end_time);
    echo "<h1>$duration</h1>";
?>