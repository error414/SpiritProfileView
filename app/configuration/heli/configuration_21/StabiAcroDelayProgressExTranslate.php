<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con21_heli;

class StabiAcroDelayProgressExTranslate {
    public  function translateCurrent($current)
    {
        return ($current / 10) . ' s';
    }
}
