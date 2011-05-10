<?php
/**
 * Реализация модели сущности "Пользователь"
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

// Исключение вида: "Такого логина в базе нету".
class CWrongLoginException extends CModelException {};


class CUser extends CAbstractModel {


	/**
	 * Конструктор.
	 *
	 */
	public function __construct(){

		parent::__construct();

		$this->aProperties['Login'] = array(
			'code'		=> 'LOGIN',
			'value'		=> '',
			'modified'	=> false
		);
		$this->aProperties['Passwd'] = array(
			'code'		=> 'PASSWD',
			'value'		=> '', // как на счет автогенерации паролей для новых пользователей? ;)
			'modified'	=> false
		);
		$this->aProperties['Email'] = array(
			'code'		=> 'EMAIL',
			'value'		=> '',
			'modified'	=> false
		);

	}

	/**
	 * Создает объект сущности.
	 * (фабричный метод)
	 *
	 * @return object							Объект конкретной сущности.
	 */
	static public function create(){

		return new self();

	}


	/**
	 * Возвращает объект с данными по идентификатору.
	 * (фабричный метод)
	 *
	 * @param string		$sId				Идентификатор нуного объекта.
	 *
	 * @return object							Объект с данными
	 * @throws CModelWrongIdException			Если не удалось найти объект или что то случилось с БД
	 */
	static public function getById( $sId ){

		return self::getModelById( self::create(), $sId );

	}


	/**
	 * Механизм выборки списка по фильтру. Без фильтра выдаст кучу всех объектов коллекции.
	 * (фабричный метод)
	 *
	 * @param array			$aFilter			Список фильтруемых свойств
	 * @param int			$iLimit				Максимальное число выбираемых элементов
	 * @param int			$iOffset			Порядковый номер элемента, начиная с которого надо вывести список.
	 *
	 * @return array							Список объектов, полученных по фильтру, может быть и пустым
	 * @throws CModelException					Выкидывает в случае возникновения проблем с БД
	 */
	static public function getList( $aFilter = array(), $iLimit = false, $iOffset = false ){

		return self::getModelList( self::create(), $aFilter, $iLimit, $iOffset );

	}


	/**
	 * Фабричный метод "Получить пользователя по его Id"
	 *
	 * @param string		$sLogin			Логин пользователя
	 *
	 * @return CUser						Объект пользователя с таким идентификатором
	 * @throws CWrongLoginException			Если такого логина в базе не оказалось
	 */
	static public function getByLogin( $sLogin ){

		try{

			$aUsers = self::getList( array( 'Login' => $sLogin ), 1 );

			return array_pop( $aUsers );

		}catch( CModelException $e ){

			throw new CWrongLoginException( 'There are no user with such login' );

		};

	}


	/**
	 * Проверка соответствия слова паролю пользователя.
	 *
	 * @param string		$sKeyWord		Слово, которое нужно проверить на соответствие паролю
	 *
	 * @return bool							true - если слово соответствует, false в иных случаях.
	 */
	public function checkPasswd( $sKeyWord ){

		return md5( $sKeyWord ) == $this->getProperty( 'Passwd' );

	}


	/**
	 * Привязка модели пользователя к колекции 'users'
	 *
	 * @return string						Назание коллекции класса пользователей
	 */
	protected function getCollectionName(){

		return 'users';

	}


	/**
	 * Помогает записать значение в свойство
	 * У Пользователя есть пароль, записать туда можно простое слово, но надо чтоб это слово сразу же защищалось.
	 *
	 * @param string		$sPropertyName		Название свойства
	 * @param mixed			$mValue				Значение свойства, которое нужно установить.
	 *
	 * @return bool								Показатель удачности записи, true - значение было изменено
	 */
	public function setProperty( $sPropertyName, $mValue ){

		if( 'Passwd' == $sPropertyName ) $mValue = md5( $mValue );

		return parent::setProperty( $sPropertyName, $mValue );

	}


};
?>
