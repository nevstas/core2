<?php
/**
 * Created by JetBrains PhpStorm.
 * User: StepovichPE
 * Date: 14.09.13
 * Time: 17:04
 * To change this template use File | Settings | File Templates.
 */

class Enum extends Zend_Db_Table_Abstract {

	protected $_name = 'core_enum';

	public function exists($expr, $var = array())
	{
		$sel = $this->select()->where($expr, $var);
		return $this->fetchRow($sel->limit(1));
	}

	public function fetchFields($fields, $expr, $var = array()) {
		$sel = $this->select()->from($this->_name, $fields);
		if ($var) {
			$sel->where($expr, $var);
		} else {
			$sel->where($expr);
		}
		return $this->fetchAll($sel);
	}

	public function fetchOne($field, $expr, $var = array())
	{
		$sel = $this->select();
		if ($var) {
			$sel->where($expr, $var);
		} else {
			$sel->where($expr);
		}
		return $this->fetchRow($sel)->$field;
	}


}