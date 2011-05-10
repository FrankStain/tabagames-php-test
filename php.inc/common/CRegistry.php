<?php
/**
 * Модуль реестра сущностей.
 * Модуль представляет собой реализацию шаблона "Service locator".
 * Механизм хорошо помогает в вопросе заменяемости классов и в общем тестировании.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

// Общий класс для всех видов ошибок реестра
class CRegistryException extends Exception {};

// Не удалось найти требуемый сервис
class CServiceNotFound extends CRegistryException {};


class CRegistry {


	/** @var array		Список зарегистрированных сервисов */
	static private $aServices = array();


	/**
	 * Позволяет обратиться к сервису.
	 *
	 * @param string	$sServiceName	Название сервиса.
	 *
	 * @return object					Требуемый сервис
	 * @throws CServiceNotFound			Если сервис не получается найти, метод бросается исключением
	 */
	static public function service( $sServiceName ){

		if( !isset( self::$aServices[ $sServiceName ] ) ) throw new CServiceNotFound( 'There are no service named '.$sServiceName );

		// Строкой задается не сам сервис, а его локатор, т.е. место нахождения сервиса.
		// В данном случае - это будет имя класса сервиса, для создания сервиса используется только конструктор по умолчанию
		if( is_string( self::$aServices[ $sServiceName ] ) && class_exists( self::$aServices[ $sServiceName ] ) ){

			$sServiceName = self::$aServices[ $sServiceName ];
			self::$aServices[ $sServiceName ] = new $sServiceName();

		};

		if( !is_object( self::$aServices[ $sServiceName ] ) ) throw new CServiceNotFound( 'There are no service named '.$sServiceName );

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
