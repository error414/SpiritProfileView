<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con12_heli;

class StabiSenzivityZProgressExTranslate {
    public  function translateCurrent($current)
    {
        return (($current) / 100) + "X";
    }
}
