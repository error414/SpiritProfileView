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

	/**
	 *
	 */
	public function renderByUrl(){
		$url 		= urldecode($this->getParameter('url'));
		$baseUrl 	= $this->getParameter('url');


		$url 	= parse_url($url);
		if(isset($url['query'])){
			parse_str($url['query'], $url['query']);
		}else{
			$this->errorFile('Unknow File');
			return;
		}

		$save = false;


		if($row = $this->profileModel->getProfileByIdFile($url['query']['id'])){
			$fileContent = $row[ProfileModel::COLUMN_DATA];
		}else{
			$baseUrl = str_replace('./', 'http://spirit-system.com/phpBB3/', $baseUrl);
			$fileContent = @file_get_contents($baseUrl);

			if(strlen($fileContent) <= 80) {
				$this->flashMessage('Broken File', 'error');
				$this->forward('upload');
				return;
			}

			$save = true;
		}


		$parser = new ProfileParser( $fileContent, $this->lang);
		if(!$parser->isValid()) {
			$this->flashMessage('Unsupported version: ' .$parser->getVersion(), 'error');
			$this->forward('upload');
			return;
		}

		$this->template->parsed = $parser->getParsedProfile();
		$this->template->parser = $parser;
		$this->template->name = $this->getParameter('name');

		if($save && isset($url['query']['id'])){
			$values = array(
				ProfileModel::COLUMN_NAME 	 => $this->getParameter('name'),
				ProfileModel::COLUMN_DATA 	 => $fileContent,
				ProfileModel::COLUMN_DATE 	 => new Nette\DateTime(),
				ProfileModel::COLUMN_VERSION => $parser->getVersion(),
				ProfileModel::COLUMN_FILEID  => $url['query']['id'],
			);

			$this->profileModel->save($values);
		}
	}

	/**
	 * @param $text
	 */
	protected function errorFile($text){
		$this->flashMessage($text, 'error');
		$this->forward('error');
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

	/**
	 *
	 * @param $form
	 * @throws Nette\Application\AbortException
	 */
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

	public function renderDownload(){
		$profile = $this->profileModel->getById($this->getParameter('id'));

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$profile->name);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		echo $profile->profile;
		$this->terminate();
	}



}
