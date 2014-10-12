<?php

namespace App\Presenters;

use Model\ProfileModel;
use Model\ProfileParser;
use Nette;


/**
 * Homepage presenter.
 */
class ProfilePresenter extends BasePresenter
{

	public function renderDefault()
	{
	}

	public function renderShow()
	{
		$this->template->profile = $this->profileModel->getById($this->getParameter('id'));

		$parser = new ProfileParser( $this->template->profile->profile, $this->lang);
		$this->template->parsed = $parser->getParsedProfile();
		$this->template->parser = $parser;
		$this->template->id = $this->profileModel->getById($this->getParameter('id'));

	}

	public function renderByUrl(){
		$phpBB = new \curl_phpbb('http://spirit-system.com/phpBB3/');

		$url = $this->getParameter('url');
		$phpBB->login('error414', 'jklm258');


		$fileContent = $phpBB->read($url);

		$parser = new ProfileParser( $fileContent, $this->lang);
		$this->template->parsed = $parser->getParsedProfile();
		$this->template->parser = $parser;
		$this->template->name = 'test';

		$phpBB->logout();
	}


	/**
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentUploadProfile()
	{
		$form = new Nette\Application\UI\Form;

		$form->addUpload('profile', 'Profile:')
			->addRule(Nette\Application\UI\Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 1 kB.',  1024 /* v bytech */)
			->setRequired('Please select profile file.');


		$form->addSubmit('send', 'upload');
		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->uploadFormSucceeded;

		return $form;
	}

	public function uploadFormSucceeded($form)
	{
		$values = $form->getValues();

		$toInsert = array(
			ProfileModel::COLUMN_NAME 		=> $values['profile']->getName(),
			ProfileModel::COLUMN_DATA 		=> $values['profile']->getContents(),
			ProfileModel::COLUMN_VERSION 	=> NULL,
		);

		try {
			$parser = new ProfileParser($values['profile']->getContents(), $this->lang);
			if(!$parser->isValid()){
				$this->flashMessage('Unsupported version: ' .$parser->getVersion(), 'error');
				return;
			}


			$toInsert[ProfileModel::COLUMN_VERSION] = $parser->getVersion();

			$row = $this->profileModel->save($toInsert);
			$this->flashMessage('Profile was saved', 'info');

		}catch(\Exception $e){
			$this->flashMessage($e->getMessage());
			return;
		}

		if($row[ProfileModel::COLUMN_ID] > 0) {
			$this->redirect('Show', array('id' => $row[ProfileModel::COLUMN_ID]));
		}
		$this->terminate();
	}



}
