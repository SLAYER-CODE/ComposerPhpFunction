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
#incluyendo los campos de prueva 
require "../vendor/bdk/debug/src/Debug/Autoloader.php";
require "../vendor/autoload.php";

use Com\Tecnick\Pdf\Parser\Process\Xref;
use Pdf2text\Pdf2text;

include "./Controller/Module/Pdf2Text.php";
$pdf = 'Resolucion.pdf';

$debug = new \bdk\Debug(array(
    'collect' => true,
    'output' => true,
));


#$PathDirAbsolute = "/var/www/html/ComposerProject/ArchivosPrueva/"; #Linux
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
    return gzuncompress($data,0);
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
    #SE inicialisa las variables para cada convercion de texto formada desde el inicio
    $document = '';

    $convertQuotes = ENT_QUOTES;
    $multibyte = 4;

    for ($i = 0; $i < count($texts); $i++) {

        $isHex   = false;
        $isPlain = false;
        $hex     = '';
        $plain   = '';

        #Calcula la cantidad de caracteres que tiene cada array del texto
        echo "Mapenado Array de un pdf";
        echo "<p>$texts[$i]</p>";
        for ($j = 0; $j < strlen($texts[$i]); $j++) {
            #Obtiene cada caracter de la cadena del texto
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
                        #Comprueba si el contenido de la variable esta asigando al texto transformado y si esa variable
                        #existe y no es null asignala para luego ser decodificado como texto
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
                        $j += strlen($oct) - 1;
                        $plain .= html_entity_decode(
                            '&#' . octdec($oct) . ';',
                            $convertQuotes
                        );
                    }
                    $j++;
                    break;

                default:
                    if ($isPlain)
                        $plain .= $c;
                    if ($isHex)
                        $hex .= $c;
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



#echo $archivePdf;
#file = fopen($archivePdf, 'rb');
#echo "<p>Mostrando Contenido</p>";

#Esto esta bien como se muestra
$file = file_get_contents($archivePdf);
if (empty($file)) {
    echo "El archivo se encuentra Vacio";
}

echo "<TextArea style='width:500px,height: 200px;'>";

var_dump(substr($file, 0, 200));
echo "</TextArea>";

echo "<p>Filtrando Contenido</p>";

$images = [];
$transformations = [];

#Obteniendo Objetos dentro del archivo de pdf
#Filtrando por Objetos encontrados

#preg_match_all("#obj#ismU", $file . 'endobj' . "\r", $objetos);
#echo "<p><h1>Imprimiendo los objetos encontrados</h1></p>";
//print_r($objetos[0]);

if (false === ($trimpos = strpos($file, '%PDF-'))) {
    throw new Exception('Invalid PDF data: missing %PDF header.');
}

$pdfData = substr($file, $trimpos);

$offset = 0;
#Obtieen el primer statxrefPreg encontrado sin offset es igual a 0
$startxrefPreg = preg_match(
    '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
    $pdfData,
    $matches,
    \PREG_OFFSET_CAPTURE, #Esto devuele el indice como un entero en la cadena
    $offset
);


echo "<h1>Mostrando las statxref Offset Igual a : $offset</h1>";
foreach ($matches as $match) {
    echo "<p>" . var_dump($match) . "</p>";
}
#Si desdeluego obtiene el primer resultado entonces anda a todos si el offset es igual a 0

$pregResult = preg_match_all(
    '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
    $pdfData,$matches,
    \PREG_SET_ORDER, #Ordena los arrays de modo que los que se pongan al otro lado sean sus datos correspondidos y no todos los datos 
    $offset
);


#Si no se encontraron referencias a los staxref entonces fallara
echo "<h1>Mostrando todos los staxref encontrados $offset</h1>";
foreach ($matches as $match) {
    echo "<p>" . var_dump($match) . "</p>";
}

$matches =   array_pop($matches); #extrae el ultimo array 
$startxref = $matches[1]; # extrae el segundo array que se extrajo del array
echo "<h1>La ultima referencia a startxref es $startxref el contenido tiene una longitud de ".\strlen($pdfData)."</h1>";
if ($startxref > \strlen($pdfData)) {
    throw new Exception('Unable to find xref (PDF corrupted?)');
}else{
    echo "la referencia de xref es mayor a la del documento ";
}

#El startxref es el ultimo en la fila de todos los startxref y cuando se quiere ubicar a primer xref entocnes los ubica como si fuera el primera 
#obteniend la posicion de este y por ultimo igualandolo para obtener la referencia, 
#sirve para comprobar que el startxref y el primer xref tengna las mismas referencias
echo strpos($pdfData, 'xref',$startxref);

// Sirve para eliminar los caracteres que ocupan basura al rededor de la referencia exacta al igua que stristr
$startxref+=4;
$offset=$startxref+strspn($pdfData,"\0\t\n\f\r ",$startxref);
$xref=[];
$obj_num = 0;
echo "<p>Preg_match search refereces Wile Cross References OFFSET: $offset</p>";

while (preg_match('/([0-9]+)[\x20]([0-9]+)[\x20]?([nf]?)(\r\n|[\x20]?[\r\n])/', $pdfData, $matches, \PREG_OFFSET_CAPTURE, $offset) > 0) {
                          
    print_r($matches[0]);
    if($matches[0][1]!=$offset){
        echo "Se esta en otra secccion";
        break;
    }

    echo "Procesando las referencias";
    $offset+=\strlen($matches[0][0]);
    echo "<p>".$matches[3][0]."</p>";
    if ('n' == $matches[3][0]) {
        // create unique object index: [object number]_[generation number]
        $index = $obj_num.'_'.(int) ($matches[2][0]);
        // check if object already exist
        if (!isset($xref['xref'][$index])) {
            // store object offset position
            $xref['xref'][$index] = (int) ($matches[1][0]);
        }
        ++$obj_num;
    } elseif ('f' == $matches[3][0]) {
        ++$obj_num;
    } else {
        // object number (index)
        $obj_num = (int) ($matches[1][0]);
    }
}
#imprimiendo array de referencias de los objetos

echo "<h1> Mostrando las referencias de los ojbetos 'Referencia' con el numero de objetos $obj_num </h1>";

print_r($xref);



if (preg_match('/trailer[\s]*<<(.*)>>/isU', $pdfData, $matches, \PREG_OFFSET_CAPTURE, $offset) > 0) {
    $trailer_data = $matches[1][0];
    if (!isset($xref['trailer']) || empty($xref['trailer'])) {
        // get only the last updated version
        $xref['trailer'] = [];
        // parse trailer_data
        if (preg_match('/Size[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
            $xref['trailer']['size'] = (int) ($matches[1]);
        }
        if (preg_match('/Root[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
            $xref['trailer']['root'] = (int) ($matches[1]).'_'.(int) ($matches[2]);
        }
        if (preg_match('/Encrypt[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
            $xref['trailer']['encrypt'] = (int) ($matches[1]).'_'.(int) ($matches[2]);
        }
        if (preg_match('/Info[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
            $xref['trailer']['info'] = (int) ($matches[1]).'_'.(int) ($matches[2]);
        }
        if (preg_match('/ID[\s]*[\[][\s]*[<]([^>]*)[>][\s]*[<]([^>]*)[>]/i', $trailer_data, $matches) > 0) {
            $xref['trailer']['id'] = [];
            $xref['trailer']['id'][0] = $matches[1];
            $xref['trailer']['id'][1] = $matches[2];
        }
    }
    #if (preg_match('/Prev[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
    #    // get previous xref
    #    $xref = getXrefData($pdfData, (int) ($matches[1]), $xref);
    #}
} else {
    throw new Exception('Unable to find trailer');
}

echo "<p>TrailerData: '' $trailer_data '' </p>";
print_r($xref);



echo "<h1> Mosntrado los matches de la primera referencia </h1>";
print_r($matches);
#comprueba si tiene la misma referencia entonces significa que no existe algun tipo de cross reference
echo strlen($matches[0][0]);

#Entonces una vez comprobado se empiesa a decodificar los xref 
#Se decodifican de 2 formas el primero utilizando decodexref y el segundo decodificando por stream el xref
echo "<h1> Obteniendo los objetos desde la tabla de referencias  </h1>";

$objects = [];
foreach ($xref['xref'] as $obj => $offset) {
    if (!isset($objects[$obj]) && ($offset > 0)) {
        // decode objects with positive offset
        echo  "<p>$offset</p>";
        echo  "<p>$obj</p>";

        #PASANDO AL LA INICIACION DEL OBJETO 

        #$objects[$obj] = $this->getIndirectObject($pdfData, $xref, $obj, $offset, true);

    }
}

#return [$xref, $objects];


#echo "<h1>Provando si htmlentities funciona</h1>";
#$caracteres = substr($file, 0, 300);
#echo htmlentities(utf8_encode($caracteres)); 




preg_match_all("#obj(.*)endobj#s", $file, $Contenido);
#echo "<p><h2> Mostrando contenido</p></ h2> ";
#echo "<p><h2> Imprime los objetos contiene el docuemnt PDF</h2></p>";

$conteo=0;
$Contenido = $Contenido[1];
for ($i = 0; $i < count($Contenido); $i++) {
    $texts = [];
    $currentObject = $Contenido[$i];
    #foreach($currentObject as $item){
    echo "<p>New Item| #00xREF |-| ";
    // echo $currentObject;
    echo substr($currentObject, 0, 300);
    echo "</p>";

    #Obteniendo el contenido de los objetos "STREAM"
    #Sirve para filtrar y una vez filtrado comprobamos si existe algun tipo de ojeto con datos incluidos 
    #Que sirvan de ayuda entonces si deberia continuar

    if (preg_match(
        #
        "#stream[\n|\r](.*)endstream[\n|\r]#ismU",
        $currentObject,
        $stream
    )) {
        echo "<p><h3>Se encontro7 " . ($stream[0]) . "</h3></p>";
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
            empty($options['Type']))) {
            echo "<p><h1>Sin Opciones</h1></p>";
            continue;
        }



        unset($options['Length']);

        // echo $Texto;

        // echo $stream;

        $data = getDecodedStream($stream, $options);
        echo "<p><h1>Mostrando los datos decodificados</h1></p>";

        echo $data;
        echo "<p><h1>Se termino de mostrar los datos decodificados</h1></p>";

        #$imagendata=base64_encode($data);
        //echo $imagendata;
        #$out = fopen("C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\Image.png", "wb");     // ready to output anywhere
        #fwrite($out,$stream);  
        #$img = "<img src= 'data:base64, $imagendata' />";

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
                #getDirtyTexts($texts, $textContainers);
            } else {
                getCharTransformations($transformations, $data);
            }
        }
        
        $decodedText = getTextUsingTransformations($textContainers, $transformations);
        echo "<p><h1>Decodificado:</h1></p>";
        echo "<TextArea style='width:500px,height: 200px;'>";
        echo utf8_encode($decodedText);
        echo "</TextArea>";
        echo "<p><h1>Decodificado (UTF-8):</h1></p>";

        $utf8Caracter = (utf8_encode($decodedText));
        echo  preg_replace('([^A-Za-z0-9 ?¿¡áéúíóñÁÉÍÓÚÑ;,:.°])', '', str_replace("\\r", " ", str_replace("\\n", " ", $utf8Caracter)));
    } else {
        $conteo++;
        echo "Nada relacionado" . $currentObject . "Conteo: " . $conteo;
    }
}
#Obteniendo el primer elemento del array encontrado dentro de php 
?>