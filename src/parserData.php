<?php
    class ParserPdf{
        protected $version=null;
        public function __construct(float $version=null)
        {
            $this->version = $version??1.4;
               
        }
        public function getVersion(){
            return $this->version;
        }

        public function Parser(string $content){
            
        }
        public function ContentParser(string $content){
            if(empty($content)){
                return null;
            }else{
                if(false == ($HeaderContent = strpos($content,"%PDF-"))){
                    throw new Exception("Invalid PDF data: missing %PDF header.");           
                }
                $ContentPdf=substr($content,$HeaderContent);
                $ReferencesTable=$this->DataRefCross($ContentPdf);    
            }
        }

        protected function DataRefCross(string $pdfData , int $offset = 0 , array $xref = []) : array {
            $startxrefPreg = preg_match(
                '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',$pdfData,$xref,\PREG_OFFSET_CAPTURE,$offset);
            
                if (0 == $offset) {
                    // find last startxref
                    $pregResult = preg_match_all(
                        '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
                        $pdfData, $matches,
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
            
            return [];            
        }
    }
?>