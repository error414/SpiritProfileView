<?php


$profile = str_split(file_get_contents('/Users/petrcada/Documents/vrtulnik/spirit-system/profily/logo600-27-7-2014.4ds'));

$parser = new ProfileParser($profile);

class ProfileParser{

	private $profile = array();

	private $parsed = array();

	private $configurator;

	/**
	 * @param $profile
	 */
	public function __construct($profile){
		foreach($profile as $key => $item){
			$this->profile[$key + 1] = hexdec(bin2hex($item));
		}
		$this->configurator = new Configurator($this->profile);;
		$this->parse();
	}

	/**
	 * @throws Exception
	 */
	private function parse(){

		foreach($this->configurator->getConfigForProfile() as $position => $config) {
			$label = array_map( array( $this->configurator, 'getStringById' ), $config['label'] );
			$this->parsed[$config['name']]['label'] = join( ' ', $label );
			//var_dump( 'label: ' . join( ' ', $label ) );

			//selectBox
			if ( $config['type'] == 'select' ) {
				$this->parsed[$config['name']]['value'] = $this->configurator->getSelectText($config['select'],  $this->profile[ $position ]);
			}

			//checkBox
			if ( $config['type'] == 'check' ) {
				$this->parsed[$config['name']]['value'] = ( $this->profile[ $position ] == 49 ? $this->configurator->getStringById('yes') : $this->configurator->getStringById('no') );
			}


			//SEEK
			if ( $config['type'] == 'seek' ) {
				if ( $config['add'] != 0 ) {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ] + $config['add'];

				} elseif ( $config['discount'] != 0 ) {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ] - $config['discount'];
				} else {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ];
				}

				if ( $config['translate'] !== NULL ) {
					$this->parsed[$config['name']]['value'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['value']);
				}
			}
		}

		var_dump($this->parsed);
	}
}


/**
 * Class Configurator
 */
class Configurator{

	private $version;

	private $string = array('cs' => array(), 'en' => array());

	public function __construct($profile){
		$this->version = $profile[1] . $profile[2];
	}

	/**
	 * @return bool|mixed
	 */
	public function getConfigForProfile(){
		if(file_exists('./configuration_'.$this->version.'/configurator.php')){
			return unserialize(file_get_contents('./configuration_'.$this->version.'/configurator.php'));
		}
		throw new Exception('Source file' . './configuration_'.$this->version.'/configurator.php not exists' );
	}

	/**
	 * @param $id
	 * @param string $lang
	 *
	 * @throws Exception
	 */
	public function getStringById($id, $lang = 'cs'){
		if(count($this->string[$lang]) == 0 && file_exists('./configuration_'.$this->version.'/strings_'.$lang.'.xml')){
			$this->loadString($lang);
		}


		$clearId = str_replace('R.string.', '', $id);
		if(isset($this->string[$lang][$clearId])){
			return str_replace('\n', "", $this->string[$lang][$clearId]);
		}

		if($id = 'Separator'){
			return '->';
		}

		return '???';
	}

	/**
	 * @param $translateClass
	 *
	 * @throws Exception
	 */
	public function getTranslateClass($translateClass){
		if(file_exists('./configuration_'.$this->version.'/'.$translateClass.'.php')){
			include_once('./configuration_'.$this->version.'/'.$translateClass.'.php');
			if(class_exists($translateClass, false)){
				return new $translateClass;
			}
		}

		throw new Exception('Class ' . $translateClass . ' not found');
	}

	/**
	 * @param $name
	 * @param $value
	 * @param string $lang
	 *
	 * @throws Exception
	 */
	public function getSelectText($name, $value, $lang = 'cs'){
		if(count($this->string[$lang]) == 0 && file_exists('./configuration_'.$this->version.'/strings_'.$lang.'.xml')){
			$this->loadString($lang);
		}

		$name = str_replace('R.array.', '', $name);

		$value  = $value >= 65  ? $value - 65 : $value;

		if(isset( $this->string[$lang][$name][$value])) {
			return $this->string[ $lang ][ $name ][ $value];
		}

		throw new Exception('index ' .$name . ' and value ' .  $value . ' not found');
	}

	/**
	 * @param $lang
	 */
	private function loadString($lang){
		$xml = simplexml_load_file('./configuration_'.$this->version.'/strings_'.$lang.'.xml');
		foreach($xml->children() as $item){
			if(strpos((string)$item->attributes(), 'value') === FALSE) {
				$this->string[ $lang ][ (string) $item->attributes() ] = (string) $item;
			}else{
				foreach($item as $subItem){
					$this->string[ $lang ][ (string) $item->attributes() ][] = (string) $subItem;
				}
			}
		}
	}


}
