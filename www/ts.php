<?php
$tsFile = 		 'lang_cs.ts';




$xml = simplexml_load_file($tsFile);


foreach($xml as $items){
    foreach($items->message as $message){
            $attr = $message->location;
            echo '<hr>';
	        echo md5($message->source) . "<br>";

	        if((string)$attr['filename'] != '') {
		        echo '<b>' . $message->source . '</b>';
		        echo '<hr>';
		        echo $message->oldsource;
	        }else{
	            echo $message->source;
	            
	        }

            echo '<hr>';
    }
}
