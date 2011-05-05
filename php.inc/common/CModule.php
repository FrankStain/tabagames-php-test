<?php
/**
 * Модуль автозагрузки системы.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

// Определяем функцию автозагрузки, spl тут будет тяжеловат, поэтому будет просто автолоадер.
if( !function_exists( '__autoload' ) ){

	function __autoload( $sClassName ){

		CSlAutoloader::requireClass( $sClassName );

	};

	// Покажем, что наша функция автозагрузки внедрилась в систему
	define( 'BLOG_AUTOLOAD_ENABLED', true );

};

/**
 * Класс автозагрузчика.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */
class CModule {


	/** @var string		Путь к корневой папке загрузки модулей */
	static private $sAutoloadRoot = false;


	/** @var array		Список модулей, доступных для автозагрузки */
	static private $aClasses = array();


	/**
	 * Возвращает корневой путь для автозагрузки файлов.
	 *
	 * @return string	Путь к корневой папке подключения модулей.
	 */
	static public function getAutoloadRoot(){

		if( !is_string( self::$sAutoloadRoot ) ) self::setAutoloadRoot( self::getInitialAutoloadRoot() );
		return self::$sAutoloadRoot;

	}


	/**
	 * Помогает установить путь к корневой папке автозагрузки.
	 *
	 * @param string	$sPath		Новый путь автозагрузки модулей.
	 */
	static protected function setAutoloadRoot($sPath){

		self::$sAutoloadRoot = realpath( $sPath );

	}


	/**
	 * Возвращает путь к папке автозагрузки по умолчанию. ("/var/www/blogus.ru/php.inc", к примеру)
	 *
	 * @return string	Путь к папке автозагрузки по умолчанию.
	 */
	static protected function getInitialAutoloadRoot(){

		return ( defined( 'BLOG_INCLUDE_ROOT' ) )? BLOG_INCLUDE_ROOT : preg_replace( '/(\/php\.inc\/.+)/', '/php.inc', __FILE__ );

	}


	/**
	 * Помогает задекларировать список классов для последующей автозагрузки.
	 * Параметр aClasses должен быть массивом и содержать пары "Имя класса" => "Путь загрузки относительно корня автозагрузки"
	 *
	 * @param array		$aClasses		Список модулей для автозагрузки вида : "Имя класса" => "Путь относительно php.inc"
	 *
	 * @return bool		Флаг удачности операции. Если false, значит что-то не так с параметром aClasses.
	 */
	static public function includeAutoloadClasses( $aClasses ){

		if( !( is_array( $aClasses ) && count( $aClasses ) ) ){

			self::logError( __METHOD__.' : неверный параметр - $aClasses.' );
			return false;

		};

		if( !is_array( self::$aClasses ) ) self::$aClasses = array();
		$sClassesRoot = self::getAutoloadRoot();

		// Эта штука поможет понять, получилось ли в системе разместить свою функцию автозагрузки
		$bAutoloadEnabled = defined( 'BLOG_AUTOLOAD_ENABLED' ) && BLOG_AUTOLOAD_ENABLED;

		foreach( $aClasses as $sClassName => $sClassFileName ){

			$sFileName = realpath( $sClassesRoot.$sClassFileName );
			if( !( is_string( $sFileName ) && is_file( $sFileName ) ) ){

				self::logError( __METHOD__.' : Не удается найти файл "'.$sClassesRoot.$sClassFileName.'".' );
				continue;

			};

			if( $bAutoloadEnabled ){

				// Когда поднята автозагрузка, модули складываются в хорошем месте, чтоб потом подключиться по требованию.
				self::$aClasses[ strtolower( $sClassName ) ] = $sFileName;

			}else{

				// На случай, если систему автозагрузки так и не удалось поднять, требуемые файлы сразу же и подключаются.
				require_once( $sFileName );

			};

		};

		return true;

	}


	/**
	 * Выполняет подключение модуля по имени класса через include.
	 *
	 * @param string	$sClassName		Название класса, который стоит загрузить
	 *
	 * @return bool		Флаг удачности операции. Если false, значит что-то не так с именем подключаемого класса.
	 */
	static public function includeClass( $sClassName ){

		if( !( $sClassFileName = self::prepareClass( $sClassName ) ) ) return false;
		self::logInfo( __METHOD__.' : Загружаем файл "'.$sClassFileName.'" для класса "'.$sClassName.'" через include_once.' );
		include_once( $sClassFileName );

		return true;

	}


	/**
	 * Выполняет подключение модуля по имени класса через require.
	 *
	 * @param string	$sClassName		Название класса, который стоит загрузить
	 *
	 * @return bool		Флаг удачности операции. Если false, значит что-то не так с именем подключаемого класса.
	 */
	static public function requireClass( $sClassName ){

		if( !( $sClassFileName = self::prepareClass( $sClassName ) ) ) return false;
		self::logInfo( __METHOD__.' : Загружаем файл "'.$sClassFileName.'" для класса "'.$sClassName.'" через require_once.' );
		require_once( $sClassFileName );

		return true;

	}


	/**
	 * Выполняет предварительную подготовку автозагрузки для класса
	 *
	 * @param string	$sClassName		Название класса, который стоит загрузить
	 *
	 * @return string|false			 	Если все хорошо - вернет путь к классу. Если все плохо - вернет false
	 */
	static private function prepareClass( $sClassName ){

		$sClassName = trim( $sClassName );
		$sClassTag = strtolower( $sClassName );
		if( !( is_string( $sClassName ) && strlen( $sClassName ) && isset( self::$aClasses[ $sClassTag ] ) ) ) return false;

		// На этом этапе ошибок быть уже принципиально не может, все уже проверено при заполнении списка автозагрузки
		$sClassFileName = self::$aClasses[ $sClassTag ];
		// Но мы все равно подстрахуемся
		if( !file_exists( $sClassFileName ) ) return false;

		return $sClassFileName;

	}


	/**
	 * Выполняет логирование ошибок
	 * @todo	добавить сюда фактический код логирования.
	 *
	 * @param string	$sMessage		Сообщение для логирования.
	 */
	static protected function logError( $sMessage ){
		//echo( '[ E ] : '.$sMessage.PHP_EOL );
	}


	/**
	 * Выполняет отладочное логирование
	 * @todo	добавить сюда фактический код логирования.
	 *
	 * @param string	$sMessage		Сообщение для логирования.
	 */
	static protected function logDebug( $sMessage ){
		//echo( '[ D ] : '.$sMessage.PHP_EOL );
	}


	/**
	 * Выполняет логирование информации
	 * @todo	добавить сюда фактический код логирования.
	 *
	 * @param string	$sMessage		Сообщение для логирования.
	 */
	static protected function logInfo( $sMessage ){
		//echo( '[ I ] : '.$sMessage.PHP_EOL );
	}

};

?>
