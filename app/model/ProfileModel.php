<?php

namespace Model;

use Nette;


class ProfileModel extends BaseModel
{
	const
		TABLE_NAME      = 'profile',
		COLUMN_ID       = 'id',
		COLUMN_NAME     = 'name',
		COLUMN_DATA     = 'profile',
		COLUMN_DATE     = 'date',
		COLUMN_VERSION  = 'version',
		COLUMN_FILEID   = 'file_id';


	/**
	 * @param $values
	 * @throws \Exception
	 */
	public function save($values)
	{
		//update
		if (isset($values['id']) && $values['id'] >= 0) {
			$this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $values['id'])->update($values);
			//insert
		} else {
			unset($values['id']);
			return $this->database->table(self::TABLE_NAME)->insert($values);
		}

	}

	/**
	 * @return array|Nette\Database\Table\IRow[]
	 */
	public function fetchAll(){
		return $this->database->table(self::TABLE_NAME)->fetchAll();
	}

	/**
	 * @param $id
	 * @return bool|mixed|Nette\Database\Table\IRow
	 */
	public function getById($id){
		return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->fetch();
	}

	/**
	 * @param $value
	 * @return array|Nette\Database\Table\IRow[]
	 */
	public function findByFullText($value){
		$value = '%' . $value . '%';
		return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME . " LIKE ?", $value)->fetchAll();
	}

	/**
	 * @param $idFile
	 * @return bool|mixed|Nette\Database\Table\IRow
	 */
	public function getProfileByIdFile($idFile){
		return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_FILEID, $idFile)->fetch();
	}
}
