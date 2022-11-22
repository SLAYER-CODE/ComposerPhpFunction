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
        }

    }
?>