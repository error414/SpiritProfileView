<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con10_aero;

class GovernorThrRangeProgressExTranslate {
    public  function translateCurrent($current)
    {
        return (($current * 10) . " μs");
    }
}
