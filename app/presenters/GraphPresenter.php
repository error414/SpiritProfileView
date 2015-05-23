<?php
namespace App\Presenters;

use Model\PNGMetadataExtractor;
use Nette;


class GraphPresenter extends BasePresenter{

	public $showGraph = false;

	public function startup(){
    	parent::startup();
    }

	public function renderDefault(){


		$this->template->showGraph = $this->showGraph;
	}

	/**
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentUploadProfile()
	{
		$form = new Nette\Application\UI\Form;

		$form->addUpload('image', 'Image:')
			->addRule(Nette\Application\UI\Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 100 kB.',  1024 * 100 /* v bytech */)
			->setRequired('Please select graph file.');


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
		$this->showGraph = true;

		$png = new PNGMetadataExtractor($values['image']->getContents());
		if($png->check_chunks('tEXt', 'graph_data')){
			$this->flashMessage('No data found', 'error');
		}

		$data = $png->get_chunks('tEXt', 'graph_data');

		preg_match_all('/\[([0-9]*),([0-9.]*)\]/', $data, $match);

		if(isset($match[2])){
			$this->template->graphData = (join(',', $match[2]));
		}else{
			$this->flashMessage('No data found', 'error');
		}
	}
}


