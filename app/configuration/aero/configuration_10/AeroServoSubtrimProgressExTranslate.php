<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con10_aero;

class AeroServoSubtrimProgressExTranslate {
    public  function translateCurrent($current)
    {
        return ($current  - 127);
    }
}
