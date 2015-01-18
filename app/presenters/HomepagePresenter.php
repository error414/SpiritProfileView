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
}
