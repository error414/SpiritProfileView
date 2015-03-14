<?php
/**
 * Date: 07.08.14
 * Time: 7:50
 */

namespace con12;

class StabiPichProgressExTranslate {

    public function translateCurrent($current)
    {
        return (($current * 10) . " %");
    }
}
