<?php
if (!isset($_FILES["imagen"])) {
	exit("No hay imagen");
}
$imagen = $_FILES["imagen"];
$ubicacionImagen = $imagen["tmp_name"];
$comando = "tesseract " . escapeshellarg($ubicacionImagen) . " stdout -l spa -c debug_file=/dev/null";
exec($comando, $textoDetectado, $codigoSalida);
if ($codigoSalida === 0) {
	echo "El texto detectado es: ";
	// Tenemos el texto como un array, podemos unirlo
	$textoComoCadena = join("\n", $textoDetectado);
	echo "<pre>";
	echo $textoComoCadena;
	echo "</pre>";
} else {
	echo "Error detectando texto. Por favor verifique que la imagen existe y que el programa de detección está instalado y es accesible desde PHP. El código de salida es: " . $codigoSalida;
}