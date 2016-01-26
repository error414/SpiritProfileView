<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con20_heli;

class StabiSenzivityYProgressExTranslate {
    public  function translateCurrent($current, $profile, $position, $configurator, $lang)
    {
        if($position == 0 && $profile[57] != 7){
            return $configurator->getStringById('in_transmitter', $lang);
        }


        return ($current - 100) . '%';
    }
}
