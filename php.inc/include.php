<?php
/**
 * Основной файл для подключения объектной модели "блога"
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

// Определяем корневой каталог классов
define( 'BLOG_INCLUDE_ROOT', dirname( realpath( __FILE__ ) ) );

// А вот тут у нас должны лежать конфиги
define( 'BLOG_CONFIG_ROOT', dirname( BLOG_INCLUDE_ROOT ).'/config' );

// Подключаен автозагрузчик
require_once( BLOG_INCLUDE_ROOT.'/common/CModule.php' );

// Инициализируем библиотеку автозагрузчика
CModule::addAutoloadedClasses( array(

	'CRegistry' => '/common/CRegistry.php',
	'CDatabase' => '/common/CDatabase.php',

) );

// Ставим сервисы
try{

	CRegistry::setService( 'DB', CDatabase::getMongoDb() );

}catch( CDatabaseException $e ){

	die( $e->getMessage() );

};


?>
