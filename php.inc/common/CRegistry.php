<?php
/**
 * Модуль реестра сущностей.
 * Модуль представляет собой реализацию шаблона "Service locator".
 * Механизм хорошо помогает в вопросе заменяемости классов и в общем тестировании.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

class CRegistry {


	/** @var array		Список зарегистрированных сервисов */
	static private $aServices = array();


	/**
	 * Позволяет обратиться к сервису.
	 * Если сервис не удается найти - метод вернет false. Это на 100% означает,
	 * что если сервис не найден, далее (при обращении к сервису через ->) последует фатальная ошибка.
	 * Это вполне нормально. Этот участок кода может либо работать, либо положить систему на лопатки.
	 * И работать он должен на 120% верно, то есть, должны быть определены и доступны полностью все используемые сервисы.
	 * (такой подход разумен только в рамках этого проектика, в иной ситуации я, скорее, предпочту сделать систему исключений)
	 *
	 * @param string	$sServiceName	Название сервиса.
	 *
	 * @return object|false				Либо вернет требуемый сервис, либо - false
	 */
	static public function service( $sServiceName ){

		if( !isset( self::$aServices[ $sServiceName ] ) ) return false;

		// Строкой задается не сам сервис, а его локатор, т.е. место нахождения сервиса.
		// В данном случае - это будет имя класса сервиса, для создания сервиса используется только конструктор по умолчанию
		if( is_string( self::$aServices[ $sServiceName ] ) && class_exists( self::$aServices[ $sServiceName ] ) ){

			$sServiceName = self::$aServices[ $sServiceName ];
			self::$aServices[ $sServiceName ] = new $sServiceName();

		};

		if( !is_object( self::$aServices[ $sServiceName ] ) ) return false;

		return self::$aServices[ $sServiceName ];

	}


	/**
	 * Позволяет добавить сервис.
	 *
	 * @param string			$sServiceName	Название сервиса
	 * @param object|string		$mService		Собственно, объект сервиса.
	 */
	static public function setService( $sServiceName, $mService ){

		// Тут наличие класса проверять не очень верно, class_exists запустит автозагрузку, а ведь сам сервис может и не понадобиться в дальнейшей работе.
		if( !( strlen( $sServiceName ) && ( is_string( $mService ) || is_object( $mService ) ) ) ) return false;

		self::$aServices[ $sServiceName ] = $mService;

	}


	/**
	 * Позволяет удалить сервис.
	 *
	 * @param string	$sServiceName	Название сервиса
	 */
	static public function removeService( $sServiceName ){

		if( !isset( self::$aServices[ $sServiceName ] ) ) return false;
		unset( self::$aServices[ $sServiceName ] );

	}


	/**
	 * Позволяет очистить реестр.
	 *
	 */
	static public function clear(){

		self::$aServices = array();

	}


};
?>
