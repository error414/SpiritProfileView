<?php
/**
 * Date: 07.04.14
 * Time: 8:29
 */
namespace Model;

use Nette;

abstract class BaseModel extends Nette\Object
{
	/**
	 * @var Nette\Database\Context
	 */
	protected $database;

	/**
	 * @param Nette\Database\Context $database
	 */
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

}

