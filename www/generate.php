<?php


define('SPIRIT_APP', '/Users/petrcada/Documents/android-projects');

define('DIFF',      SPIRIT_APP . '/settigs-mobile/settings/src/com/spirit/DiffActivity.java');
define('PROFILE',   SPIRIT_APP . '/settigs-mobile/settings/src/com/helpers/DstabiProfile.java');
############################################################################################################

$profileFile = file_get_contents(PROFILE);
preg_match_all('/profileMap\.put\(\"([^\"]*)\".*new ProfileItem\(([^\,]*),([^\,]*),([^,)]*)/', $profileFile , $profileItemListBuffer);


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


echo(serialize($configuration));
//var_dump($configuration);




