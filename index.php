<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>

<?php


###############################################################################################
if(!isset($_GET['hash']) || $_GET['hash'] != md5('error414')){
	die('unknow credentials');
}

$url = isset($_GET['url']) ? $_GET['url'] : 'ee';


$file = file_get_contents($url);
if(!$file){
	die('file not found');
}

$profile = str_split($file);


try {
	$parser = new ProfileParser( $profile );
	$parsed = $parser->getParsedProfile();
}catch(Exception $e){
	die($e->getMessage());
}


echo '<table>';
	foreach($parsed as $item){
		echo '<tr>';
		echo "<td>$item[label]</td>";
		if(isset($item['min']) && $item['min'] !== NULL && $item['min'] !== ''){
			echo "<td>$item[value] ($item[min] <-> $item[max])</td>";
		}else{
			echo "<td>$item[value]</td>";
		}
		echo '</tr>';
	}
echo '</table>';
?>
</body>
