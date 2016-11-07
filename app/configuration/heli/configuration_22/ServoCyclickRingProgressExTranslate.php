<?php
/**
 * Date: 07.08.14
 * Time: 7:49
 */

namespace con22_heli;

class ServoCyclickRingProgressExTranslate {

    private $profile;

    function __construct($profile) {
        $this->profile = $profile;
    }

    public function translateCurrent($current)
    {
        $angle = (6 * $current * 1.418439716) / $this->profile[20];

        return $current . " (~" . floor($angle) . "°)";
    }
}
