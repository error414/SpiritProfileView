<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con10_aero;

class AilDiffProgressExTranslate {
    public  function translateCurrent($current)
    {
        return (($current  - 127) . ' %');
    }
}
