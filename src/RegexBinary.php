<?php
    $item= "Esta es una cadena en la que es necesario buscar si hay tildes";
    $string = "hola";

    #Esto es sensible a mayusculas y ademas debe iniciar con esta palabra
    if(preg_match("#^Esta#i",$item)){
        echo "Si hay esta cadena";
    }else{
        echo "No hay esta cadena";
    }

    if(preg_match("#tildes$#i",$item)){
        echo "Si hay esta cadena";
    }else{
        echo "No hay esta cadena";
    }   

    if(preg_match("#Esta[2dwqd ]#",$item)){
        echo "<p>Enviando un mensaje</p>";
    }else{
        echo "Error a la hora de realizar practicas";
    }
    // #Los metacaracteres son los siguinetes \^$.[]()?*+{}

    if(preg_match("/c[ade]na/",$item)){
        echo "Caracteres encontrados dentro de una letra";
    }else{
        echo "No se contro nada relacionado";
    }

    if(preg_match("#Esta#",$item)){
        echo "Caracteres encontrados dentro de una letra ?";
    }else{
        echo "No se contro nada relacionado";
    }

    $string = "Llegaré pronto que voy vol\rando \r andando \r";
    echo $string;
    preg_match("#(and|vol)ando#i", $string, $matches);
    foreach($matches as $item){
        echo "<p></p>";
        var_dump($item);
    }

?>