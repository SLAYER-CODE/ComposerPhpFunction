<?php
class ParserData
{
    protected $version = null;
    protected $rawParser;
    public function __construct(float $version = null)
    {
        $this->version = $version ?? 1.4;
        $this->rawParser = new ParserPdf();
    }
    public function getVersion()
    {
        return $this->version;
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
                    echo "Debugging" . $value;
                }
                return $elements;

            case 'numeric':

                // return new ElementNumeric($value);

            case 'boolean':
                // return new ElementBoolean($value);

            case 'null':
                // return new ElementNull();

            case '(':
                // if ($date = ElementDate::parse('('.$value.')', $document)) {
                // return $date;
                // }

                // return ElementString::parse('('.$value.')', $document);

            case '<':
                // return $this->parseHeaderElement('(', ElementHexa::decode($value), $document);

            case '/':
                // return ElementName::parse('/'.$value, $document);

            case 'ojbref': // old mistake in tcpdf parser
            case 'objref':
                
                // return new ElementXRef($value, $document);

            case '[':
                $values = [];
                if (\is_array($value)) {
                    foreach ($value as $sub_element) {
                        $sub_type = $sub_element[0];
                        $sub_value = $sub_element[1];
                        // $values[] = $this->parseHeaderElement($sub_type, $sub_value, $document);
                    }
                }
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
        foreach ($data as $id => $structure) {
            $content = '';
            foreach ($structure as $position => $part) {
                if (\is_int($part)) {
                    $part = [null, null];
                }
                switch ($part[0]) {

                    case '[':
                        $elements = [];
                        print("<pre>" . print_r($part, true) . "</pre>");
                        foreach ($part[1] as $sub_element) {
                            $sub_type = $sub_element[0];
                            $sub_value = $sub_element[1];
                            $this->ParserItem($sub_type, $sub_value);
                            // $elements[] = $this->parseHeaderElement($sub_type, $sub_value, $document);

                        }

                        // $header = new Header($elements, $document);
                        break;

                    case '<<':
                        // $header = $this->parseHeader($part[1], $document);
                        break;

                    case 'stream':
                        $content = isset($part[3][0]) ? $part[3][0] : $part[1];

                        // if ($header->get('Type')->equals('ObjStm')) {
                        //     $match = [];

                        //     // Split xrefs and contents.
                        //     preg_match('/^((\d+\s+\d+\s*)*)(.*)$/s', $content, $match);
                        //     $content = $match[3];
                        //     // Extract xrefs.
                        //     $xrefs = preg_split(
                        //         '/(\d+\s+\d+\s*)/s',
                        //         $match[1],
                        //         -1,
                        //       \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE
                        //     );
                        //     $table = [];

                        //     foreach ($xrefs as $xref) {
                        //         list($id, $position) = preg_split("/\s+/", trim($xref));
                        //         $table[$position] = $id;
                        //     }

                        //     ksort($table);

                        //     $ids = array_values($table);
                        //     $positions = array_keys($table);

                        //     foreach ($positions as $index => $position) {
                        //         $id = $ids[$index].'_0';
                        //         $next_position = isset($positions[$index + 1]) ? $positions[$index + 1] : \strlen($content);
                        //         $sub_content = substr($content, $position, (int) $next_position - (int) $position);

                        //         $sub_header = Header::parse($sub_content, $document);
                        //         $object = PDFObject::factory($document, $sub_header, '', $this->config);
                        //         $this->objects[$id] = $object;
                        //     }

                        //     // It is not necessary to store this content.

                        //     return;
                        // }
                        break;

                    default:
                        // $element = $this->parseHeaderElement($part[0], $part[1], $document);

                        //     if ($element) {
                        //         $header = new Header([$element], $document);
                        //     }
                        // }
                        break;
                }
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
