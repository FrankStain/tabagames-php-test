<?php
/**
 * Реализация модели сущности "Заметка"
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-09
 */


class CNote extends CAbstractMessage {


	/**
	 * Конструктор.
	 * К уже заданным свойствам добавляем еще парочку.
	 *
	 */
	public function __construct(){

		parent::__construct();

		$this->aProperties['Title'] = array(
			'code'		=> 'TITLE',
			'value'		=> '',
			'modified'	=> false
		);
		$this->aProperties['Tags'] = array(
			'code'		=> 'TAGS',
			'value'		=> '',
			'modified'	=> false
		);

	}


	/**
	 * Создает объект сущности.
	 * (фабричный метод)
	 *
	 * @todo Была бы моя воля, яб весь этот копи-паст оставил бы в AbstractModel!!! У PHP проблемы с наследованием статических методов...
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
	 * @todo Надо избавляться... Это все приходится таскать лишь потому, что в CAbstractModel self::create не срабатывает как положено.
	 *
	 * @param string		$sId				Идентификатор нуного объекта.
	 *
	 * @return object							Объект с данными
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
	 */
	static public function getList( $aFilter = array(), $iLimit = false, $iOffset = false ){

		return self::getModelList( self::create(), $aFilter, $iLimit, $iOffset );

	}


	/**
	 * Выборка всех заметок. Применять при желании уронить сервер. :)
	 *
	 * @return array							Список всех заметок для всех авторов
	 */
	static public function getAll(){

		// Все так просто, не правда ли?
		return self::getList();

	}


	/**
	 * Выборка всех заметок автора
	 *
	 * @param string		$sAuthorId			Идентификатор автора заметок
	 *
	 * @return array							Список всех заметок автора
	 */
	static public function getListByAuthorId( $sAuthorId ){

		return self::getList( array( 'Author' => $sAuthorId ) );

	}


	/**
	 * Выборка всех заметок в определенное время, между Start- и End- датами.
	 *
	 * @param int			$iStartDate			Дата начала промежутка времени (UTC)
	 * @param int			$iEndDate			Дата конца конца промежутка времени (UTC)
	 *
	 * @return array							Список отловленных заметок
	 */
	static public function getListByDateRange( $iStartDate, $iEndDate ){

		return self::getList( array( 'Date' => array( '$gt' => $iStartDate, '$lt' => $iEndDate ) ) );

	}

	/**
	 * Выборка заметок по списку тегов.
	 * Вернет все заметки, где есть хоть один из перечисленных тегов.
	 *
	 * @param array			$aTags				Список тегов для поиска
	 *
	 * @return array							Список отловленных заметок
	 */
	static public function getListByTags( $aTags ){

		$sRegParam = '/('.implode( '|', $aTags ).')/';
		return self::getList( array( 'Tags' => new MongoRegex( $sRegParam ) ) );

	}


	/**
	 * Привязка модели пользователя к колекции 'notes'
	 *
	 * @return string						Назание коллекции класса заметок
	 */
	protected function getCollectionName(){

		return 'notes';

	}


};
?>
