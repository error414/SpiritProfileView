<?php
/**
 * Date: 07.08.14
 * Time: 7:49
 */

namespace con13_aero;

class ServoSubtrimProgressExTranslate {

    public function translateCurrent($current)
    {
        return ($current - 127);
    }
}
