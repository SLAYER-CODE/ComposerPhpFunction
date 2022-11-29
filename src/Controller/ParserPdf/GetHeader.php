<?php
class GetHeader{
    protected $element=null;
    public function __construct(array $element=[]){
        $this->element = $element;
    }
    public function getElement():array{
        foreach ($this->element as $name => $element ){
            
        }
        return $this->element;
    }

}


?>