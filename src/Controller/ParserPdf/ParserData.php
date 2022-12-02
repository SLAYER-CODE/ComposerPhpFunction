<?php

use ParserData as GlobalParserData;

class ParserData
{
    protected $version = null;
    protected $rawParser;
    protected static $formats = [
        4 => 'Y',
        6 => 'Ym',
        8 => 'Ymd',
        10 => 'YmdH',
        12 => 'YmdHi',
        14 => 'YmdHis',
        15 => 'YmdHise',
        17 => 'YmdHisO',
        18 => 'YmdHisO',
        19 => 'YmdHisO',
    ];

    public function __construct(float $version = null)
    {
        $this->version = $version ?? 1.4;
        $this->rawParser = new ParserPdf();
    }

    public function getVersion()
    {
        return $this->version;
    }
    //Decodificacion de estado de funcionalidad
    public static function decode(string $value): string
    {
        $text = '';
        $length = \strlen($value);

        if ('00' === substr($value, 0, 2)) {
            for ($i = 0; $i < $length; $i += 4) {
                $hex = substr($value, $i, 4);
                $text .= '&#' . str_pad(hexdec($hex), 4, '0', \STR_PAD_LEFT) . ';';
            }
        } else {
            for ($i = 0; $i < $length; $i += 2) {
                $hex = substr($value, $i, 2);
                $text .= \chr(hexdec($hex));
            }
        }
        $text = html_entity_decode($text, \ENT_NOQUOTES, 'UTF-8');

        return $text;
    }


