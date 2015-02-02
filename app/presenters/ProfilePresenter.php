<?php
namespace App\Presenters;

use Model\ProfileModel;
use Model\ProfileParser;
use Model\ProfileComparator;
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
        $profile = $this->profileModel->getById($this->getParameter('id'));
        $this->template->profile = $profile;
        
		$parser = new ProfileParser( $this->template->profile->profile, $this->lang);
         
        
		$this->template->parsed = $parser->getParsedProfile();
		$this->template->parser = $parser;
		$this->template->id = $profile;

		if($this->template->parser->isValid()){
			$this->profileModel->increaseViews($this->getParameter('id'));
		}

	}
    
    public function renderCompare(){
            $profile1 = $this->profileModel->getById($this->getParameter('id'));
            $profile2 = $this->profileModel->getById($this->getParameter('id2'));
            
            $parser1 = new ProfileParser ($profile1->profile, $this->lang);
            $parser2 = new ProfileParser ($profile2->profile, $this->lang);
            
            if ($parser1->getVersion() === $parser2->getVersion()){
                
                $compareResult = new ProfileComparator ($parser1->getParsedProfile(), $parser2->getParsedProfile());
                
                $this->template->id = $profile1;
                $this->template->id2 = $profile2;
                
                $this->template->profile1 = $profile1;
                $this->template->profile2 = $profile2;
                
                $this->template->parser1 = $parser1;
                $this->template->parser2 = $parser2;
                
                $this->template->parsed1 = $parser1->getParsedProfile();
                $this->template->parsed2 = $parser2->getParsedProfile();
                
                $this->template->compared = $compareResult->getCompared();
                $this->template->profile2Values = $compareResult->getValues($res, $this->template->parsed2);
                
                if($this->template->parser1->isValid()&&$this->template->parser2->isValid()){
		          $this->profileModel->increaseViews($this->getParameter('id'));
                  $this->profileModel->increaseViews($this->getParameter('id2'));
                }


            }else{
                
                $this->errorFile('Uncomparable versions: ' . $parser1->getVersion() . ' != ' . $parser2->getVersion());
                     
           }
             
    }

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
				$this->errorFile('Broken File');
				return;
			}

			$save = true;
		}

        try{
    		$parser = new ProfileParser( $fileContent, $this->lang);
    		if(!$parser->isValid()) {
    			$this->errorFile('Unsupported version: ' .$parser->getVersion());
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
    
    			$id = $this->profileModel->save($values);
    		}else{
    			$id = $url['query']['id'];
    		}
            
        }catch(\Exception $e){
			$this->flashMessage($e->getMessage());
			return;
		}
        
		if($this->template->parser->isValid()){
			$this->profileModel->increaseViews($id);
		}
	}

	/**
	 * @param $text
	 */
	protected function errorFile($text){
		$this->flashMessage($text, 'error');
		$this->redirect('efile');
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
			ProfileModel::COLUMN_IP  	 	=> $_SERVER['REMOTE_ADDR'],
			ProfileModel::COLUMN_USERAGENT 	=> $_SERVER['HTTP_USER_AGENT'],
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
	
	public function renderEfile(){
	}



}
