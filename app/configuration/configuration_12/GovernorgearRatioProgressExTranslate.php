<?php
/**
 * Date: 07.08.14
 * Time: 7:49
 */

namespace con12;

class GovernorgearRatioProgressExTranslate {

    public function translateCurrent($current)
    {
        return round($current / 20, 2);
    }
}