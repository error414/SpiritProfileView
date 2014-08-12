<?php
/**
 * Date: 08.08.14
 * Time: 9:10
 */

namespace Model;

class ProfileParser{

	private $profile = array();

	private $parsed = array();

	private $configurator;

	/**
	 * @param $profile
	 */
	public function __construct($profile){

		$profile = str_split($profile);
		foreach($profile as $key => $item){
			$this->profile[$key + 1] = hexdec(bin2hex($item));
		}
		$this->configurator = new Configurator($this->profile);
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
		return $this->configurator->getStringById($id);
	}

	/**
	 * @throws Exception
	 */
	private function parse(){
		$this->parsed['version']['label'] =  $this->configurator->getStringById('version');
		$this->parsed['version']['value'] =  $this->configurator->getVersion();
		$this->parsed['version']['path'] = 'version';

		foreach($this->configurator->getConfigForProfile() as $position => $config) {
			$label = array_map( array( $this->configurator, 'getStringById' ), $config['label'] );
			$this->parsed[$config['name']]['label'] = join( ' ', $label );
			$this->parsed[$config['name']]['path'] = join( ' ', $config['label'] );
			$this->parsed[$config['name']]['min']   = $config['min'];
			$this->parsed[$config['name']]['max']   = $config['max'];

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
					$this->parsed[$config['name']]['min'] = $config['min'] + $config['add'];
					$this->parsed[$config['name']]['max'] = $config['max'] + $config['add'];

				} elseif ( $config['discount'] != 0 ) {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ] - $config['discount'];
					$this->parsed[$config['name']]['min'] = $config['min'] - $config['discount'];
					$this->parsed[$config['name']]['max'] = $config['max'] - $config['discount'];
				} else {
					$this->parsed[$config['name']]['value'] = $this->profile[ $position ];
				}

				if ( $config['translate'] !== NULL ) {
					$this->parsed[$config['name']]['value'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['value']);
					$this->parsed[$config['name']]['max'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['max']);
					$this->parsed[$config['name']]['min'] = $this->configurator->getTranslateClass( $config['translate'] )->translateCurrent($this->parsed[$config['name']]['min']);
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
