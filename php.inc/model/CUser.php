<?php
/**
 * Модуль реестра сущностей.
 * Модуль представляет собой реализацию шаблона "Service locator".
 * Механизм хорошо помогает в вопросе заменяемости классов и в общем тестировании.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */


class CUser extends CAbstractModel {


	/** @var array				Список свойств объекта в виде "имя свойства" => "значение" */
	protected $aProperties = array(
		'login'		=> NULL,
		'email'		=> NULL,
		'passwd'	=> NULL,
	);


	/** @var string				Название коллекции, в которой был взят элемент */
	protected $sCollectionName = 'users';



	protected function __construct(){

	}


};
?>
