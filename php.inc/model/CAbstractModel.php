<?php
/**
 * Класс абстрактной модели. Определяет общие интерфейсы работы со всеми объектами моделей.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-08
 */

// Общее исключение моделей
class CModelException extends Exception {};

// Неверный идентификатор
class CModelWrongIdException extends CModelException {};


abstract class CAbstractModel {


	/** @var array				Список свойств объекта в виде "имя свойства" => "значение" */
	protected $aProperties = array();


	/** @var string				Идентификатор документа, _Id, иным словами */
	protected $sId = false;


	/**
	 * Всего лишь онструктор.
	 *
	 */
	public function __construct(){

	}


	/**
	 * Создает объект сущности.
	 * (фабричный метод)
	 *
	 * @return object							Объект конкретной сущности.
	 */
	abstract static public function create();


	/**
	 * Возвращает объект с данными по идентификатору.
	 * (фабричный метод)
	 *
	 * @param string		$sId				Идентификатор нуного объекта.
	 *
	 * @return object							Объект с данными
	 * @throws CModelWrongIdException			Если не удалось найти объект или что то случилось с БД
	 */
	abstract static public function getById( $sId );


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
	abstract public static function getList( $aFilter = array(), $iLimit = false, $iOffset = false );


	/**
	 * Возвращает название коллекции конкретной модели.
	 * Абстрактный метод.
	 *
	 * @return string			Строковое название коллекции модели
	 */
	abstract protected function getCollectionName();


	/**
	 * Реализует общее тело метода getById
	 *
	 * @param CAbstractModel	$oPrototype		Объект-прототип модели, для которой и надо получить объект по идентификатору
	 *
	 */
	static protected function getModelById( CAbstractModel $oPrototype, $sId ){

		$oResult = NULL;

		try{

			// Да, в руках у нас сейчас всего лишь прототип, по хорошему, его нельзя использовать. Надо из него создать будущий результат.
			$oResult = $oPrototype->create();

			$aProperties = $oPrototype->getCollection()->findOne( array(
				'_id' => new MongoId( $sId )
			) );

			if( is_null( $aProperties ) ) throw new CModelWrongIdException( 'Object "'.$sId.'" not found' );

			self::assignObjectProperties( $oResult, $aProperties );

		}catch( MongoException $e ){

			throw new CModelWrongIdException( 'Wrong ID : '.$e->getMessage() );

		};

		return $oResult;

	}


	/**
	 * Механизм выборки списка по фильтру. Без фильтра выдаст кучу всех объектов коллекции.
	 * (фабричный метод)
	 *
	 * @param CAbstractModel	$oPrototype		Объект-прототип модели, для которой и надо получить реальный объект
	 * @param array				$aFilter		Список фильтруемых свойств
	 * @param int				$iLimit			Максимальное число выбираемых элементов
	 * @param int				$iOffset		Порядковый номер элемента, начиная с которого надо вывести список.
	 *
	 * @return array							Список объектов, полученных по фильтру, может быть и пустым
	 * @throws CModelException					Выкидывает в случае возникновения проблем с БД
	 */
	static protected function getModelList( CAbstractModel $oPrototype, $aFilter = array(), $iLimit = false, $iOffset = false ){

		$aResult = array();
		$iOffset = intval( $iOffset );
		$iLimit = intval( $iLimit );

		try{

			$aCriteria = $oPrototype->makeDbCriteria( $aFilter );

			$oObjects = $oPrototype->getCollection()->find( $aCriteria );
			if( $iOffset ) $oObjects->skip( $iOffset );
			if( $iLimit ) $oObjects->limit( $iLimit );

			$aResultObjects = array();
			foreach( $oObjects as $sId => $aData ){

				$oCurrentObject = $oPrototype->create();

				$oCurrentObject->sId = $sId;
				self::assignObjectProperties( $oCurrentObject, $aData );

				$aResultObjects[ $sId ] = $oCurrentObject;

			};

			$aResult = $aResultObjects;

		}catch( MongoException $e ){

			throw new CModelException( __METHOD__.' error : '.$e->getMessage() );

		};

		return $aResult;

	}


	/**
	 * Помогает собрать критери для выборки из БД
	 *
	 * @param array			$aFilter			Список фильтруемых свойств
	 *
	 * @return array							ФСформированный критерий
	 */
	protected function makeDbCriteria( $aFilter ){

		$aCriteria = array();

		foreach( $aFilter as $sPropertyName => $mFilter ){

			if( 'Id' == $sPropertyName ){

				$sPropertyCode = '_id';
				$mFilter = new MongoId( $mFilter );

			}else{

				if( !isset( $this->aProperties[ $sPropertyName ] ) ) continue;
				$sPropertyCode = $this->aProperties[ $sPropertyName ]['code'];

			};

			$aCriteria[ $sPropertyCode ] = $mFilter;

		};

		return $aCriteria;

	}


