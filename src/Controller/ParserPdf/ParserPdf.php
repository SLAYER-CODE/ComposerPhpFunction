<?php
class ParserPdf
{
    protected $version = null;
    public function __construct(float $version = null)
    {
        $this->version = $version ?? 1.4;
    }
    public function getVersion()
    {
        return $this->version;
    }

    public function Parser(string $content)
    {
    }


    protected function DataRefCross(string $pdfData, int $offset = 0, array $xref = []): array
    {
        $startxrefPreg = preg_match(
            '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
            $pdfData,
            $xref,
            \PREG_OFFSET_CAPTURE,
            $offset
        );

        if (0 == $offset) {
            // find last startxref
            $pregResult = preg_match_all(
                '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
                $pdfData,
                $matches,
                \PREG_SET_ORDER,
                $offset
            );
            if (0 == $pregResult) {
                throw new Exception('Unable to find startxref');
            }
            $matches = array_pop($matches);
            $startxref = $matches[1];
        } elseif (strpos($pdfData, 'xref', $offset) == $offset) {
            // Already pointing at the xref table
            $startxref = $offset;
        } elseif (preg_match('/([0-9]+[\s][0-9]+[\s]obj)/i', $pdfData, $matches, \PREG_OFFSET_CAPTURE, $offset)) {
            // Cross-Reference Stream object
            $startxref = $offset;
        } elseif ($startxrefPreg) {
            // startxref found
            $startxref = $matches[1][0];
        } else {
            throw new Exception('Unable to find startxref');
        }

        if ($startxref > \strlen($pdfData)) {
            throw new Exception('Unable to find xref (PDF corrupted?)');
        }
        // check xref position
        if (strpos($pdfData, 'xref', $startxref) == $startxref) {
            // Cross-Reference
            $xref = $this->decodeXref($pdfData, $startxref, $xref);
        } else {
            // Cross-Reference Stream
            $xref = $this->decodeXrefStream($pdfData, $startxref, $xref);
        }
        if (empty($xref)) {
            throw new Exception('Unable to find xref');
        }

        return $xref;
    }

