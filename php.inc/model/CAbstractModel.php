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

		$oResult = NULL;

		try{

			$oResult = self::create();

			$aProperties = $oResult->getCollection()->findOne( array(
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
	 * @param aray			$aFilter			Список фильтруемых свойств
	 *
	 * @return array							Список объектов, полученных по фильтру, может быть и пустым
	 * @throws CModelException					Выкидывает в случае возникновения проблем с БД
	 */
	public static function getList( $aFilter = array() ){

		$aResult = array();

		try{

			$oPrototype = self::create();
			$aCriteria = array();

			foreach( $aFilter as $sPropertyName => $mFilter ){

				if( 'Id' == $sPropertyName ){

					$sPropertyCode = '_id';
					$mFilter = new MongoId( $mFilter );

				}else{

					if( !isset( $oPrototype->aProperties[ $sPropertyName ] ) ) continue;
					$sPropertyCode = $oPrototype->aProperties[ $sPropertyName ]['code'];

				};

				$aCriteria[ $sPropertyCode ] = $mFilter;

			};

			$oObjects = $oPrototype->getCollection()->find( $aCriteria );

			$aResultObjects = array();
			foreach( $oObjects as $sId => $aData ){

				$oCurrentObject = self::crete();

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
			$this->sId = $aProperties['_id'];

		}catch( MongoException $e ){

			throw new CModelException( __METHOD__.' error : '.$e->getMessage() );

		};

		return true;

	}

	protected function updateDbRecord(){

	}


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
		return $this->aProperties[ $sPropertyName ];

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
		$this->aProperties[ $sPropertyName ] = $mValue;
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

		return CResistry::service( 'DB' );

	}


	/**
	 * Возвращает название коллекции конкретной модели.
	 * Абстрактный метод.
	 *
	 * @return string			Строковое название коллекции модели
	 */
	abstract protected function getCollectionName();


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
