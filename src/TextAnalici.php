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
    $pdf='odajup.pdf';
    $PathDirAbsolute = "C:\\xampp7.2\\htdocs\\composerProject\\ArchivosPrueva\\"; #windows
    $archivePdf= $PathDirAbsolute.$pdf;
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

    function getDecodeObjet($stream,$options){
        $data = '';
        $images = [];
        
    }

    echo $archivePdf; 
    $file = fopen($archivePdf,'rb');
    echo "<p>Mostrando Contenido</p>";
    
    $file=@file_get_contents($archivePdf,false,null,0);
    if(empty($file)){
        echo "El archivo se encuentra Vacio";
    }
    echo"<TextArea style='width:500px,height: 200px;'>";
    var_dump(substr($file,0,200));
    echo"</TextArea>";
    echo "<p>Filtrando Contenido</p>";

    $ArrayTransforamcion = [];
    $ArrayTextos=[];
    #Obteniendo Objetos dentro del archivo de pdf
    #Filtrando por Objetos encontrados
    preg_match_all("#obj#ismU",$file.'endobj'."\r",$objetos);
    var_dump($objetos[0]);    
    #Filtrando 
    preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismU", $file .
    'endobj' . "\r", $Contenido);

    echo "<p><h2> Imprime los objetos contiene el docuemnt PDF</h2></p>";
    
    for ($i = 0; $i < count($Contenido[0]); $i++) {
        $currentObject = $Contenido[0] [$i];
        #foreach($currentObject as $item){
        echo "<p>New Item| #00xREF |-| ";
        echo substr($currentObject,0,300);
        echo "</p>";
        #Obteniendo el contenido de los objetos "STREAM"
        #Sirve para filtrar y una vez filtrado comprobamos si existe algun tipo de ojeto con datos incluidos 
        #Que sirvan de ayuda entonces si deberia continuar
        if (preg_match("#stream[\n|\r](.*)endstream[\n|\r]#ismU",
        $currentObject . "endstream\r", $stream )) {
            echo "<p><h3>Se encontro ".count($stream)."</h3></p>";            
            #Este foreach es para ver el contenido que todos los streams que contiene el array
            #Pero se econtraron 2 del mismo tipo en todos los ojbetos del PDF
            #Por ello el programa simplemente seleciona el ultimo ya que ese no contiene la palabra Stream al inicio
            #Para desarollar este tipo de componentes es necesario utilizar algun tipo de desencriptador que permita
            #El desarollo de contenido por medio del PHP

            foreach($stream as $streamOfItem){
                echo "<p># Mostrnado Contenido # ".substr($streamOfItem,0,300)."</p>";
                #Formateando la cadena
                
            }
            #Seleccionando la segunda  cadena sin stream y limpieando los caracteres externos;
            $stream=ltrim($stream[1]);
            #Esta funcion formatea la cadena del Objeto para ver que opciones tienen
            #Una vez creado los objetos simplemente obtenemos los parametros y los asignamos a un diccionario dentro de un array
            $options=getObjectOptions($currentObject);
            print_r($options);

        }
    }
    #Obteniendo el primer elemento del array encontrado dentro de php 
?>