	/**
	 * Помогает выставить в объекте значения всех свойств из записи в базе
	 *
	 * @param object		$oDestObject		Целевой объект, в котором надо выставить свойства
	 * @param array			$aSourceData		Исходный массив записи из БД
	 */
	static protected function assignObjectProperties( $oDestObject, $aSourceData ){

		foreach( $oDestObject->aProperties as $sPropertyName => $aProperty ){

			$sPropertyCode = $aProperty['code'];
			if( !isset( $aSourceData[ $sPropertyCode ] ) ) continue;

			$oDestObject->aProperties[ $sPropertyName ]['value'] = $aSourceData[ $sPropertyCode ];
			$oDestObject->aProperties[ $sPropertyName ]['modified'] = false;

		};

	}


	/**
	 * Удаление объекта.
	 *
	 * @return bool								При удачном удалении вернет true, иначе - false
	 */
	public function delete(){

		if( !is_string( $this->sId ) ) return false;

		try{

			$this->getCollection()->remove(
				array( '_id' => new MongoId( $this->sId ) ),
				array( 'safe' => true )
			);

			$this->sId = false;

		}catch( MongoException $e ){

			return false;

		};

		return true;

	}


	/**
	 * Сохранение объекта.
	 * Новый объект добавит, имеющийся - обновит.
	 *
	 * @return bool								При удачном сохранении вернет true, иначе - fale
	 */
	public function save(){

		if( $this->isNew() ){

			return $this->addDbRecord();

		}else{

			return $this->updateDbRecord();

		};

	}


	/**
	 * Помогает понять, является ли объект модели новым.
	 *
	 * @return bool								true для нового объекта, false - для объекта из БД
	 */
	public function isNew(){

		return !is_string( $this->sId );

	}


	/**
	 * Процесс добавления новой записи в БД.
	 * После этого объект получит свой _id и перестанет быть новым.
	 *
	 */
	protected function addDbRecord(){

		try{

			$aProperties = $this->exportProperties();

			$this->getCollection()->insert( $aProperties, array( 'safe' => true ) );
			$this->sId = trim( $aProperties['_id'] );

		}catch( MongoException $e ){

			throw new CModelException( __METHOD__.' error : '.$e->getMessage() );

		};

		return true;

	}


	/**
	 * Процесс обновления новой записи в БД.
	 *
	 */
	protected function updateDbRecord(){

		try{

			$aProperties = $this->exportProperties();
            $aProperties['_id'] = new MongoId( $this->sId );

			$this->getCollection()->save( $aProperties, array( 'safe' => true ) );

		}catch( MongoException $e ){

			throw new CModelException( __METHOD__.' error : '.$e->getMessage() );

		};

		return true;

	}


	/**
	 * Помогает из свойств объекта создать массив для БД.
	 *
	 * @return array							Массив вида "поле БД" => "Значение"
	 */
	protected function exportProperties(){

		$aResult = array();

		foreach( $this->aProperties as $sPropertyName => $aProperty ){

			$sPropertyCode = $aProperty['code'];
			$aResult[ $sPropertyCode ] = $aProperty['value'];

		};

		return $aResult;

	}


	/**
	 * Возвращает значение свойства по его названию.
	 *
	 * @param string		$sPropertyName		Название свойства
	 * @param mixed			$mDefault			Значение свойства по уполчанию, на случай, если свойство не определено.
	 *
	 * @return mixed							Фактическое значение свойства
	 */
	public function getProperty( $sPropertyName, $mDefault = NULL ){

		if( !isset( $this->aProperties[ $sPropertyName ] ) ) return $mDefault;
		return $this->aProperties[ $sPropertyName ]['value'];

	}


	/**
	 * Помогает записать значение в свойство
	 *
	 * @param string		$sPropertyName		Название свойства
	 * @param mixed			$mValue				Значение свойства, которое нужно установить.
	 *
	 * @return bool								Показатель удачности записи, true - значение было изменено
	 */
	public function setProperty( $sPropertyName, $mValue ){

		if( !isset( $this->aProperties[ $sPropertyName ] ) ) return false;
		$this->aProperties[ $sPropertyName ]['modified'] = $this->aProperties[ $sPropertyName ]['value'] != $mValue;
		$this->aProperties[ $sPropertyName ]['value'] = $mValue;
		return true;

	}


	/**
	 * Возвращает ID объекта (_id записи в БД)
	 *
	 * @return string							ID объекта (_id)
	 */
	public function getId(){

		return $this->sId;

	}


	/**
	 * Помогает быстро оступиться к объекту базы
	 *
	 * @return MongoDB			Объект басзы
	 */
	protected function getDB(){

		return CRegistry::service( 'DB' );

	}


	/**
	 * Помогает из БД получить колекцию модели.
	 *
	 * @return MongoCollection	Коллекция конкретной модели
	 */
	protected function getCollection(){

		$sCollectionName = $this->getCollectionName();
		return self::getDB()->$sCollectionName;

	}


};
?>
