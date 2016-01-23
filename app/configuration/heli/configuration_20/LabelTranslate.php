<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */

namespace con20_heli;

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
        );

        if(isset($isReversed[$label[0]]))
        {
            return $profile[6] % 2 == 1 ? $label[0] : $label[1];
        }


    }
}
