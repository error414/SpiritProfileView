<?php

namespace App\Presenters;

use Nette,
	App\Model;
use PDO;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->forward('Profile:upload');
	}

	public function createComponentSearchProfile(){
		$form = new Nette\Application\UI\Form;
		$form->addText('text', '')->addRule(Nette\Forms\Form::MIN_LENGTH, 'Minimal length is 3 character', 3)->setRequired(true);
		$form->addSubmit('sub', 'search');

		$form->onSuccess[] = $this->searchFormSucceeded;
		return $form;
	}

	public function searchFormSucceeded(Nette\Forms\Form $form){
		$values = $form->getValues();

		$this->template->profiles = $this->profileModel->findByFullText($values['text']);
	}

	public function renderTest()
	{
		$atributy = Array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
		);
		try {
			$db = new PDO('mysql:host=localhost;dbname=profile', 'root', 'jklm', $atributy);
			$doc = new \DOMDocument("1.0");
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;
			$root = $doc->createElement("pricelist");
			$doc->appendChild($root);
			$select = $db->prepare("SELECT id, name, price FROM pricelist");
			$select->execute();
			while ($row = $select->fetch(PDO::FETCH_OBJ)) {
				$item = $doc->createElement("item");
				$item->appendChild($doc->createElement("id", $row->id));
				$item->appendChild($doc->createElement("name", $row->name));
				$item->appendChild($doc->createElement("price", $row->price));
				$root->appendChild($item);
			}
			$sablona = new \DOMDocument();
			$sablona->load('/Users/petrcada/Sites/SpiritProfileView/www/html.xsl');
			$sablona->formatOutput = true;
			$xsl = new \XSLTProcessor();
			$xsl->importStyleSheet($sablona);
			echo $xsl->transformToXML($doc);
		} catch (\Exception $e) {
			echo $e->getMessage(), "\n";
		}


		$this->terminate();
	}


}
