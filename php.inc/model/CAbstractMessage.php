<?php
/**
 * Обобщенный класс сообщения, служит основой для моделей "Заметка" и "Комментарий"
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-09
 */


abstract class CAbstractMessage extends CAbstractModel {


	/**
	 * Конструктор.
	 * У всех текстовых сообщений всегда есть что то общее... Даже если они нацарапаны на заборе. :)
	 *
	 */
	public function __construct(){

		parent::__construct();

		$this->aProperties['Author'] = array(
			'code'		=> 'AUTHOR',
			'value'		=> '',
			'modified'	=> false
		);
		$this->aProperties['Text'] = array(
			'code'		=> 'TEXT',
			'value'		=> '',
			'modified'	=> false
		);
		$this->aProperties['Date'] = array(
			'code'		=> 'DATE',
			'value'		=> time(),
			'modified'	=> false
		);

	}


};
?>
