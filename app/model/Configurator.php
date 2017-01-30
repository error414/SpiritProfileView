<?php
/**
 * Date: 08.08.14
 * Time: 9:11
 */

namespace Model;

class Configurator{

	const HELI = 'heli';
	const AERO = 'aero';

	private $version;
	private $versionDir;
	private $humanReadVersion;
	private $mode = self::HELI;

	private $string = array('cs' => array(), 'en' => array());

	private $profile;

	/**
	 * @param $profile
	 */
	public function __construct($profile){
		if($profile[1] > 127)
		{
			$profile[1] = $profile[1] - 127;
			$this->mode = self::AERO;
		}
		//new version
		if((count($profile) >= 63 && $profile[63] > 0) || ($profile[1] > 1)){
			$this->version          		 = $profile[1] . $profile[63];
			$this->humanReadVersion          = $this->mode . '-' . $profile[1] . '.' . $profile[63];

			if($profile[2] < 128){
				$this->humanReadVersion .= '.' . $profile[2];
			}elseif($profile[2] < 220){
				$this->humanReadVersion .= '-beta' . ($profile[2] - 128);
			}else{
				$this->humanReadVersion .= '-rc' . ($profile[2] - 220);
			}
		}else{
			//special version 1.1.0
			if($profile[1] == 1 && $profile[2] == 1 && $profile[63] == 0){
				$this->version          		 = $profile[1] . $profile[2];
				$this->humanReadVersion = $this->mode . '-' . $profile[1] . '.'.$profile[2].'.' . $profile[63];
			}else{
				$this->version          = $profile[1] . '' . $profile[2];
				$this->humanReadVersion = $this->mode . '-' . $profile[1] . '.0.' . $profile[2];
			}


		}

		$this->profile = $profile;
	}

	/**
	 * @return string
	 */
	public function getVersion(){
		return $this->humanReadVersion;
	}

	/**
	 *
	 * @return bool
	 */
	public function isValid(){
		return file_exists(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/configurator.php');
	}

	/**
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function getConfigForProfile(){
		if(file_exists(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/configurator.php')){
			return unserialize(file_get_contents(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/configurator.php'));
		}
		throw new \Exception('Source file' .__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/configurator.php not exists' );
	}

	/**
	 * @param $id
	 * @param string $lang
	 *
	 * @throws \Exception
	 */
	public function getStringById($id, $lang = 'cs'){
		if(count($this->string[$lang]) == 0 && file_exists(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/strings_'.$lang.'.xml')){
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
	 * @throws \Exception
	 */
	public function getTranslateClass($translateClass){
		if(file_exists(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/'.$translateClass.'.php')){
			include_once(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/'.$translateClass.'.php');
			if(class_exists('con'. $this->version . '_' .$this->mode . '\\' . $translateClass, false)){
				$className = ('con'. $this->version. '_' .$this->mode . '\\' . $translateClass);
				return new $className($this->profile);
			}
		}

		throw new \Exception('Class ' . $translateClass . ' not found');
	}

	/**
	 * @param $name
	 * @param $value
	 * @param string $lang
	 *
	 * @throws \Exception
	 */
	public function getSelectText($name, $value, $lang = 'cs'){
		if(count($this->string[$lang]) == 0 && file_exists(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->versionDir.'/strings_'.$lang.'.xml')){
			$this->loadString($lang);
		}

		$name = str_replace('R.array.', '', $name);

		$value  = $value >= 65  ? $value - 65 : $value;

		if(isset( $this->string[$lang][$name][$value])) {
			return $this->string[ $lang ][ $name ][ $value];
		}

		throw new \Exception('index ' .$name . ' and value ' .  $value . ' not found');
	}

	/**
	 * @param $lang
	 */
	private function loadString($lang){
		$xml = simplexml_load_file(__DIR__ . '/../configuration/'.$this->mode.'/configuration_'.$this->version.'/strings_'.$lang.'.xml');
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

	public function getMode()
    {
        return $this->mode;
    }


}
