<?php

namespace App\Presenters;

use Model\ProfileModel;
use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var ProfileModel */
    protected $profileModel;

    /**
     * @param Model\MenuModel
     */
    public function injectDomainsModel(ProfileModel $profileModel)
    {
        if ($this->profileModel) {
            throw new \Nette\InvalidStateException('ProfileModel Model has already been set');
        }
        $this->profileModel = $profileModel;
    }

}
