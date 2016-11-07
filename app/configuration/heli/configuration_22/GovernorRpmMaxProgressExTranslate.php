<?php
/**
 * Date: 27/03/15
 * Time: 22:20
 */

namespace con22_heli;

class GovernorRpmMaxProgressExTranslate {

	public function translateCurrent($current)
    {
        return (($current * 10) + 1500) . " RPM";
    }

}
