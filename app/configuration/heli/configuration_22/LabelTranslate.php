<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con22_heli;

class LabelTranslate {


    /**
     * @param $profile
     * @param $item
     *
     * @return string
     */
    public  function translateCurrent($profile, $item)
    {
        $label = explode(':', str_replace(' ', '', $item));

        $isReversed = array(
            'R.string.servo_ch1' => 1,
            'R.string.servo_ch3' => 1,
            'R.string.right_limit' => 1,
            'R.string.left_limit' => 1,
        );

        if(isset($isReversed[$label[0]]))
        {
            return $profile[6] % 2 == 1 && $profile[6] < 71 ? $label[0] : $label[1];
        }


    }
}