    protected function decodeXref(string $pdfData, int $startxref, array $xref = []): array
    {

        echo "<p>Entrando a decodeXref</p>";
        $startxref += 4; // 4 is the length of the word 'xref'
        // skip initial white space chars
        $offset = $startxref + strspn($pdfData, '[\0\t\n\f\r ]', $startxref);
        // initialize object number
        $obj_num = 0;
        // search for cross-reference entries or subsection
        while (preg_match('/([0-9]+)[\x20]([0-9]+)[\x20]?([nf]?)(\r\n|[\x20]?[\r\n])/', $pdfData, $matches, \PREG_OFFSET_CAPTURE, $offset) > 0) {

            if ($matches[0][1] != $offset) {
                // we are on another section
                break;
            }

            $offset += \strlen($matches[0][0]);
            if ('n' == $matches[3][0]) {
                // create unique object index: [object number]_[generation number]
                $index = $obj_num . '_' . (int) ($matches[2][0]);
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
        // get trailer data
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
                    $xref['trailer']['root'] = (int) ($matches[1]) . '_' . (int) ($matches[2]);
                }
                if (preg_match('/Encrypt[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['encrypt'] = (int) ($matches[1]) . '_' . (int) ($matches[2]);
                }
                if (preg_match('/Info[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['info'] = (int) ($matches[1]) . '_' . (int) ($matches[2]);
                }
                if (preg_match('/ID[\s]*[\[][\s]*[<]([^>]*)[>][\s]*[<]([^>]*)[>]/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['id'] = [];
                    $xref['trailer']['id'][0] = $matches[1];
                    $xref['trailer']['id'][1] = $matches[2];
                }
            }
            if (preg_match('/Prev[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
                // get previous xref

                echo "<p>Se encontro /prev dentro del trailer llamando nuevamente a getXrefData</p>";
                $xref = $this->DataRefCross($pdfData, (int) ($matches[1]), $xref);
            }
        } else {
            throw new Exception('Unable to find trailer');
        }

        return $xref;
    }

    protected function getRawObject(string $pdfData, int $offset = 0): array
    {
        echo "<p>Obteniendo la codificacion para mostrar el objeto</p>";

        $objtype = ''; // object type to be returned
        $objval = ''; // object value to be returned

        // skip initial white space chars
        #saltando los caracteres iniciales "\0\t\n\f\r "
        #Si se encuentra algo entonces agrega dentro del offset
        $offset += strspn($pdfData, $this->config->getPdfWhitespaces(), $offset);

        // get first char
        #Obtiene el primer caracter del pdfData por medio de su index por offset 
        $char = $pdfData[$offset];
        // get object type

        switch ($char) {
            case '%':  // \x25 PERCENT SIGN
                // skip comment and search for next token
                $next = strcspn($pdfData, "\r\n", $offset);
                if ($next > 0) {
                    $offset += $next;
                    #Inicializa un ciclo de recursividad si encuentra un comentario para analizar el siguiente bloque
                    return $this->getRawObject($pdfData, $offset);
                }
                break;

            case '/':  // \x2F SOLIDUS

                // name object
                $objtype = $char;
                ++$offset;
                $pregResult = preg_match(
                    '/^([^\x00\x09\x0a\x0c\x0d\x20\s\x28\x29\x3c\x3e\x5b\x5d\x7b\x7d\x2f\x25]+)/',
                    substr($pdfData, $offset, 256),
                    $matches
                );

                //echo "Mostrando Match".var_dump($matches)."Despiege";

                if (1 == $pregResult) {
                    $objval = $matches[1]; // unescaped value
                    $offset += \strlen($objval);
                }
                break;
            case '(':   // \x28 LEFT PARENTHESIS
            case ')':  // \x29 RIGHT PARENTHESIS
                // literal string object
                $objtype = $char;
                ++$offset;
                $strpos = $offset;
                #Comprueba que el primer caracter sea un ('(') el cual indica que hay una llave abierta
                if ('(' == $char) {
                    #Contador de '(' por si encuentra uno mas adelante lo logre ubicar y distinguir entre el origina o el que se encuentra dentro de este
                    $open_bracket = 1;
                    #Bucle buscador del corchete cerrado para indentificar el contenido encontrado dentro.

                    while ($open_bracket > 0) {

                        #Buscando 

                        if (!isset($pdfData[$strpos])) {
                            break;
                        }
                        $ch = $pdfData[$strpos];
                        switch ($ch) {
                            case '\\':  // REVERSE SOLIDUS (5Ch) (Backslash)
                                // skip next character
                                ++$strpos;
                                break;

                            case '(':  // LEFT PARENHESIS (28h)
                                ++$open_bracket;
                                break;

                            case ')':  // RIGHT PARENTHESIS (29h)
                                --$open_bracket;
                                break;
                        }
                        ++$strpos;
                    }
                    $objval = substr($pdfData, $offset, ($strpos - $offset - 1));
                    $offset = $strpos;
                }
                break;

            case '[':   // \x5B LEFT SQUARE BRACKET
            case ']':  // \x5D RIGHT SQUARE BRACKET
                // array object
                $objtype = $char;
                ++$offset;
                #De igual forma que el anterior comprueba que ele primer caractere no se encuentre relacionado al string
                #
                if ('[' == $char) {
                    // get array content
                    $objval = [];
                    #Luego inicialisa un bucle en el cual se encuentre dentro de lo Square bracket  un objeto de esto se encargara la misma funcion [getRawObjet]
                    #si se se encuentra retornara un array vasio y si se encuentra retornara el offset del contenido
                    do {
                        $oldOffset = $offset;
                        // get element
                        $element = $this->getRawObject($pdfData, $offset);
                        $offset = $element[2];
                        $objval[] = $element;
                    } while ((']' != $element[0]) && ($offset != $oldOffset));
                    // remove closing delimiter
                    array_pop($objval);
                }
                break;

            case '<':  // \x3C LESS-THAN SIGN
            case '>':  // \x3E GREATER-THAN SIGN
                #Basicamente es lo mismo que los dos anteriores
                #La sigueinte condicion comprueba que despues de esto este el mismo caracter > que da lo mismo a >>
                if (isset($pdfData[($offset + 1)]) && ($pdfData[($offset + 1)] == $char)) {
                    // dictionary object
                    #>>
                    $objtype = $char . $char;
                    $offset += 2;
                    if ('<' == $char) {
                        // get array content
                        $objval = [];
                        do {
                            $oldOffset = $offset;
                            // get element
                            $element = $this->getRawObject($pdfData, $offset);

                            $offset = $element[2];
                            $objval[] = $element;
                            #
                        } while (('>>' != $element[0]) && ($offset != $oldOffset));
                        // remove closing delimiter
                        array_pop($objval);
                    }
                } #Si no es verdadero entonces es una simple 
                else {
                    // hexadecimal string object
                    $objtype = $char;
                    ++$offset;
                    $pregResult = preg_match(
                        '/^([0-9A-Fa-f\x09\x0a\x0c\x0d\x20]+)>/iU',
                        substr($pdfData, $offset),
                        $matches
                    );
                    if (('<' == $char) && 1 == $pregResult) {
                        // remove white space characters
                        $objval = strtr($matches[1], $this->config->getPdfWhitespaces(), '');
                        $offset += \strlen($matches[0]);
                    } elseif (false !== ($endpos = strpos($pdfData, '>', $offset))) {
                        $offset = $endpos + 1;
                    }
                }
                break;

            default:
                #Si no se encontraron ningunolos datos anteriores verifica si el dato dentro tiene una palabra en concretamente.
                #endobj Es el final del bloque del objeto
                if ('endobj' == substr($pdfData, $offset, 6)) {
                    // indirect object
                    $objtype = 'endobj';
                    $offset += 6;
                } elseif ('null' == substr($pdfData, $offset, 4)) {
                    // null object
                    $objtype = 'null';
                    $offset += 4;
                    $objval = 'null';
                } elseif ('true' == substr($pdfData, $offset, 4)) {
                    // boolean true object
                    $objtype = 'boolean';
                    $offset += 4;
                    $objval = 'true';
                } elseif ('false' == substr($pdfData, $offset, 5)) {
                    // boolean false object
                    $objtype = 'boolean';
                    $offset += 5;
                    $objval = 'false';
                } elseif ('stream' == substr($pdfData, $offset, 6)) {
                    // start stream object
                    $objtype = 'stream';
                    $offset += 6;
                    #Filtra todos dentro del string si tiene caracteres que hacen saltar o espacios.
                    #El substr indica la cadena desde el offset y posteriormente que lo filtre en la cadena de saltos y lienas
                    if (1 == preg_match('/^([\r]?[\n])/isU', substr($pdfData, $offset), $matches)) {

                        echo "<p>Debugger:" . print_r($matches) . " </p>";

                        $offset += \strlen($matches[0]);

                        #Busca el endstream con un caracter especial al frente que se supone contienen todos los objetos que tienen un stream
                        $pregResult = preg_match(
                            '/(endstream)[\x09\x0a\x0c\x0d\x20]/isU',
                            substr($pdfData, $offset),
                            $matches,
                            \PREG_OFFSET_CAPTURE
                        );
                        echo "<p>Debugger:" . print_r($matches) . " </p>";
                        #El siguiente codigo obtiene el objval del contendor y procede a realizar un offset
                        #Es decir lo que se encuentra dentro del stream y endstream
                        if (1 == $pregResult) {
                            echo "<p>Debugger: Se encontro el endstream mostrnado desde $offset y " . $matches[0][1] . " </p>";
                            $objval = substr($pdfData, $offset, $matches[0][1]);
                            $offset += $matches[1][1];
                        }
                    }
                } elseif ('endstream' == substr($pdfData, $offset, 9)) {
                    // end stream object 
                    $objtype = 'endstream';
                    $offset += 9;

                    #Se puede dar el caso de que se encuentren los siguientes atributos dentro de un contenido de datos dado poruna tabla de referencias
                } elseif (1 == preg_match('/^([0-9]+)[\s]+([0-9]+)[\s]+R/iU', substr($pdfData, $offset, 33), $matches)) {
                    // indirect object reference
                    #Este tipo de objeto dentro de un pdf no es comun visualisarlo pero si esta presente
                    $objtype = 'objref';
                    $offset += \strlen($matches[0]);
                    $objval = (int) ($matches[1]) . '_' . (int) ($matches[2]);
                } elseif (1 == preg_match('/^([0-9]+)[\s]+([0-9]+)[\s]+obj/iU', substr($pdfData, $offset, 33), $matches)) {
                    // object start
                    #El objeto tal y como se observa se tiene que relacionar comunmente con el que se encuentra ahi
                    $objtype = 'obj';
                    $objval = (int) ($matches[1]) . '_' . (int) ($matches[2]);
                    $offset += \strlen($matches[0]);
                } elseif (($numlen = strspn($pdfData, '+-.0123456789', $offset)) > 0) {
                    // numeric object
                    #para identificar el tipo de numero que se tiene 
                    $objtype = 'numeric';
                    $objval = substr($pdfData, $offset, $numlen);
                    $offset += $numlen;
                }
                break;
        }
        #El objettype se encarga de decir que tipo de objeto es el que se encontro puede ser una variable o una cadena de stream
        #El objval es el contenido especifico que se tiene 
        #El offset es el indicador final de la cadena todo se cuenta para obtener el offset
        return [$objtype, $objval, $offset];
    }

    protected function getObjectHeaderPattern(array $objRefs): string
    {
        // consider all whitespace character (PDF specifications)
        return '/' . $objRefs[0] . $this->config->getPdfWhitespacesRegex() . $objRefs[1] . $this->config->getPdfWhitespacesRegex() . 'obj' . '/';
    }

    protected function getObjectHeaderLen(array $objRefs): int
    {
        // "4 0 obj"
        // 2 whitespaces + strlen("obj") = 5
        return 5 + \strlen($objRefs[0]) + \strlen($objRefs[1]);
    }

    protected function getObjectVal(string $pdfData, $xref, array $obj): array
    {
        if ('objref' == $obj[0]) {
            // reference to indirect object
            if (isset($this->objects[$obj[1]])) {
                // this object has been already parsed
                return $this->objects[$obj[1]];
            } elseif (isset($xref[$obj[1]])) {
                // parse new object
                $this->objects[$obj[1]] = $this->getIndirectObject($pdfData, $xref, $obj[1], $xref[$obj[1]], false);

                return $this->objects[$obj[1]];
            }
        }
        return $obj;
    }

    protected function decodeStream(string $pdfData, array $xref, array $sdic, string $stream): array
    {
        // get stream length and filters
        echo "<p>Debugger: Decodificando el stream encontrado MC2 </p>";
        #Primero obtiene la cadena entera del stream
        $slength = \strlen($stream);
        #Comprueba que esa cadena no sea menor a 0 si lo es retornara un array vacio
        if ($slength <= 0) {
            return ['', []];
        }
        #Lo sigueinte es obtener un array con una cadena de filtros
        $filters = [];
        #Recorre en un bucle de el ultimo elemento que paso por esta funcion (Esta es un diccionario)
        foreach ($sdic as $k => $v) {
            if ('/' == $v[0]) {
                #Esto comprueba que la cadena tenga un length propio y si es asi entonces continua en este bloque de codigo
                if (('Length' == $v[1]) && (isset($sdic[($k + 1)])) && ('numeric' == $sdic[($k + 1)][0])) {
                    // get declared stream length
                    #Obtiene la longitud de la cadena
                    $declength = (int) ($sdic[($k + 1)][1]);
                    #Comprueba que la longitud comprobada del stream coincida con el atributo de la cadena
                    if ($declength < $slength) {
                        #Si es asi entonces corrigelo y elimina el contenido que se le esta sumando al stream es decir esta cadena por la cual
                        #se ingreso a este bloque de codigo
                        $stream = substr($stream, 0, $declength);
                        $slength = $declength;
                    }
                #Si no se encuentre length entonces comprueba que no este el Filter si lo esta ejecuta el bloque siguiente
                } elseif (('Filter' == $v[1]) && (isset($sdic[($k + 1)]))) {
                    // resolve indirect object
                    $objval = $this->getObjectVal($pdfData, $xref, $sdic[($k + 1)]);
                    if ('/' == $objval[0]) {
                        $filters[] = $objval[1];
                        // single filter
                    } elseif ('[' == $objval[0]) {
                        // array of filters
                        foreach ($objval[1] as $flt) {
                            if ('/' == $flt[0]) {
                                $filters[] = $flt[1];
                            }
                        }
                    }
                }
            }
        }
    }

    protected function getIndirectObject(string $pdfData, array $xref, string $objRef, int $offset = 0, bool $decoding = true): array
    {

        echo "<p>Entrando a la redireccion de objetos</p>";
        /*
         * build indirect object header
         */
        // $objHeader = "[object number] [generation number] obj"
        #Esto es como el split dentro de python
        $objRefArr = explode('_', $objRef);
        #Compruba que el objeto tenga 2 datos en el array
        if (2 !== \count($objRefArr)) {
            throw new Exception('Invalid object reference for $obj.');
        }

        //Esta funcion obtiene la logitud de la cadena del objeto obtenido por la tabla de referencias para luego ser retornada como un numero entero
        $objHeaderLen = $this->getObjectHeaderLen($objRefArr);

        /*
         * check if we are in position
         */
        #Desde aca empieza la nueva funcionalidad del Pdf 23/11/22
        // ignore whitespace characters at offset
        $offset += strspn($pdfData, $this->config->getPdfWhitespaces(), $offset);
        #Obtiene la funcionalidad del offset dentro de strspn
        echo "<p>Primer Offset: $offset</p>";
        // ignore leading zeros for object number
        $offset += strspn($pdfData, '0', $offset);
        echo "<p>Segundo offset filtrando el 0: $offset con un length de : $objHeaderLen</p>";

        echo "<p>Entrando a la redireccion de objetos</p>";
        #Se tiene claro que los offsets son bytes del archivo pdf y las tablas de referencia marcan de donde empiesa un objeto

        echo "Mostrando" . substr($pdfData, $offset, $objHeaderLen) . "fin";
        #echo "Mosntradno los objetos de Header Patter $objRefArr";
        echo "<p>Despues de obtener el header" . $this->getObjectHeaderPattern($objRefArr) . "Desarollo</p>";

        #Esta funcion es para comprobar que el objeto se encuentre con sus referencias a las que se indicaron si el objeto no se encuentra 
        #entonces simplemente retorna una undefinicion null del la referencia del objeto
        if (0 == preg_match($this->getObjectHeaderPattern($objRefArr), substr($pdfData, $offset, $objHeaderLen))) {

            // an indirect reference to an undefined object shall be considered a reference to the null object
            return ['null', 'null', $offset];
        }

        /*
         * get content
         */
        // starting position of object content
        #Obteniendo el contenido despues de la referencia de tabla cruzada de offset
        $offset += $objHeaderLen;
        #Repositior para obtener los objetos
        $objContentArr = [];
        $i = 0; // object main index
        #Este es el lugar donde se decodifica el stream para obtener el objeto.
        do {
            $oldOffset = $offset;
            // get element
            #Obteniendo el objeto por el contenido RAW empesando desde el offset asignado para la cadena o stream;
            $element = $this->getRawObject($pdfData, $offset);
            $offset = $element[2];
            // decode stream using stream's dictionary information
            #decodifica la cadena una vez extraida del pdf
            #Ademas comprueba que el typo de elemento sea un stream
            #Comprueba que la cadena no este vacia
            #Tambien comprueba que  el primer elemento del array sea igual a "<<" (Esto no se entiende claro aun)
            if ($decoding && ('stream' === $element[0]) && (isset($objContentArr[($i - 1)][0])) && ('<<' === $objContentArr[($i - 1)][0])) {
                #Una vez dentro decodifica el elemento pasando los parametros de [El pdf del dato,referencias de la tabla,el ultimo contenido devuelto indice 1], y la cadena stream del objeto
               $element[3] = $this->decodeStream($pdfData, $xref, $objContentArr[($i - 1)][1], $element[1]);
            }

            #Si cumple o no las condiciones anteriores de igual forma se le asignara al objeto de contenido de arrays el elemento obtenido
            #Con los siguiente datos [typo, valor de la cadena codificada, ultimo offset de la cadena]
            #dentro de este se asigna el array obtenido por el objeto mediante la tabla de referencias y el valor decodificado
            $objContentArr[$i] = $element;
            ++$i;
        #la comprobacion dice lo siguiente
        #si no es endobj sigue buscando y si el offset es diferente al olofset sigue buscando, si es igual quiere decir que no a avansado nada
        } while (('endobj' !== $element[0]) && ($offset !== $oldOffset));
        // remove closing delimiter
        array_pop($objContentArr);

        /*
         * return raw object content
         */
        return $objContentArr;
    }

    protected function decodeXrefStream(string $pdfData, int $startxref, array $xref = []): array
    {
        // try to read Cross-Reference Stream
        $xrefobj = $this->getRawObject($pdfData, $startxref);
        $xrefcrs = $this->getIndirectObject($pdfData, $xref, $xrefobj[1], $startxref, true);
        if (!isset($xref['trailer']) || empty($xref['trailer'])) {
            // get only the last updated version
            $xref['trailer'] = [];
            $filltrailer = true;
        } else {
            $filltrailer = false;
        }
        if (!isset($xref['xref'])) {
            $xref['xref'] = [];
        }
        $valid_crs = false;
        $columns = 0;
        $predictor = null;
        $sarr = $xrefcrs[0][1];
        if (!\is_array($sarr)) {
            $sarr = [];
        }

        $wb = [];

        foreach ($sarr as $k => $v) {
            if (
                ('/' == $v[0])
                && ('Type' == $v[1])
                && (isset($sarr[($k + 1)])
                    && '/' == $sarr[($k + 1)][0]
                    && 'XRef' == $sarr[($k + 1)][1]
                )
            ) {
                $valid_crs = true;
            } elseif (('/' == $v[0]) && ('Index' == $v[1]) && (isset($sarr[($k + 1)]))) {
                // initialize list for: first object number in the subsection / number of objects
                $index_blocks = [];
                for ($m = 0; $m < \count($sarr[($k + 1)][1]); $m += 2) {
                    $index_blocks[] = [$sarr[($k + 1)][1][$m][1], $sarr[($k + 1)][1][$m + 1][1]];
                }
            } elseif (('/' == $v[0]) && ('Prev' == $v[1]) && (isset($sarr[($k + 1)]) && ('numeric' == $sarr[($k + 1)][0]))) {
                // get previous xref offset
                $prevxref = (int) ($sarr[($k + 1)][1]);
            } elseif (('/' == $v[0]) && ('W' == $v[1]) && (isset($sarr[($k + 1)]))) {
                // number of bytes (in the decoded stream) of the corresponding field
                $wb[0] = (int) ($sarr[($k + 1)][1][0][1]);
                $wb[1] = (int) ($sarr[($k + 1)][1][1][1]);
                $wb[2] = (int) ($sarr[($k + 1)][1][2][1]);
            } elseif (('/' == $v[0]) && ('DecodeParms' == $v[1]) && (isset($sarr[($k + 1)][1]))) {
                $decpar = $sarr[($k + 1)][1];
                foreach ($decpar as $kdc => $vdc) {
                    if (
                        '/' == $vdc[0]
                        && 'Columns' == $vdc[1]
                        && (isset($decpar[($kdc + 1)])
                            && 'numeric' == $decpar[($kdc + 1)][0]
                        )
                    ) {
                        $columns = (int) ($decpar[($kdc + 1)][1]);
                    } elseif (
                        '/' == $vdc[0]
                        && 'Predictor' == $vdc[1]
                        && (isset($decpar[($kdc + 1)])
                            && 'numeric' == $decpar[($kdc + 1)][0]
                        )
                    ) {
                        $predictor = (int) ($decpar[($kdc + 1)][1]);
                    }
                }
            } elseif ($filltrailer) {
                if (('/' == $v[0]) && ('Size' == $v[1]) && (isset($sarr[($k + 1)]) && ('numeric' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['size'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) && ('Root' == $v[1]) && (isset($sarr[($k + 1)]) && ('objref' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['root'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) && ('Info' == $v[1]) && (isset($sarr[($k + 1)]) && ('objref' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['info'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) && ('Encrypt' == $v[1]) && (isset($sarr[($k + 1)]) && ('objref' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['encrypt'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) && ('ID' == $v[1]) && (isset($sarr[($k + 1)]))) {
                    $xref['trailer']['id'] = [];
                    $xref['trailer']['id'][0] = $sarr[($k + 1)][1][0][1];
                    $xref['trailer']['id'][1] = $sarr[($k + 1)][1][1][1];
                }
            }
        }

        // decode data
        if ($valid_crs && isset($xrefcrs[1][3][0])) {
            if (null !== $predictor) {
                // number of bytes in a row
                $rowlen = ($columns + 1);
                // convert the stream into an array of integers
                $sdata = unpack('C*', $xrefcrs[1][3][0]);
                // split the rows
                $sdata = array_chunk($sdata, $rowlen);

                // initialize decoded array
                $ddata = [];
                // initialize first row with zeros
                $prev_row = array_fill(0, $rowlen, 0);
                // for each row apply PNG unpredictor
                foreach ($sdata as $k => $row) {
                    // initialize new row
                    $ddata[$k] = [];
                    // get PNG predictor value
                    $predictor = (10 + $row[0]);
                    // for each byte on the row
                    for ($i = 1; $i <= $columns; ++$i) {
                        // new index
                        $j = ($i - 1);
                        $row_up = $prev_row[$j];
                        if (1 == $i) {
                            $row_left = 0;
                            $row_upleft = 0;
                        } else {
                            $row_left = $row[($i - 1)];
                            $row_upleft = $prev_row[($j - 1)];
                        }
                        switch ($predictor) {
                            case 10:  // PNG prediction (on encoding, PNG None on all rows)
                                $ddata[$k][$j] = $row[$i];
                                break;

                            case 11:  // PNG prediction (on encoding, PNG Sub on all rows)
                                $ddata[$k][$j] = (($row[$i] + $row_left) & 0xff);
                                break;

                            case 12:  // PNG prediction (on encoding, PNG Up on all rows)
                                $ddata[$k][$j] = (($row[$i] + $row_up) & 0xff);
                                break;

                            case 13:  // PNG prediction (on encoding, PNG Average on all rows)
                                $ddata[$k][$j] = (($row[$i] + (($row_left + $row_up) / 2)) & 0xff);
                                break;

                            case 14:  // PNG prediction (on encoding, PNG Paeth on all rows)
                                // initial estimate
                                $p = ($row_left + $row_up - $row_upleft);
                                // distances
                                $pa = abs($p - $row_left);
                                $pb = abs($p - $row_up);
                                $pc = abs($p - $row_upleft);
                                $pmin = min($pa, $pb, $pc);
                                // return minimum distance
                                switch ($pmin) {
                                    case $pa:
                                        $ddata[$k][$j] = (($row[$i] + $row_left) & 0xff);
                                        break;

                                    case $pb:
                                        $ddata[$k][$j] = (($row[$i] + $row_up) & 0xff);
                                        break;

                                    case $pc:
                                        $ddata[$k][$j] = (($row[$i] + $row_upleft) & 0xff);
                                        break;
                                }
                                break;

                            default:  // PNG prediction (on encoding, PNG optimum)
                                throw new Exception('Unknown PNG predictor: ' . $predictor);
                        }
                    }
                    $prev_row = $ddata[$k];
                } // end for each row
                // complete decoding
            } else {
                // number of bytes in a row
                $rowlen = array_sum($wb);
                // convert the stream into an array of integers
                $sdata = unpack('C*', $xrefcrs[1][3][0]);
                // split the rows
                $ddata = array_chunk($sdata, $rowlen);
            }

            $sdata = [];

            // for every row
            foreach ($ddata as $k => $row) {
                // initialize new row
                $sdata[$k] = [0, 0, 0];
                if (0 == $wb[0]) {
                    // default type field
                    $sdata[$k][0] = 1;
                }
                $i = 0; // count bytes in the row
                // for every column
                for ($c = 0; $c < 3; ++$c) {
                    // for every byte on the column
                    for ($b = 0; $b < $wb[$c]; ++$b) {
                        if (isset($row[$i])) {
                            $sdata[$k][$c] += ($row[$i] << (($wb[$c] - 1 - $b) * 8));
                        }
                        ++$i;
                    }
                }
            }

            // fill xref
            if (isset($index_blocks)) {
                // load the first object number of the first /Index entry
                $obj_num = $index_blocks[0][0];
            } else {
                $obj_num = 0;
            }
            foreach ($sdata as $k => $row) {
                switch ($row[0]) {
                    case 0:  // (f) linked list of free objects
                        break;

                    case 1:  // (n) objects that are in use but are not compressed
                        // create unique object index: [object number]_[generation number]
                        $index = $obj_num . '_' . $row[2];
                        // check if object already exist
                        if (!isset($xref['xref'][$index])) {
                            // store object offset position
                            $xref['xref'][$index] = $row[1];
                        }
                        break;

                    case 2:  // compressed objects
                        // $row[1] = object number of the object stream in which this object is stored
                        // $row[2] = index of this object within the object stream
                        $index = $row[1] . '_0_' . $row[2];
                        $xref['xref'][$index] = -1;
                        break;

                    default:  // null objects
                        break;
                }
                ++$obj_num;
                if (isset($index_blocks)) {
                    // reduce the number of remaining objects
                    --$index_blocks[0][1];
                    if (0 == $index_blocks[0][1]) {
                        // remove the actual used /Index entry
                        array_shift($index_blocks);
                        if (0 < \count($index_blocks)) {
                            // load the first object number of the following /Index entry
                            $obj_num = $index_blocks[0][0];
                        } else {
                            // if there are no more entries, remove $index_blocks to avoid actions on an empty array
                            unset($index_blocks);
                        }
                    }
                }
            }
        } // end decoding data
        if (isset($prevxref)) {
            // get previous xref
            $xref = $this->DataRefCross($pdfData, $prevxref, $xref);
        }
        return $xref;
    }

    public function ContentParser(string $content)
    {
        if (empty($content)) {
            return null;
        } else {
            if (false === ($HeaderContent = strpos($content, "%PDF-"))) {
                throw new Exception("El Pdf es invalido no se logro encontrar Header.");
            }
            $ContentPdf = substr($content, $HeaderContent);
            $ReferencesTable = $this->DataRefCross($ContentPdf);
            
            $objects = [];
            foreach ($ReferencesTable['xref'] as $obj => $offset) {
                if (!isset($objects[$obj]) && ($offset > 0)) { 
                    # Enviando objetos como 230_0 y offset 410592 el offset es el index de la referencia 
                    # Y el 230_0 es como un id para el objeto
                    # este numero 230_0 es el numero del objeto y numero de generacion el segundo objeto, es el desplasamiento.
                    $objects[$obj] = $this->getIndirectObject($ContentPdf, $ReferencesTable, $obj, $offset, true);
                    // [Mostrando]|: Mostrnado array de objetos despues de decodicarlos
                    #var_dump($objects[$obj]);
                }
            }
            echo print("<pre>".print_r($ReferencesTable)."</pre>");
            return [$ReferencesTable, $objects];
        }
    }

}
