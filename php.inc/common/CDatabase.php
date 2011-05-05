<?php
/**
 * Обертка для БД Mongo, поможет быстро получить инстанцию объекта для работы с базой
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

class CDatabaseException extends Exception {};

class CDatabase {


	/** @var Mongo			Инстанция объекта Mongo */
	static private $oMongoInstance = false;


	/** @var MongoDB		Инстанция базы Mongo */
	static private $oMongoDb = false;


	/**
	 */
	static protected function getMongoConfig(){

		$aDBConfig = parse_ini_file( BLOG_CONFIG_ROOT.'/database.conf', true );
		if( !is_array( $aDBConfig ) ) throw new CDatabaseException( 'Main database config not found.' );

		$aMongoConfig = $aDBConfig['mongo'];
		if( !is_array( $aMongoConfig ) ) throw new CDatabaseException( 'Mongo database config not found.' );

		return $aMongoConfig;

	}


	/**
	 */
	static public function getMongoInstance(){

		if( !is_object( self::$oMongoInstance ) ) self::initMongoInstance();

		return self::$oMongoInstance;

	}


	/**
	 */
	static protected function initMongoInstance(){

		self::$oMongoInstance = NULL;

		$aMongoConfig = self::getMongoConfig();

		try{

			$oMongo = new Mongo( $aMongoConfig['host'] );

			$sDbName = $aMongoConfig['dbname'];
			self::setMongoDb( $oMongo->$sDbName );

		}catch( MongoConnectionException $e ){

			throw new CDatabaseException( 'Mongo connection failed. '.$e->getMessage() );

		};

		self::setMongoInstance( $oMongo );

	}


	/**
	 */
	static protected function setMongoInstance( Mongo $oMongoInstance ){

		self::$oMongoInstance = $oMongoInstance;

	}


	/**
	 */
	static public function getMongoDb(){

		if( !is_object( self::$oMongoDb ) ) self::initMongoDb();

		return self::$oMongoDb;

	}

	/**
	 */
	static protected function initMongoDb(){

		self::$oMongoDb = NULL;

		$oMongo = self::getMongoInstance();
		$aMongoConfig = self::getMongoConfig();

		try{

			$sDbName = $aMongoConfig['dbname'];
			self::setMongoDb( $oMongo->$sDbName );

		}catch( MongoException $e ){

			throw new CDatabaseException( 'Can not select Mongo database. '.$e->getMessage() );

		};

	}

	/**
	 */
	static protected function setMongoDb( MongoDB $oMongoDb ){

		self::$oMongoDb = $oMongoDb;

	}


};
?>