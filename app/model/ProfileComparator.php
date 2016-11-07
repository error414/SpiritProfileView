<?php
namespace Model;


class ProfileComparator{
    
    private $parsedProfile1;
    private $parsedProfile2;
    private $compared;
    
    public function __construct (array $parsedProfile1, array $parsedProfile2){ 
    
        $this->parsedProfile1 = $parsedProfile1;    
        $this->parsedProfile2 = $parsedProfile2;
        
        // Debug //
        //    \Nette\Diagnostics\Debugger::barDump($this->getCompared());   
        // Debug end //
    
    }
    
    /**
    * @return array
    */
     
    public function getCompared (){
        
        $comparedArray = $this->array_diff_assoc_recursive($this->parsedProfile1,$this->parsedProfile2);

        $buff = array();
        
        foreach($comparedArray as $key => $item){
            $this->multiarrayKeys($buff, $item, $key);
        }
        
        return $buff;
    }   
    

    
    private function array_diff_assoc_recursive($array1, $array2) {
        
        $difference=array();
        
        foreach($array1 as $key => $value) {    
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if( !empty($new_diff) ){
                        $difference[$key] = $new_diff;
                    }
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }
        return $difference;
        
    }
    

    /**
     * @param $res
     * @param $ar
     * @param string $parentNode
     */
    private function multiarrayKeys(&$res, $ar, $parentNode = '') {
        $currentParentNode = $parentNode;
        foreach($ar as $key => $value) {
            if (is_array($value)) {
                $currentParentNode = $parentNode . '/' . $key;
                $this->multiarrayKeys($res, $value,  $currentParentNode);
            }else{
                $res[$currentParentNode] = $currentParentNode;
            }
        }
    }

      
}