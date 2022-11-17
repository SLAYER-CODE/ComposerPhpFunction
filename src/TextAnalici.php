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

use Pdf2text\Pdf2text;

include "./Controller/Module/Pdf2Text.php";
$pdf = 'Resolucion.pdf';


//$PathDirAbsolute = "/var/www/html/ComposerProject/ArchivosPrueva/"; #Linux
$PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
$archivePdf = $PathDirAbsolute . $pdf;
function getObjectOptions($object)
{
    $options = [];

    if (preg_match("#<<(.*)>>#ismU",  $object, $options)) {
        $options = explode('/', $options[1]);
        @array_shift($options);

        $o = [];
        for ($j = 0; $j < @count($options); $j++) {
            $options[$j] = preg_replace("#\s+#", ' ', trim($options[$j]));
            if (strpos($options[$j], ' ') !== false) {
                $parts = explode(' ', $options[$j]);
                $o[$parts[0]] = $parts[1];
            } else
                $o[$options[$j]] = true;
        }
        $options = $o;
        unset($o);
    }

    return $options;
}

function decodeAsciiHex($input)
{
    $output    = '';
    $isOdd     = true;
    $isComment = false;
    $codeHigh  = -1;

    for ($i = 0; $i < strlen($input) && $input[$i] !== '>'; $i++) {
        $c = $input[$i];

        if ($isComment) {
            if ($c == '\r' || $c == '\n') {
                $isComment = false;
            }
        }

        switch ($c) {
            case '\0':
            case '\t':
            case '\r':
            case '\f':
            case '\n':
            case ' ':
                break;
            case '%':
                $isComment = true;
                break;
            default:
                $code = hexdec($c);

                if ($code === 0 && $c != '0') {
                    return '';
                }

                if ($isOdd) {
                    $codeHigh = $code;
                } else {
                    $output .= chr($codeHigh * 16 + $code);
                }

                $isOdd = !$isOdd;
                break;
        }
    }

    if ($input[$i] !== '>') {
        return '';
    }

    if ($isOdd) {
        $output .= chr($codeHigh * 16);
    }

    return $output;
}

function decodeAscii85($input)
{
    $output    = '';
    $isComment = false;
    $ords      = [];
    $state     = 0;

    for ($i = 0; $i < strlen($input) && $input[$i] !== '~'; $i++) {
        $c = $input[$i];

        if ($isComment) {
            if ($c === '\r' || $c === '\n') {
                $isComment = false;
            }
            continue;
        }

        if (
            $c === '\0' ||
            $c === '\t' ||
            $c === '\r' ||
            $c === '\f' ||
            $c === '\n' ||
            $c === ' '
        ) {
            continue;
        }

        if ($c === '%') {
            $isComment = true;
            continue;
        }

        if ($c === 'z' && $state === 0) {
            $output .= str_repeat(chr(0), 4);
            continue;
        }

        if ($c < '!' || $c > 'u') {
            return '';
        }

        $code           = ord($input[$i]) & 0xff;
        $ords[$state++] = $code - ord('!');

        if ($state === 5) {
            $state = 0;

            for ($sum = 0, $j = 0; $j < 5; $j++) {
                $sum = $sum * 85 + $ords[$j];
            }
            for ($j = 3; $j >= 0; $j--) {
                $output .= chr($sum >> ($j * 8));
            }
        }
    }

    if ($state === 1) {
        return '';
    } elseif ($state > 1) {
        for ($i = 0, $sum = 0; $i < $state; $i++) {
            $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
        }
        for ($i = 0; $i < $state - 1; $i++) {
            try {
                if (!($o = chr($sum >> ((3 - $i) * 8)))) {
                    throw new \RuntimeException('An error occurred.');
                }
                $output .= $o;
            } catch (Exception $e) {
                // Don't do anything
            }
        }
    }

    return $output;
}

/**
 * Decode Flate Method
 *
 * @param  string $data
 * @return string
 */
function decodeFlate($data)
{
    return gzuncompress($data);
}

function getDecodedStream($stream, $options)
{
    $data = '';

    if (empty($options['Filter'])) {
        $data = $stream;
    } else {
        $length  = !empty($options['Length']) ?
            $options['Length'] : strlen($stream);
        $_stream = substr($stream, 0, $length);

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'ASCIIHexDecode':
                    $_stream = decodeAsciiHex($_stream);
                    break;
                case 'ASCII85Decode':
                    $_stream = decodeAscii85($_stream);
                    break;
                case 'FlateDecode':
                    // $_stream = gzdeflate($_stream)
                    $_stream = decodeFlate($_stream);
                    break;
                default:
                    break;
            }
        }
        $data = $_stream;
    }

    return $data;
}

function getDecodeObjet($stream, $options)
{
    $data = '';
    $images = [];
}

