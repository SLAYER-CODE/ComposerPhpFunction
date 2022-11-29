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

#PDFTOCAIRO CREATE

use NcJoes\PopplerPhp\Config;
use NcJoes\PopplerPhp\PdfToCairo;
use Smalot\PdfParser\Page;

use thiagoalessio\TesseractOCR\TesseractOCR;
#Configurando binarios para la carga de Poopler
#Config::setBinDirectory('C:\xampp7.2\htdocs\composerProject\vendor\bin\poopler');
#Configurando binarios para los directorios
$OutPutCachePath = 'C:\xampp7.2\htdocs\composerProject\storage\\';
#Config::setOutputDirectory($OutPutCachePath);

function secondsToTime($s)
{
    $h = floor($s / 3600);
    $s -= $h * 3600;
    $m = floor($s / 60);
    $s -= $m * 60;
    return $h . ':' . sprintf('%02d', $m) . ':' . sprintf('%02d', $s);
}
function printItemPrime($index, $text)
{
    echo "<div>";
    echo "<h2 class='indiceText'> $index</h2>";
    echo "<textarea class='text' rows='20' cols='10'> $text </textarea>";
    echo "</div>";
}

function RemoveElements($files)
{
    foreach ($files as $file) {
        if (is_file($file)) unlink($file);
    }
    echo "Delete Cache Updating...";
}

$start_time = microtime(true);
$parseador = new \Smalot\PdfParser\Parser();

#$PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
$PathDirAbsolute="/home/slayer/Practicas/ArchivosPrueva/"; #Linux
$VarItem = scandir($PathDirAbsolute);

$VarArrayFilters = array();
foreach ($VarItem as $item) {
    $extencion = (new SplFileInfo($item))->getExtension();
    if ($extencion == 'pdf' && !strpos($item, '$RECYCLE.BIN')) {
        array_push($VarArrayFilters, $item);
        echo "<h5>$item</h5>";
    }
}   

foreach ($VarArrayFilters as $documento) {
    if ($documento == "Resolucion.pdf") {
        try {
            $doc = $parseador->parseFile($PathDirAbsolute . $documento);
        } catch (Exception $e) {
            echo "Ubo un error $documento" . $e;
        }
        $paginas = $doc->getPages();
        $title = $doc->getDetails();
        echo "<h1>$documento</h1>";
        print_r($title);
        echo "<div class='divTextPdf'>";

        foreach ($paginas as $indice => $pagina) {
            $texto = $pagina->getText();
            $indice += 1;
            #printItemPrime($indice, $texto);
        }
        echo "</div>";
    } 
    // else {
    //     #Create Archive
    //     $cairo = new PdfToCairo($PathDirAbsolute . $documento);
    //     $pngPdf = $cairo->generateJPG();
    //     $index = 1;
    //     foreach (scandir($OutPutCachePath) as $pagina) {
    //         $extencion = (new SplFileInfo($pagina))->getExtension();
    //         if ($extencion == "jpg") {
    //             echo "$OutPutCachePath.$pagina";
    //             $texto = (new TesseractOCR($OutPutCachePath . $pagina))->run();
    //             printItemPrime($index, $texto);
    //         }
    //     }
    //     RemoveElements(glob($OutPutCachePath . "*"));
    //     #$ArchiveCache = "ArchiveCache";
    //     #mkdir($PathDirAbsolute . "pdfScaner");
    //     #exec("pdfimages $PathDirAbsolute\\$document  $PathDirAbsolute\\pdfScanner\\$ArchiveCache");
    //     #nlink($PathDirAbsolute . "pdfScaner");
    // }
}

$end_time = microtime(true);
$duration = secondsToTime($end_time - $start_time);
echo "<h1>$duration</h1>"
?>