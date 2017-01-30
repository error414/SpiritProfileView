<?php
if(!isset($_GET['version'])){
	die('version must be set');
}

if(!isset($_GET['mode'])){
	die('mode must be set');
}


define('SPIRIT_APP', '/Users/petrcada/Documents/android/');
define('TARGET', '../app/configuration/');
define('DIFF',      SPIRIT_APP . 'settigs-mobile/settings/src/com/spirit/DiffActivity.java');
define('PROFILE',   SPIRIT_APP . 'settigs-mobile/settings/src/com/helpers/DstabiProfile.java');
############################################################################################################

$profileFile = file_get_contents(PROFILE);

$start  = 'BUILD_' . strtoupper($_GET['mode']);
$stop   = 'BUILD_' . strtoupper($_GET['mode']) . '_END';
preg_match('/' . $start . '.*' . $stop . '/s', $profileFile , $rawProfile);

if(count($rawProfile) == 0)
{
	die('no profile mode found');
}

preg_match_all('/profileMap\.put\(\"([^\"]*)\".*new ProfileItem\(([^\,]*),([^\,]*),([^,)]*)/', $rawProfile[0] , $profileItemListBuffer);

$profileItemList = array_combine($profileItemListBuffer[1],  $profileItemListBuffer[2]);
$profileItemMin = array_map('trim', array_combine($profileItemListBuffer[1],  $profileItemListBuffer[3]));
$profileItemMax = array_map('trim', array_combine($profileItemListBuffer[1],  $profileItemListBuffer[4]));
############################################################################################################

$diffFile = file_get_contents(DIFF);
preg_match_all('/\)\)\{([^\}]*)\}/', $diffFile , $diffItemList);

unset($diffItemList[0]);
$diffItemList = $diffItemList[1];

preg_match_all('/diffItem\.getLabel\(\)\.equals\(\"([^\"]*)\"\)/', $diffFile , $diffItemNames);
unset($diffItemNames[0]);
$diffItemNames = $diffItemNames[1];

$diffItemList = array_combine($diffItemNames, $diffItemList);
unset($diffItemNames);
############################################################################################################

$configuration = array();

foreach($diffItemList as $name => $item){

	if($profileItemList[$name] == '' || ($name == 'SERVO_TYPE' && $_GET['mode'] == 'AERO'))
	{
		continue;
	}

	$configuration[$profileItemList[$name]] = array(
		'label'         => NULL,
		'name'          => $name,
		'type'          => NULL,
		'add'           => 0,
		'discount'      => 0,
		'translate'     => NULL,
		'min'           => NULL,
		'max'           => NULL,

	);

	//label
	preg_match_all('/diffItem\.setLabel\((.*)\)/', $item, $label);
	unset($label[0]);
	$label = $label[1][0];


	preg_match_all('/R\.string\.[^)]*|textSeparator/', $item, $label);
	$label = $label[0];

	$labelBuffer = array();
	foreach($label as $key => $labelItem){
		if($labelItem != 'R.string.no' && $labelItem != 'R.string.yes'){
			$labelBuffer[$key] = $labelItem;
		}
	}

	$label = $labelBuffer;


	$configuration[$profileItemList[$name]]['label'] = $label;

	$clearItem = str_replace("\n", "", $item);

	//value #########################################

	//selectBox
	if(strpos($item, 'getValueForSpinner') !== false){
		$configuration[$profileItemList[$name]]['type'] = 'select';
		//String[] values = getResources().getStringArray(R.array.position_values);
		preg_match_all('/R\.array\.[^)]*/', $item, $select);
		$select = $select[0][0];
		$configuration[$profileItemList[$name]]['select'] = $select;

	// checkBox
	}elseif(strpos($item, 'getValueForCheckBox') !== false) {
		$configuration[$profileItemList[$name]]['type'] = 'check';
		$configuration[$profileItemList[$name]]['min'] = $profileItemMin[$name];
		$configuration[$profileItemList[$name]]['max'] = $profileItemMax[$name];
	// seekbar
	}elseif(strpos($item, 'getValueInteger') !== false) {
		$configuration[$profileItemList[$name]]['min'] = $profileItemMin[$name];
		$configuration[$profileItemList[$name]]['max'] = $profileItemMax[$name];

		if(strpos($item, '+')!== false) {
			preg_match_all('/\.getValueInteger\(\) \+ ([0-9]*)\)/', $item, $add);
			unset($add[0]);
			$add = $add[1][0];


			$configuration[$profileItemList[$name]]['add'] = $add;
		}

		if(strpos($item, '-')!== false) {
			if(preg_match_all('/\.getValueInteger\(\) \- ([0-9]*)\)/', $item, $discount)) {
				unset( $discount[0] );
				$discount = $discount[1][0];


				$configuration[ $profileItemList[ $name ] ]['discount'] = $discount;
			}
		}

		if(strpos( $item, 'translate')!== false) {
			preg_match_all('/new ([^\(]*)/', $item, $translate);
			unset($translate[0]);
			$translate = $translate[1][0];

			$configuration[$profileItemList[$name]]['translate'] = $translate;
		}


		$configuration[$profileItemList[$name]]['type'] = 'seek';
	}
}

$targetDir = TARGET . $_GET['mode'] . '/configuration_'. $_GET['version'] . '/';
$targetDirPrew = TARGET . $_GET['mode'] . '/configuration_'. ($_GET['version'] - 1) . '/';


if(!is_dir($targetDir)){
	mkdir($targetDir);
}

file_put_contents($targetDir . 'configurator.php', serialize($configuration));


copy(SPIRIT_APP . '/settigs-mobile/settings/res/values/strings.xml', $targetDir . '/strings_en.xml');
copy(SPIRIT_APP . '/settigs-mobile/settings/res/values-cs/strings.xml', $targetDir . '/strings_cs.xml');


//copy($targetDirPrew . '/ServoCorrectionProgressExTranslate.php', $targetDir . '/ServoCorrectionProgressExTranslate.php');
//copy($targetDirPrew . '/StabiPichProgressExTranslate.php', $targetDir . '/StabiPichProgressExTranslate.php');
//copy($targetDirPrew . '/StabiSenzivityProgressExTranslate.php', $targetDir . '/StabiSenzivityProgressExTranslate.php');