function getTextUsingTransformations($texts, $transformations)
{
    $document = '';
    $convertQuotes = ENT_QUOTES;
    $multibyte = 4;

    for ($i = 0; $i < count($texts); $i++) {
        $isHex   = false;
        $isPlain = false;
        $hex     = '';
        $plain   = '';

        for ($j = 0; $j < strlen($texts[$i]); $j++) {
            $c = $texts[$i][$j];

            switch ($c) {
                case '<':
                    $hex     = '';
                    $isHex   = true;
                    $isPlain = false;
                    break;
                case '>':
                    $hexs = str_split($hex, $multibyte);
                    for ($k = 0; $k < count($hexs); $k++) {

                        $chex = str_pad($hexs[$k], 4, '0');
                        if (isset($transformations[$chex])) {
                            $chex = $transformations[$chex];
                        }
                        $document .= html_entity_decode('&#x' . $chex . ';');
                    }
                    $isHex = false;
                    break;
                case '(':
                    $plain   = '';
                    $isPlain = true;
                    $isHex   = false;
                    break;
                case ')':
                    $isPlain   = false;
                    $document .= $plain;
                    break;
                case '\\':
                    $c2 = $texts[$i][$j + 1];

                    if (in_array($c2, ['\\', '(', ')'])) {
                        $plain .= $c2;
                    } elseif ($c2 === 'n') {
                        $plain .= '\n';
                    } elseif ($c2 === 'r') {
                        $plain .= '\r';
                    } elseif ($c2 === 't') {
                        $plain .= '\t';
                    } elseif ($c2 === 'b') {
                        $plain .= '\b';
                    } elseif ($c2 === 'f') {
                        $plain .= '\f';
                    } elseif ($c2 >= '0' && $c2 <= '9') {
                        $oct    = preg_replace(
                            "#[^0-9]#",
                            '',
                            substr($texts[$i], $j + 1, 3)
                        );
                        $j     += strlen($oct) - 1;
                        $plain .= html_entity_decode(
                            '&#' . octdec($oct) . ';',
                            $convertQuotes
                        );
                    }
                    $j++;
                    break;

                default:
                    if ($isHex)
                        $hex .= $c;
                    elseif ($isPlain)
                        $plain .= $c;
                    break;
            }
        }
        $document .= "\n";
    }

    return $document;
}

function getDirtyTexts(&$texts, $textContainers)
{
    #Se supone que este algoritmo limpia las cadenas para poder observar el texto

    for ($j = 0; $j < count($textContainers); $j++) {
        if (preg_match_all(
            "#\[(.*)\]\s*TJ[\n|\r]#ismU",
            $textContainers[$j],
            $parts
        )) {
            $texts = array_merge($texts, [
                implode('', $parts[1])
            ]);
        } elseif (preg_match_all(
            "#T[d|w|m|f]\s*(\(.*\))\s*Tj[\n|\r]#ismU",
            $textContainers[$j],
            $parts
        )) {
            $texts = array_merge($texts, [
                implode('', $parts[1])
            ]);
        } elseif (preg_match_all(
            "#T[d|w|m|f]\s*(\[.*\])\s*Tj[\n|\r]#ismU",
            $textContainers[$j],
            $parts
        )) {
            $texts = array_merge($texts, [
                implode('', $parts[1])
            ]);
        }
        #Cada parte del array es un objeto, como un parrafo y muestra 
        #El texto se muestra pero con cordenadas para insertalo luego dentro del
        #echo "Partes";
        #echo implode("",$parts[1]);
        #print_r($parts);
    }
}

/**
 * Get Char Transformations Method.
 *
 * @param  array $transformations by reference
 * @param  string $stream
 */
function getCharTransformations(&$transformations, $stream)
{
    preg_match_all(
        "#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU",
        $stream,
        $chars,
        PREG_SET_ORDER
    );
    preg_match_all(
        "#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU",
        $stream,
        $ranges,
        PREG_SET_ORDER
    );

    for ($j = 0; $j < count($chars); $j++) {
        $count = $chars[$j][1];
        $current = explode("\n", trim($chars[$j][2]));
        for ($k = 0; $k < $count && $k < count($current); $k++) {
            if (preg_match(
                "#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is",
                trim($current[$k]),
                $map
            )) {
                $transformations[str_pad($map[1], 4, "0")] = $map[2];
            }
        }
    }
    for ($j = 0; $j < count($ranges); $j++) {
        $count = $ranges[$j][1];
        $current = explode("\n", trim($ranges[$j][2]));
        for ($k = 0; $k < $count && $k < count($current); $k++) {
            if (preg_match(
                "#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is",
                trim($current[$k]),
                $map
            )) {
                $from  = hexdec($map[1]);
                $to    = hexdec($map[2]);
                $_from = hexdec($map[3]);

                for ($m = $from, $n = 0; $m <= $to; $m++, $n++) {
                    $transformations[sprintf("%04X", $m)] =
                        sprintf("%04X", $_from + $n);
                }
            } elseif (preg_match(
                "#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU",
                trim($current[$k]),
                $map
            )) {
                $from  = hexdec($map[1]);
                $to    = hexdec($map[2]);
                $parts = preg_split("#\s+#", trim($map[3]));

                for (
                    $m = $from, $n = 0;
                    $m <= $to && $n < count($parts);
                    $m++, $n++
                ) {
                    $transformations[sprintf("%04X", $m)] =
                        sprintf("%04X", hexdec($parts[$n]));
                }
            }
        }
    }
}


