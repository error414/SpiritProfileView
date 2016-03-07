<?php
/**
 * Date: 08.08.14
 * Time: 9:10
 */

namespace Model;

use con20_heli\LabelTranslate;

class ProfileParser{

	private $profile = array();

	private $parsed = array();

	private $lang = 'cs';

	private $configurator;

	/**
	 * @param $profile
	 */
	public function __construct($profile, $lang){

		$this->lang = $lang;
		$profile = str_split($profile);
		foreach($profile as $key => $item){
			$this->profile[$key + 1] = hexdec(bin2hex($item));
		}
		$this->configurator = new Configurator($this->profile);
		var_dump($this->configurator->getConfigForProfile());
		die();
		if($this->configurator->isValid()) {
			$this->parse();
		}
	}

	public function isValid(){
		return $this->configurator->isValid();
	}

	public function getVersion(){
		return $this->configurator->getVersion();
	}

	/**
	 * @return array
	 */
	public function getParsedProfile(){
		return $this->parsed;
	}

	public function getText($id){
		if(strpos($id, ':') != 0)
		{
			$labelTranslate = new LabelTranslate();
			$id = $labelTranslate->translateCurrent($this->profile, $id);
		}

		return $this->configurator->getStringById($id, $this->lang);
	}

	/**
	 * @throws Exception
	 */
	private function parse(){
		$this->parsed['version']['label'] =  $this->configurator->getStringById('version', $this->lang);
		$this->parsed['version']['value'] =  $this->configurator->getVersion();
		$this->parsed['version']['path'] = 'version';

		foreach($this->configurator->getConfigForProfile() as $position => $config) {

			//fix sig int
			if($config['min'] < 0 && $this->profile[ $position ] > 128){
				$this->profile[ $position ] = $this->profile[ $position ] - 256;
			}

			$label = array_map( array( $this, 'getText' ), $config['label'] );
			$this->parsed[$config['name']]['label'] = join( ' ', $label );
			$this->parsed[$config['name']]['path'] = join( ' ', $config['label'] );

			//selectBox
			if ( $config['type'] == 'select' ) {
				$this->parsed[$config['name']]['value'] = $this->configurator->getSelectText($config['select'],  $this->profile[ $position ], $this->lang);
			}

			//checkBox
			if ( $config['type'] == 'check' ) {
				$this->parsed[$config['name']]['value'] = ( $this->profile[ $position ] == 49 ||  $this->profile[ $position ] == 1? $this->configurator->getStringById('yes', $this->lang)  : $this->configurator->getStringById('no',$this->lang) );
			}


			//SEEK
			if ( $config['type'] == 'seek' ) {
				if ( $config['add'] != 0 ) {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ] + $config['add'];
					$this->parsed[$config['name']]['min'] = $config['min'] + $config['add'];
					$this->parsed[$config['name']]['max'] = $config['max'] + $config['add'];

				} elseif ( $config['discount'] != 0 ) {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ] - $config['discount'];
					$this->parsed[$config['name']]['min'] = $config['min'] - $config['discount'];
					$this->parsed[$config['name']]['max'] = $config['max'] - $config['discount'];
				} else {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ];
					$this->parsed[$config['name']]['min']   = $config['min'];
					$this->parsed[$config['name']]['max']   = $config['max'];
				}

				if ( $config['translate'] !== NULL ) {
					$this->parsed[$config['name']]['value'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['value'], $this->profile, 0, $this->configurator, $this->lang);
					$this->parsed[$config['name']]['max'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['max'], $this->profile, 1, $this->configurator, $this->lang);
					$this->parsed[$config['name']]['min'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['min'], $this->profile, 2, $this->configurator, $this->lang);
				}
			}
		}

		$this->parsed = $this->buildGroup($this->parsed);
	}

	protected function buildGroup($parsed){
		$grouped = array();

		foreach($parsed as $item){
			$path = explode(' ', $item['path']);

			if(count($path) == 1){
				$grouped['general_button_text'][] = $item;
				continue;
			}

			$grouped[str_replace('R.string.', '', $path[0])][] = $item;
		}
		return $grouped;

	}
}
