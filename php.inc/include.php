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

	// Техническое окружение проекта
	'CRegistry'				=> '/common/CRegistry.php',
	'CDatabaseFactory'		=> '/common/CDatabaseFactory.php',

	// Модели предметной среды
	'CAbstractModel'		=> '/model/CAbstractModel.php',
	'CModelException'		=> '/model/CAbstractModel.php',
	'CUser'					=> '/model/CUser.php',
	'CAbstractMessage'		=> '/model/CAbstractMessage.php',
	'CNote'					=> '/model/CNote.php',

) );

// Ставим сервисы
try{

	CRegistry::setService( 'DB', CDatabaseFactory::getMongoDb() );

}catch( CDatabaseFactoryException $e ){

	die( $e->getMessage() );

};

// Ставим сервисы предметной среды
CRegistry::setService( 'User', 'CUser' );
CRegistry::setService( 'Note', 'CNote' );
CRegistry::setService( 'Comment', 'CComment' );


?>