echo $archivePdf;
$file = fopen($archivePdf, 'rb');
echo "<p>Mostrando Contenido</p>";

$file = @file_get_contents($archivePdf, false, null, 0);
if (empty($file)) {
    echo "El archivo se encuentra Vacio";
}
echo "<TextArea style='width:500px,height: 200px;'>";
var_dump(substr($file, 0, 200));
echo "</TextArea>";
echo "<p>Filtrando Contenido</p>";

$images = [];
$transformations = [];
$texts = [];
#Obteniendo Objetos dentro del archivo de pdf
#Filtrando por Objetos encontrados
preg_match_all("#obj#ismU", $file . 'endobj' . "\r", $objetos);
var_dump($objetos[0]);
#Filtrando 
preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismU", $file .
    'endobj' . "\r", $Contenido);

echo "<p><h2> Imprime los objetos contiene el docuemnt PDF</h2></p>";



for ($i = 0; $i < count($Contenido[0]); $i++) {
    $currentObject = $Contenido[0][$i];
    #foreach($currentObject as $item){
    echo "<p>New Item| #00xREF |-| ";
    echo substr($currentObject, 0, 300);
    echo "</p>";
    
    #Obteniendo el contenido de los objetos "STREAM"
    #Sirve para filtrar y una vez filtrado comprobamos si existe algun tipo de ojeto con datos incluidos 
    #Que sirvan de ayuda entonces si deberia continuar
    if (preg_match(
        "#stream[\n|\r](.*)endstream[\n|\r]#ismU",
        $currentObject . "endstream\r",
        $stream
    )) {
        echo "<p><h3>Se encontro " . count($stream) . "</h3></p>";
        #Este foreach es para ver el contenido que todos los streams que contiene el array
        #Pero se econtraron 2 del mismo tipo en todos los ojbetos del PDF
        #Por ello el programa simplemente seleciona el ultimo ya que ese no contiene la palabra Stream al inicio
        #Para desarollar este tipo de componentes es necesario utilizar algun tipo de desencriptador que permita
        #El desarollo de contenido por medio del PHP
        foreach ($stream as $streamOfItem) {
            echo "<p># Mostrnado Contenido # " . substr($streamOfItem, 0, 300) . "</p>";
            #Formateando la cadena

        }
        #Seleccionando la segunda  cadena sin stream y limpieando los caracteres externos;
        $stream = ltrim($stream[1]);
        #Esta funcion formatea la cadena del Objeto para ver que opciones tienen
        #Una vez creado los objetos simplemente obtenemos los parametros y los asignamos a un diccionario dentro de un array
        $options = getObjectOptions($currentObject);
        echo "Opciones: ";
        print_r($options);

        if (!(empty($options['Length1']) &&
            empty($options['Type']) )) {
            echo "<p><h1>Sin Opciones</h1></p>";
            continue;
        }


        
        unset($options['Length']);

        // echo $Texto;

        // echo $stream;

        $data = getDecodedStream($stream, $options);
        echo $data;
        $imagendata=base64_encode($data);
        //echo $imagendata;
        $out = fopen("C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\Image.png", "wb");     // ready to output anywhere
        fwrite($out,$stream);  
        
        $img = "<img src= 'data:base64, $imagendata' />";
        print($img);
        #echo "<p><h1>Creacion de codigo</h1></p>";
        #Comprueba que el dato tengo Algunos caracteres antes de continuar
        if (strlen($data)) {
            #Fintra nuevamente los caracteres para su busqueda una vez decodificado el Stream
            if (preg_match_all("#BT[\n|\r](.*)ET[\n|\r]#ismU", $data .
                'ET' . "\r", $textContainers)) {
                #Dentro de estos contenedores o bloques del array se pueden observar que cada caractere
                #tiene unas cordenadas que indican donden van los textos tambien indican el tamaño de la fuente
                #print_r($textContainers);
                $textContainers = @$textContainers[1];
                #echo "<p><h1>Imprimiendo el primer controlador</h1></p>";
                #print_r($textContainers);
                getDirtyTexts($texts, $textContainers);
            } else {
                getCharTransformations($transformations, $data);
            }
        }
        $decodedText = getTextUsingTransformations($texts, $transformations);
        echo "<p><h1>Decodificado:</h1></p>";
        $utf8Caracter = (utf8_encode($decodedText));
        echo  preg_replace('([^A-Za-z0-9 ?¿¡áéúíóñÁÉÍÓÚÑ;,:.°])', '', str_replace("\\r", " ", str_replace("\\n", " ", $utf8Caracter)));
    }
}
#Obteniendo el primer elemento del array encontrado dentro de php 
?>