    public function ParserItem(string $type, $value)
    {
        switch ($type) {
            case '<<': //null;
            case '>>':
                $elements = [];
                $count = \count($value);
                for ($position = 0; $position < $count; $position += 2) {

                    $name = $value[$position][1];
                    $type = $value[$position + 1][0];
                    $value = $value[$position + 1][1];
                    //Identificando el item una vez se pase para analizar como objeto o trailer de referencia

                    $elements[$name] = $this->ParserItem($type, $value);
                    //Elemento vacio de referencia(ParseItem)
                }
                if (\array_key_exists("Type", $elements)) {
                    $Sistemas = $elements["Type"];
                    echo $Sistemas;
                }
                return $elements;

            case 'numeric':
                return (float) $value;
            case 'boolean':
                return (bool) $value;
            case 'null':
                return null;
            case '(':
                $offset = 0;
                if (preg_match('/^\s*\(D\:(?P<name>.*?)\)/s', $value, $match)) {
                    $name = $match['name'];
                    $name = str_replace("'", '', $name);
                    $date = false;

                    // Smallest format : Y
                    // Full format     : YmdHisP
                    if (preg_match('/^\d{4}(\d{2}(\d{2}(\d{2}(\d{2}(\d{2}(Z(\d{2,4})?|[\+-]?\d{2}(\d{2})?)?)?)?)?)?)?$/', $name)) {
                        if ($pos = strpos($name, 'Z')) {
                            $name = substr($name, 0, $pos + 1);
                        } elseif (18 == \strlen($name) && preg_match('/[^\+-]0000$/', $name)) {
                            $name = substr($name, 0, -4) . '+0000';
                        }
                        $format = GlobalParserData::$formats[\strlen($name)];

                        $date = \DateTime::createFromFormat($format, $name, new \DateTimeZone('UTC'));
                    } else {
                        // special cases
                        if (preg_match('/^\d{1,2}-\d{1,2}-\d{4},?\s+\d{2}:\d{2}:\d{2}[\+-]\d{4}$/', $name)) {
                            $name = str_replace(',', '', $name);
                            $format = 'n-j-Y H:i:sO';
                            $date = \DateTime::createFromFormat($format, $name, new \DateTimeZone('UTC'));
                        }
                    }

                    if (!$date) {
                        return false;
                    }

                    $offset += strpos($value, '(D:') + \strlen($match['name']) + 4; // 1 for '(D:' and ')'

                    return $date;
                }
                return false;

                // if ($date = ElementDate::parse('('.$value.')', $document)) {
                // return $date;
                // }

                // return ElementString::parse('('.$value.')', $document);

            case '<':
                //Decodifinca el valor en hexadecimal

                return $this->ParserItem('(', GlobalParserData::decode($value));
                // return $this->parseHeaderElement('(', ElementHexa::decode($value), $document);

            case '/':
                $offset = 0;
                if (preg_match('/^\s*\/([A-Z0-9\-\+,#\.]+)/is', $value, $match)) {
                    $name = $match[1];
                    $offset += strpos($value, $name) + \strlen($name);

                    $parts = preg_split('/(#\d{2})/s', $name, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
                    $text = '';

                    foreach ($parts as $part) {
                        if (preg_match('/^#\d{2}$/', $part)) {
                            $text .= \chr(hexdec(trim($part, '#')));
                        } else {
                            $text .= $part;
                        }
                    }

                    return $text;
                }

                return false;
                // return ElementName::parse('/'.$value, $document);

            case 'ojbref': // old mistake in tcpdf parser

            case 'objref':
                return $value;
            case '[':
                $values = [];
                if (\is_array($value)) {
                    foreach ($value as $sub_element) {
                        $sub_type = $sub_element[0];
                        $sub_value = $sub_element[1];
                        // $values[] = $this->parseHeaderElement($sub_type, $sub_value, $document);
                    }
                }
                return $values;
                // return new ElementArray($values, $document);
            case 'endstream':
            case 'obj': //I don't know what it means but got my project fixed.
            case '':
                // Nothing to do with.
                return null;
            default:
                throw new \Exception('Invalid type: "' . $type . '".');
        }
    }

    public function Parser(string $content)
    {
        list($referenceTable, $data) = $this->rawParser->ContentParser($content);


        if (isset($referenceTable['trailer']['encrypt'])) {
            throw new \Exception('Secured pdf file are currently not supported.');
        }

        if (empty($data)) {
            throw new \Exception('Object list not found. Possible secured file.');
        }

        //Se guardan los datos atravez de objetos y trailers para ser utilizados mas adelante
        echo "<p>Debugger: Mostrando los datos </p>";
        #print("<pre>".print_r($data,true)."</pre>");
        //Esto itinera todos los objetos una vez parceados y los itinera para poder identificar el tipo de objeto con su respetvio offset
        # Example ['/',['[','value','offset']]]
        $header = [];
        foreach ($data as $id => $structure) {
            $content = '';
            foreach ($structure as $position => $part) {
                if (\is_int($part)) {
                    $part = [null, null];
                }
                switch ($part[0]) {

                    case '[':
                        //ParserHeader() Function
                        $elements = [];
                        print("<pre>" . print_r($part, true) . "</pre>");
                        foreach ($part[1] as $sub_element) {
                            $sub_type = $sub_element[0];
                            $sub_value = $sub_element[1];
                            $elements[] = $this->ParserItem($sub_type, $sub_value);
                        }
                        $header = $elements;
                        break;

                    case '<<':
                        // $header = $this->parseHeader($part[1], $document);
                        $elements = [];
                        $count = \count($structure);

                        for ($position = 0; $position < $count; $position += 2) {
                            $name = $structure[$position][1];
                            $type = $structure[$position + 1][0];
                            $value = $structure[$position + 1][1];
                            $elements[$name] = $this->ParserItem($type, $value,);
                        }
                        $header = $elements;
                        break;

                    case 'stream':

                        $content = isset($part[3][0]) ? $part[3][0] : $part[1];
                        if(array_key_exists("Type",$header) )
                        //Obtiene el header desde los anteriores puntos
                        if ($header->get('Type')->equals('ObjStm')) {
                            $match = [];

                            // Split xrefs and contents.
                            preg_match('/^((\d+\s+\d+\s*)*)(.*)$/s', $content, $match);
                            $content = $match[3];
                            // Extract xrefs.
                            $xrefs = preg_split(
                                '/(\d+\s+\d+\s*)/s',
                                $match[1],
                                -1,
                                \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE
                            );
                            $table = [];

                            foreach ($xrefs as $xref) {
                                list($id, $position) = preg_split("/\s+/", trim($xref));
                                $table[$position] = $id;
                            }

                            ksort($table);

                            $ids = array_values($table);
                            $positions = array_keys($table);

                            foreach ($positions as $index => $position) {
                                $id = $ids[$index] . '_0';
                                $next_position = isset($positions[$index + 1]) ? $positions[$index + 1] : \strlen($content);
                                $sub_content = substr($content, $position, (int) $next_position - (int) $position);

                                $sub_header = Header::parse($sub_content, $document);
                                $object = PDFObject::factory($document, $sub_header, '', $this->config);
                                $this->objects[$id] = $object;
                            }

                            // It is not necessary to store this content.

                            return;
                        }
                        break;

                        break;

                    default:
                        // $element = $this->parseHeaderElement($part[0], $part[1], $document);

                        //     if ($element) {
                        //         $header = new Header([$element], $document);
                        //     }
                        // }
                        break;
                }
                print("<pre>" . print_r($header, true) . "</pre>");
            }
        }
        /*             $document = new Document();
            $this->objects = [];

            foreach ($data as $id => $structure) {
                $this->parseObject($id, $structure, $document);
                unset($data[$id]);
            }
        
            $document->setTrailer($this->parseTrailer($xref['trailer'], $document));
            $document->setObjects($this->objects);

            return $document;   
 */
    }
}
