<?php
/**
 * Date: 07.08.14
 * Time: 7:51
 */
 
class StabiSenzivityProgressExTranslate {
    public  function translateCurrent($current)
    {
        return (($current / 100) . " X");
    }
}
