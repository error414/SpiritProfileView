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
        $this->compared = $this->array_diff_assoc_recursive($parsedProfile1,$parsedProfile2);
            \Nette\Diagnostics\Debugger::barDump($parsedProfile1);
            \Nette\Diagnostics\Debugger::barDump($this->compared);
            
        // Debug end //    
    
    }
    

    private function array_diff_assoc_recursive($array1, $array2) {
        
        $difference=array();
        
        foreach($array1 as $key => $value) {    
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->array_diff_assoc_recursive($value, $array2[$key]);
                    if( !empty($new_diff) )
                        $difference[$key] = $new_diff;
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }
        return $difference;
        
    }
    
    public function getCompared (){
        
        return $this->array_diff_assoc_recursive($this->parsedProfile1,$this->parsedProfile2);
        
    }
        
       
}