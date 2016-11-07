<?php
/**
 * Date: 07.08.14
 * Time: 7:49
 */

namespace con21_heli;

class GovernorThrRangeProgressExTranslate {

    public function translateCurrent($current)
    {
        return ($current * 10) . " μs";
    }
}
