<?php
namespace App\Presenters;


use Model\ProfileComparator;
use Model\ProfileParser;

class CliPresenter extends BasePresenter{


	public function startup(){
    	parent::startup();

    	if(!$this->getSession()->getSection('lang')->lang){
    	    $this->lang = $this->getSession()->getSection('lang')->lang = 'cs';
    	}else{
    	    $this->lang = $this->getSession()->getSection('lang')->lang;
    	}
    }

	public function renderDefault(){

	}

	/**
	 * @throws \Nette\Application\AbortException
	 */
	public function renderCli(){
		$post = json_decode(file_get_contents( 'php://input' ));

		$command    = $post->method;
		$params     = $post->params;

		switch($command){
			case 'help':
				$this->payload->result = $this->help($params);
				break;
			case 'ls':
				$this->payload->result = $this->ls($params);
				break;

			case 'show':
				$this->payload->result = $this->show($params);
				break;

			case 'compare':
				$this->payload->result = $this->compare($params);
				break;

			case 'find':
				$this->payload->result = $this->find($params);
				break;

			case 'locale':
				if(isset($params[0])){
					$this->payload->result = $this->lang = $this->getSession()->getSection('lang')->lang = in_array($params[0], array('cs', 'en')) ? $params[0] : 'en';
				}else{
					$this->payload->result = $this->lang;
				}

				break;
			default:
				$this->payload->error = 'unknow command';
				break;

		}


		$this->terminate();
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function help($params){
		return "
\033[0;1mclear \033[0;0mclear screen
\033[0;1mls  \033[0;0mlist of saved profiles
\033[0;1mshow {profile number} \033[0;0mshow profile, for example 'show 35'
\033[0;1mlocale \033[0;0mdisplay locale
\033[0;1mlocale {locale code} \033[0;0mset locale to locale code, available locale codes are cs and en
\033[0;1mcompare {profile1 number} {profile2 number} \033[0;0mcompare profiles, for example 'compare 2 10'
\033[0;1mfind -v {version} -n {name} \033[0;0mfind profiles, for example find -v 1.0.* -n *trex*'
		";

	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function ls($params){
		$profiles = $this->profileModel->fetchAll();

		return $this->formatList($profiles);
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function find($params){
		if(count($params) < 2){
			return 'bad params';
		}

		$query = array();

		for($i = 1; $i < count($params); $i = $i + 2){
			switch($params[$i - 1]) {
				case '-v':
					$query[] = array('version LIKE ?' => str_replace('*', '%', $params[$i]));
					break;
				case '-n':
					$query[] = array('name LIKE ?' => str_replace('*', '%', $params[$i]));
					break;

				default:
					return 'bad params';
			}
		}

		return $this->formatList($this->profileModel->findAll($query));

	}

	/**
	 * @param $profiles
	 *
	 * @return string
	 */
	private function formatList($profiles)
	{
		if(!$profiles){
			return '';
		}

		$nameLength = 10;
		foreach($profiles as $profile){
			$nameLength = max($nameLength, mb_strlen($profile->name, 'UTF-8') + 5);
		}

		$result = '';
		foreach($profiles as $profile){
			$result .= '[' . $profile->id . ']' . str_repeat(' ', 4 - mb_strlen((string)$profile->id), 'UTF-8') . $profile->name . str_repeat(' ', $nameLength - mb_strlen($profile->name, 'UTF-8')). $profile->version . "\n";
		}

		return $result;
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function show($params){
		if(!isset($params[0])){
			return 'profile not found';
		}

		$profile = $this->profileModel->getById($params[0]);
		if(!$profile){
			return 'profile not found';
		}

		$parser = new ProfileParser( $profile->profile, $this->lang);

		$parsed = $parser->getParsedProfile();
		$result = "\n" . $profile->name .'(' . $profile->version . ") \n" . str_repeat('-', mb_strlen($profile->name .'(' . $profile->version . ")", 'UTF-8')) . "\n" . str_repeat('-', mb_strlen($profile->name .'(' . $profile->version . ")", 'UTF-8')) . "\n";

		$labelLength = 10;
		$valueLength = 10;
		foreach($parsed as $nameGroup => $group){
			foreach($group as $item){
				$labelLength = max($labelLength, mb_strlen($item['label']) + 5, 'UTF-8');
				$valueLength = max($valueLength, mb_strlen($item['value']) + 5, 'UTF-8');
			}
		}

		foreach($parsed as $nameGroup => $group){
			$result .= "\n" . $parser->getText($nameGroup) . "\n";
			$result .= str_repeat('-', mb_strlen($parser->getText($nameGroup), 'UTF-8')) . "\n";

			foreach($group as $item){
				$result .= $item['label'];
				if(isset($item['min']) && $item['min'] !== NULL && $item['min'] !== '') {
					$result .= str_repeat(' ', $labelLength - mb_strlen($item['label'], 'UTF-8')) . $item['value'];
					$result .= str_repeat( ' ', $valueLength - mb_strlen( $item['value'], 'UTF-8' ) ) . ($item['min'] . " <-> " . $item['max']) . "\n";
				}else{
					$result .= str_repeat(' ', $labelLength - mb_strlen($item['label'], 'UTF-8')) . $item['value'] . "\n";
				}

			}
		}

		return $result;
	}

	/**
	 * @param $params
	 *
	 * @return string
	 */
	private function compare($params){
		if(!isset($params[0]) || !isset($params[1])){
			return 'profile not found';
		}

		$profile1 = $this->profileModel->getById($params[0]);
        $profile2 = $this->profileModel->getById($params[1]);

		if(!$profile1 || !$profile2){
			return 'profile not found';
		}

		$parser1 = new ProfileParser ($profile1->profile, $this->lang);
        $parser2 = new ProfileParser ($profile2->profile, $this->lang);

		$parsed1 = $parser1->getParsedProfile();
		$parsed2 = $parser2->getParsedProfile();

		if ($parser1->getVersion() !== $parser2->getVersion()){
			return 'profile version not match';
		}

		$compareResult = new ProfileComparator($parser1->getParsedProfile(), $parser2->getParsedProfile());
		$compared = $compareResult->getCompared();

		$result = "\n" . $profile1->name .'(' . $profile1->version . ") <-> ";
		$result .= $profile2->name .'(' . $profile2->version . ") \n" . str_repeat('-', mb_strlen($profile2->name .'(' . $profile2->version . ")", 'UTF-8') * 2 + 5) . "\n" . str_repeat('-', mb_strlen($profile2->name .'(' . $profile2->version . ")", 'UTF-8')* 2 + 5) . "\n\n";

		$labelLength = 10;
		$valueLength = 10;
		$profileName1Length = mb_strlen($profile1->name, 'UTF-8');
		foreach($parsed1 as $nameGroup => $group){
			foreach($group as $item){
				$labelLength = max($labelLength, mb_strlen($item['label'], 'UTF-8') + 5);
				$valueLength = max($valueLength, mb_strlen($item['value'], 'UTF-8') + 5);
			}
		}

		$result .= str_repeat( ' ', $labelLength) . $profile1->name;
		$result .= str_repeat( ' ', $profileName1Length + 10 - mb_strlen($profile1->name, 'UTF-8')) . $profile2->name . "\n" ;

		foreach($parsed1 as $nameGroup => $group){
			foreach($group as $key => $item){
				if(in_array($nameGroup . '/' . $key, $compared)) {
					$result .= $item['label'];
					$result .= str_repeat( ' ', $labelLength - mb_strlen( $item['label'], 'UTF-8' ) ) . $item['value'];
					$result .= str_repeat( ' ', $profileName1Length + 10 - mb_strlen( $item['value'], 'UTF-8' ) ) . $parsed2[$nameGroup][$key]['value'] . "\n";
				}
			}
		}

		return $result;
	}
}
