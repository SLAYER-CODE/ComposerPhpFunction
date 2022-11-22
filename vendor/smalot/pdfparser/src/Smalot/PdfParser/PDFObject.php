<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 *
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace Smalot\PdfParser;

use Smalot\PdfParser\XObject\Form;
use Smalot\PdfParser\XObject\Image;

/**
 * Class PDFObject
 */
class PDFObject
{
    const TYPE = 't';

    const OPERATOR = 'o';

    const COMMAND = 'c';

    /**
     * The recursion stack.
     *
     * @var array
     */
    public static $recursionStack = [];

    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var Header
     */
    protected $header = null;

    /**
     * @var string
     */
    protected $content = null;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Document $document,
        ?Header $header = null,
        ?string $content = null,
        ?Config $config = null
    ) {
        $this->document = $document;
        $this->header = $header ?? new Header();
        $this->content = $content;
        $this->config = $config;
    }

    public function init()
    {
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getHeader(): ?Header
    {
        return $this->header;
    }

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    /**
     * @return Element|PDFObject|Header
     */
    public function get(string $name)
    {
        return $this->header->get($name);
    }

    public function has(string $name): bool
    {
<<<<<<< HEAD
            
=======
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
        return $this->header->has($name);
    }

    public function getDetails(bool $deep = true): array
    {
        return $this->header->getDetails($deep);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function cleanContent(string $content, string $char = 'X')
    {
        $char = $char[0];
<<<<<<< HEAD
        $content = str_replace(['\\\\', '\\)', '\\('], $char . $char, $content);
=======
        $content = str_replace(['\\\\', '\\)', '\\('], $char.$char, $content);
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c

        // Remove image bloc with binary content
        preg_match_all('/\s(BI\s.*?(\sID\s).*?(\sEI))\s/s', $content, $matches, \PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $part) {
            $content = substr_replace($content, str_repeat($char, \strlen($part[0])), $part[1], \strlen($part[0]));
        }

        // Clean content in square brackets [.....]
        preg_match_all('/\[((\(.*?\)|[0-9\.\-\s]*)*)\]/s', $content, $matches, \PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, \strlen($part[0])), $part[1], \strlen($part[0]));
        }

        // Clean content in round brackets (.....)
        preg_match_all('/\((.*?)\)/s', $content, $matches, \PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, \strlen($part[0])), $part[1], \strlen($part[0]));
        }

        // Clean structure
        if ($parts = preg_split('/(<|>)/s', $content, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE)) {
            $content = '';
            $level = 0;
            foreach ($parts as $part) {
                if ('<' == $part) {
                    ++$level;
                }

                $content .= (0 == $level ? $part : str_repeat($char, \strlen($part)));

                if ('>' == $part) {
                    --$level;
                }
            }
        }

        // Clean BDC and EMC markup
        preg_match_all(
<<<<<<< HEAD
            '/(\/[A-Za-z0-9\_]*\s*' . preg_quote($char) . '*BDC)/s',
=======
            '/(\/[A-Za-z0-9\_]*\s*'.preg_quote($char).'*BDC)/s',
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
            $content,
            $matches,
            \PREG_OFFSET_CAPTURE
        );
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, \strlen($part[0])), $part[1], \strlen($part[0]));
        }

        preg_match_all('/\s(EMC)\s/s', $content, $matches, \PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, \strlen($part[0])), $part[1], \strlen($part[0]));
        }

        return $content;
    }

    public function getSectionsText(?string $content): array
    {
<<<<<<< HEAD
        #Esta seccion solo se encarga de limpiar caracteres extraños y obtener mas informacion acerca del  texto
        $sections = [];
        $content = ' ' . $content . ' ';
        $textCleaned = $this->cleanContent($content, '_');

        // Extract text blocks.
        //Desplegando el texto en bloques 
=======
        $sections = [];
        $content = ' '.$content.' ';
        $textCleaned = $this->cleanContent($content, '_');

        // Extract text blocks.
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
        if (preg_match_all('/(\sQ)?\s+BT[\s|\(|\[]+(.*?)\s*ET(\sq)?/s', $textCleaned, $matches, \PREG_OFFSET_CAPTURE)) {
            foreach ($matches[2] as $pos => $part) {
                $text = $part[0];
                if ('' === $text) {
                    continue;
                }
                $offset = $part[1];
                $section = substr($content, $offset, \strlen($text));

                // Removes BDC and EMC markup.
<<<<<<< HEAD
                $section = preg_replace('/(\/[A-Za-z0-9]+\s*<<.*?)(>>\s*BDC)(.*?)(EMC\s+)/s', '${3}', $section . ' ');

                // Add Q and q flags if detected around BT/ET.
                // @see: https://github.com/smalot/pdfparser/issues/387
                $section = trim((!empty($matches[1][$pos][0]) ? "Q\n" : '') . $section) . (!empty($matches[3][$pos][0]) ? "\nq" : '');
=======
                $section = preg_replace('/(\/[A-Za-z0-9]+\s*<<.*?)(>>\s*BDC)(.*?)(EMC\s+)/s', '${3}', $section.' ');

                // Add Q and q flags if detected around BT/ET.
                // @see: https://github.com/smalot/pdfparser/issues/387
                $section = trim((!empty($matches[1][$pos][0]) ? "Q\n" : '').$section).(!empty($matches[3][$pos][0]) ? "\nq" : '');
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c

                $sections[] = $section;
            }
        }

        // Extract 'do' commands.
<<<<<<< HEAD
        // Esto extrae los comandos del texto y activan una funcion en especial
=======
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
        if (preg_match_all('/(\/[A-Za-z0-9\.\-_]+\s+Do)\s/s', $textCleaned, $matches, \PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $part) {
                $text = $part[0];
                $offset = $part[1];
                $section = substr($content, $offset, \strlen($text));

                $sections[] = $section;
            }
        }
<<<<<<< HEAD
=======

>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
        return $sections;
    }

    private function getDefaultFont(Page $page = null): Font
    {
        $fonts = [];
        if (null !== $page) {
            $fonts = $page->getFonts();
        }

        $firstFont = $this->document->getFirstFont();
        if (null !== $firstFont) {
            $fonts[] = $firstFont;
        }

        if (\count($fonts) > 0) {
            return reset($fonts);
        }

        return new Font($this->document, null, null, $this->config);
    }

    /**
     * @throws \Exception
     */
    public function getText(?Page $page = null): string
    {
<<<<<<< HEAD
        #Esto decodifica el texto 
=======
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
        $result = '';
        $sections = $this->getSectionsText($this->content);
        $current_font = $this->getDefaultFont($page);
        $clipped_font = $current_font;

        $current_position_td = ['x' => false, 'y' => false];
        $current_position_tm = ['x' => false, 'y' => false];

<<<<<<< HEAD
        #hasta aqui no se realizo nada, ninguna modificacion en texto
        
        self::$recursionStack[] = $this->getUniqueId();
        
        #empesando a visualizar el texto en secciones
        foreach ($sections as $section) {
            echo "<p>|Iniciando</p>";
            print("<pre>".print_r($section,true) ."</pre>");
            echo "<p>Debugger: Fin de la seccion Termianndo|</p>";
            
            #Esto basicamente crear un array que inserta el commando Operador y CARACTER de cada exprecion
            #Dando un resultado como el siguiente [TYPO,OPERADOR,COMANDO]
            $commands = $this->getCommandsText($section);
            
            
            $reverse_text = false;
            $text = '';
            foreach ($commands as $command) {
                /* echo "<h2>Desarollo</h2>";
                print("<pre>".print_r($command,true) ."</pre>");
                 */
                #Esto obtiene los operadores que se encuentran del array como o y tiene un diccionario de clave valor
                
                switch ($command[self::OPERATOR]) {
                    #Comprueba si el caracter siguiente un es un operadore de reversa
=======
        self::$recursionStack[] = $this->getUniqueId();

        foreach ($sections as $section) {
            $commands = $this->getCommandsText($section);
            $reverse_text = false;
            $text = '';

            foreach ($commands as $command) {
                switch ($command[self::OPERATOR]) {
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'BMC':
                        if ('ReversedChars' == $command[self::COMMAND]) {
                            $reverse_text = true;
                        }
                        break;
<<<<<<< HEAD
                        #Indicica espaciado en el texto
                        // set character spacing
                    case 'Tc':
                        break;

                        #Mover el texto hacia el punto actual
                    // move text current point
                    // Indica para donde se movera el siguiente texto con su respectivo comando
                    case 'Td':
                        #Elimina los espacios del texto con el cual el operador esta trabajando
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
                        #Obtiene los puntos del texto
                        $y = array_pop($args);
                        $x = array_pop($args);
                        
                        #Posiciona en donde deberia estar ubicado el texto si es menor al anteriormente ubicado en la pocicion y
                        #Si es asi entonces es vertical y horizontal
=======

                    // set character spacing
                    case 'Tc':
                        break;

                    // move text current point
                    case 'Td':
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
                        $y = array_pop($args);
                        $x = array_pop($args);
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                        if (((float) $x <= 0) ||
                            (false !== $current_position_td['y'] && (float) $y < (float) ($current_position_td['y']))
                        ) {
                            // vertical offset
                            $text .= "\n";
<<<<<<< HEAD
                        } elseif (
                            false !== $current_position_td['x'] && (float) $x > (float) ($current_position_td['x']
=======
                        } elseif (false !== $current_position_td['x'] && (float) $x > (float) (
                                $current_position_td['x']
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                            )
                        ) {
                            $text .= $this->config->getHorizontalOffset();
                        }
<<<<<<< HEAD
                        
                        $current_position_td = ['x' => $x, 'y' => $y];
                        break;
                        #Establecer el interlineado del texto
                        // move text current point and set leading
=======
                        $current_position_td = ['x' => $x, 'y' => $y];
                        break;

                    // move text current point and set leading
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'TD':
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
                        $y = array_pop($args);
                        $x = array_pop($args);
                        if ((float) $y < 0) {
                            $text .= "\n";
                        } elseif ((float) $x <= 0) {
                            $text .= ' ';
                        }
                        break;

<<<<<<< HEAD
                        #Relacionado con la fuente del texto
                    case 'Tf':
                        list($id) = preg_split('/\s/s', $command[self::COMMAND]);
                        #echo "<p>Debugger: $id </p>";
=======
                    case 'Tf':
                        list($id) = preg_split('/\s/s', $command[self::COMMAND]);
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                        $id = trim($id, '/');
                        if (null !== $page) {
                            $new_font = $page->getFont($id);
                            // If an invalid font ID is given, do not update the font.
                            // This should theoretically never happen, as the PDF spec states for the Tf operator:
                            // "The specified font value shall match a resource name in the Font entry of the default resource dictionary"
                            // (https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDF32000_2008.pdf, page 435)
                            // But we want to make sure that malformed PDFs do not simply crash.
<<<<<<< HEAD
                            #Esta instruccion sirve para corregir si existe algun tipo de erro en la funete a la hora de traducir el codigo
=======
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                            if (null !== $new_font) {
                                $current_font = $new_font;
                            }
                        }
                        break;

                    case 'Q':
                        // Use clip: restore font.
                        $current_font = $clipped_font;
                        break;

                    case 'q':
                        // Use clip: save font.
                        $clipped_font = $current_font;
                        break;

                    case "'":
                    case 'Tj':
<<<<<<< HEAD
                        #No se entiende esta parte del codigo
                        $command[self::COMMAND] = [$command];
                        // no break
                    case 'TJ':

                        #Aca esta la solucion!!
                        $sub_text = $current_font->decodeText($command[self::COMMAND]);
                        $text .= $sub_text;
                        break;
                        // set leading
=======
                        $command[self::COMMAND] = [$command];
                        // no break
                    case 'TJ':
                        #Esto es lo que decodifica el texto
                        $sub_text = $current_font->decodeText($command[self::COMMAND]);
                        
                        $text .= $sub_text;
                        break;

                    // set leading
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'TL':
                        $text .= ' ';
                        break;

                    case 'Tm':
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
<<<<<<< HEAD
                            $y = array_pop($args);
=======
                        $y = array_pop($args);
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                        $x = array_pop($args);
                        if (false !== $current_position_tm['x']) {
                            $delta = abs((float) $x - (float) ($current_position_tm['x']));
                            if ($delta > 10) {
                                $text .= "\t";
                            }
                        }
                        if (false !== $current_position_tm['y']) {
                            $delta = abs((float) $y - (float) ($current_position_tm['y']));
                            if ($delta > 10) {
                                $text .= "\n";
                            }
                        }
                        $current_position_tm = ['x' => $x, 'y' => $y];
                        break;

<<<<<<< HEAD
                        // set super/subscripting text rise
                    case 'Ts':
                        break;

                        // set word spacing
                    case 'Tw':
                        break;

                        // set horizontal scaling
=======
                    // set super/subscripting text rise
                    case 'Ts':
                        break;

                    // set word spacing
                    case 'Tw':
                        break;

                    // set horizontal scaling
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'Tz':
                        $text .= "\n";
                        break;

<<<<<<< HEAD
                        // move to start of next line
=======
                    // move to start of next line
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'T*':
                        $text .= "\n";
                        break;

                    case 'Da':
                        break;

                    case 'Do':
                        if (null !== $page) {
                            $args = preg_split('/\s/s', $command[self::COMMAND]);
                            $id = trim(array_pop($args), '/ ');
                            $xobject = $page->getXObject($id);

                            // @todo $xobject could be a ElementXRef object, which would then throw an error
                            if (\is_object($xobject) && $xobject instanceof self && !\in_array($xobject->getUniqueId(), self::$recursionStack)) {
                                // Not a circular reference.
                                $text .= $xobject->getText($page);
                            }
                        }
                        break;

                    case 'rg':
                    case 'RG':
                        break;

                    case 're':
                        break;

                    case 'co':
                        break;

                    case 'cs':
                        break;

                    case 'gs':
                        break;

                    case 'en':
                        break;

                    case 'sc':
                    case 'SC':
                        break;

                    case 'g':
                    case 'G':
                        break;

                    case 'V':
                        break;

                    case 'vo':
                    case 'Vo':
                        break;

                    default:
                }
            }

            // Fix Hebrew and other reverse text oriented languages.
            // @see: https://github.com/smalot/pdfparser/issues/398
            if ($reverse_text) {
                $chars = mb_str_split($text, 1, mb_internal_encoding());
                $text = implode('', array_reverse($chars));
            }

            $result .= $text;
        }

<<<<<<< HEAD
        return $result . ' ';
=======
        return $result.' ';
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
    }

    /**
     * @throws \Exception
     */
    public function getTextArray(?Page $page = null): array
    {
        $text = [];
        $sections = $this->getSectionsText($this->content);
        $current_font = new Font($this->document, null, null, $this->config);

        foreach ($sections as $section) {
            $commands = $this->getCommandsText($section);

            foreach ($commands as $command) {
                switch ($command[self::OPERATOR]) {
<<<<<<< HEAD
                        // set character spacing
                    case 'Tc':
                        break;

                        // move text current point
                    case 'Td':
                        break;

                        // move text current point and set leading
=======
                    // set character spacing
                    case 'Tc':
                        break;

                    // move text current point
                    case 'Td':
                        break;

                    // move text current point and set leading
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'TD':
                        break;

                    case 'Tf':
                        if (null !== $page) {
                            list($id) = preg_split('/\s/s', $command[self::COMMAND]);
                            $id = trim($id, '/');
                            $current_font = $page->getFont($id);
                        }
                        break;

                    case "'":
                    case 'Tj':
                        $command[self::COMMAND] = [$command];
                        // no break
                    case 'TJ':
                        $sub_text = $current_font->decodeText($command[self::COMMAND]);
                        $text[] = $sub_text;
                        break;

<<<<<<< HEAD
                        // set leading
=======
                    // set leading
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'TL':
                        break;

                    case 'Tm':
                        break;

<<<<<<< HEAD
                        // set super/subscripting text rise
                    case 'Ts':
                        break;

                        // set word spacing
                    case 'Tw':
                        break;

                        // set horizontal scaling
=======
                    // set super/subscripting text rise
                    case 'Ts':
                        break;

                    // set word spacing
                    case 'Tw':
                        break;

                    // set horizontal scaling
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'Tz':
                        //$text .= "\n";
                        break;

<<<<<<< HEAD
                        // move to start of next line
=======
                    // move to start of next line
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                    case 'T*':
                        //$text .= "\n";
                        break;

                    case 'Da':
                        break;

                    case 'Do':
                        if (null !== $page) {
                            $args = preg_split('/\s/s', $command[self::COMMAND]);
                            $id = trim(array_pop($args), '/ ');
                            if ($xobject = $page->getXObject($id)) {
                                $text[] = $xobject->getText($page);
                            }
                        }
                        break;

                    case 'rg':
                    case 'RG':
                        break;

                    case 're':
                        break;

                    case 'co':
                        break;

                    case 'cs':
                        break;

                    case 'gs':
                        break;

                    case 'en':
                        break;

                    case 'sc':
                    case 'SC':
                        break;

                    case 'g':
                    case 'G':
                        break;

                    case 'V':
                        break;

                    case 'vo':
                    case 'Vo':
                        break;

                    default:
                }
            }
        }

        return $text;
    }

    public function getCommandsText(string $text_part, int &$offset = 0): array
    {
        $commands = $matches = [];

        while ($offset < \strlen($text_part)) {
            $offset += strspn($text_part, "\x00\x09\x0a\x0c\x0d\x20", $offset);
            $char = $text_part[$offset];

            $operator = '';
            $type = '';
            $command = false;

            switch ($char) {
                case '/':
                    $type = $char;
                    if (preg_match(
                        '/^\/([A-Z0-9\._,\+]+\s+[0-9.\-]+)\s+([A-Z]+)\s*/si',
                        substr($text_part, $offset),
                        $matches
<<<<<<< HEAD
                    )) {
=======
                    )
                    ) {
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                        $operator = $matches[2];
                        $command = $matches[1];
                        $offset += \strlen($matches[0]);
                    } elseif (preg_match(
                        '/^\/([A-Z0-9\._,\+]+)\s+([A-Z]+)\s*/si',
                        substr($text_part, $offset),
                        $matches
<<<<<<< HEAD
                    )) {
=======
                    )
                    ) {
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                        $operator = $matches[2];
                        $command = $matches[1];
                        $offset += \strlen($matches[0]);
                    }
                    break;

                case '[':
                case ']':
                    // array object
                    $type = $char;
                    if ('[' == $char) {
                        ++$offset;
                        // get elements
                        $command = $this->getCommandsText($text_part, $offset);

                        if (preg_match('/^\s*[A-Z]{1,2}\s*/si', substr($text_part, $offset), $matches)) {
                            $operator = trim($matches[0]);
                            $offset += \strlen($matches[0]);
                        }
                    } else {
                        ++$offset;
                        break;
                    }
                    break;

                case '<':
                case '>':
                    // array object
                    $type = $char;
                    ++$offset;
                    if ('<' == $char) {
                        $strpos = strpos($text_part, '>', $offset);
                        $command = substr($text_part, $offset, ($strpos - $offset));
                        $offset = $strpos + 1;
                    }

                    if (preg_match('/^\s*[A-Z]{1,2}\s*/si', substr($text_part, $offset), $matches)) {
                        $operator = trim($matches[0]);
                        $offset += \strlen($matches[0]);
                    }
                    break;

                case '(':
                case ')':
                    ++$offset;
                    $type = $char;
                    $strpos = $offset;
                    if ('(' == $char) {
                        $open_bracket = 1;
                        while ($open_bracket > 0) {
                            if (!isset($text_part[$strpos])) {
                                break;
                            }
                            $ch = $text_part[$strpos];
                            switch ($ch) {
                                case '\\':
<<<<<<< HEAD
                                    // REVERSE SOLIDUS (5Ch) (Backslash)
=======
                                 // REVERSE SOLIDUS (5Ch) (Backslash)
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                                    // skip next character
                                    ++$strpos;
                                    break;

                                case '(':
<<<<<<< HEAD
                                    // LEFT PARENHESIS (28h)
=======
                                 // LEFT PARENHESIS (28h)
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                                    ++$open_bracket;
                                    break;

                                case ')':
<<<<<<< HEAD
                                    // RIGHT PARENTHESIS (29h)
=======
                                 // RIGHT PARENTHESIS (29h)
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                                    --$open_bracket;
                                    break;
                            }
                            ++$strpos;
                        }
                        $command = substr($text_part, $offset, ($strpos - $offset - 1));
                        $offset = $strpos;

                        if (preg_match('/^\s*([A-Z\']{1,2})\s*/si', substr($text_part, $offset), $matches)) {
                            $operator = $matches[1];
                            $offset += \strlen($matches[0]);
                        }
                    }
                    break;

                default:
                    if ('ET' == substr($text_part, $offset, 2)) {
                        break;
                    } elseif (preg_match(
                        '/^\s*(?P<data>([0-9\.\-]+\s*?)+)\s+(?P<id>[A-Z]{1,3})\s*/si',
                        substr($text_part, $offset),
                        $matches
<<<<<<< HEAD
                    )) {
=======
                    )
                    ) {
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c
                        $operator = trim($matches['id']);
                        $command = trim($matches['data']);
                        $offset += \strlen($matches[0]);
                    } elseif (preg_match('/^\s*([0-9\.\-]+\s*?)+\s*/si', substr($text_part, $offset), $matches)) {
                        $type = 'n';
                        $command = trim($matches[0]);
                        $offset += \strlen($matches[0]);
                    } elseif (preg_match('/^\s*([A-Z\*]+)\s*/si', substr($text_part, $offset), $matches)) {
                        $type = '';
                        $operator = $matches[1];
                        $command = '';
                        $offset += \strlen($matches[0]);
                    }
            }

            if (false !== $command) {
                $commands[] = [
                    self::TYPE => $type,
                    self::OPERATOR => $operator,
                    self::COMMAND => $command,
                ];
            } else {
                break;
            }
        }

        return $commands;
    }

    public static function factory(
        Document $document,
        Header $header,
        ?string $content,
        ?Config $config = null
    ): self {
        switch ($header->get('Type')->getContent()) {
            case 'XObject':
                switch ($header->get('Subtype')->getContent()) {
                    case 'Image':
                        return new Image($document, $header, $config->getRetainImageContent() ? $content : null, $config);

                    case 'Form':
                        return new Form($document, $header, $content, $config);
                }

                return new self($document, $header, $content, $config);

            case 'Pages':
                return new Pages($document, $header, $content, $config);

            case 'Page':
                return new Page($document, $header, $content, $config);

            case 'Encoding':
                return new Encoding($document, $header, $content, $config);

            case 'Font':
                $subtype = $header->get('Subtype')->getContent();
<<<<<<< HEAD
                $classname = '\Smalot\PdfParser\Font\Font' . $subtype;
=======
                $classname = '\Smalot\PdfParser\Font\Font'.$subtype;
>>>>>>> 3b4623222288ae26a5e1e153ac2b58a1f5f1ca3c

                if (class_exists($classname)) {
                    return new $classname($document, $header, $content, $config);
                }

                return new Font($document, $header, $content, $config);

            default:
                return new self($document, $header, $content, $config);
        }
    }

    /**
     * Returns unique id identifying the object.
     */
    protected function getUniqueId(): string
    {
        return spl_object_hash($this);
    }
}
