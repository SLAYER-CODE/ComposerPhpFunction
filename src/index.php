
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.cdnfonts.com/css/matematica" rel="stylesheet">
    <title>Document</title>
</head>
<body>
    <h1>Get Content Files 'PDF' Archives</h1>
</body>
</html>
<?php
    $start_time = microtime(true);
    include "../vendor/autoload.php";


    function secondsToTime($s)
    {
        $h = floor($s / 3600);
        $s -= $h * 3600;
        $m = floor($s / 60);
        $s -= $m * 60;
        return $h.':'.sprintf('%02d', $m).':'.sprintf('%02d', $s);
    }

    $start_time=microtime(true);    
    $parseador = new \Smalot\PdfParser\Parser();
    $PathDirAbsolute="/home/slayer/Practicas/ArchivosPrueva/";
    $VarItem = scandir($PathDirAbsolute);
    $VarArrayFilters=array();
    foreach ($VarItem as $item){
        $extencion = (new SplFileInfo($item))->getExtension();
        if($extencion == 'pdf' && !strpos($item,'$RECYCLE.BIN')) {
            array_push($VarArrayFilters,$item); 
            echo "<h5>$item</h5>";
        }
    }
 
        try{
        $doc = $parseador->parseFile($PathDirAbsolute.$documento);
        }catch (Exception $e){
            echo "Ubo un error $documento".$e;
        }
        $paginas = $doc->getPages();
        $title = $doc->getDetails();
        echo "<h1>$documento</h1>";
        print_r($title);
        echo "<div class='divTextPdf'>";
        foreach($paginas as $indice=>$pagina){
            $texto = $pagina->getText();
            $indice += 1;
            echo "<div>";
            echo "<h2 class='indiceText'> $indice</h2>";
            echo "<textarea class='text' rows='20' cols='10'> $texto </textarea>";
            echo "</div>";
        }
        echo "</div>";
    }
    $end_time = microtime(true);
    $duration= secondsToTime($end_time -$start_time);
    echo "<h1>$duration</h1>"    


    /*Add Project
        mikehaertl/php-pdftk
        "php-pdfbox/php-pdfbox"

    */
?>
