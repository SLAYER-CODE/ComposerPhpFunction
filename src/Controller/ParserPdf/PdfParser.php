<?php
    class PdfParser{
        protected $fileBinary=null;
        public function __construct(string $file)
        {
            $this->fileBinary=$file;   
        }

        public function openPDf(){
            $fileOpenPDf=file_get_contents($this->fileBinary);
            return $fileOpenPDf;
            #Open pdf abriendo contenedor para observar el tipo de contenido dentro de open pdf
            
        }

    }
?>