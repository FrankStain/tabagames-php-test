<?php
/**
 * Пример использования класса CModule. За одно - своеобразный метод тестирования.
 * Посмотрим как будет работать автозагрузка.
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-06
 */

error_reporting( E_ALL );

require_once( preg_replace( '/([\/\\]tests[\/\\].+)/', '/php.inc/include.php', __FILE__ ) );

// Этот класс нужен только для изменения корня автозагрузки.
// Изменение корня автозагрузки в штатном режиме недопустимо, поэтому метод setAutoloadRoot является защищенным.
class CExampleModule extends CModule {


	static public function setExampleAutoloadRoot(){

		self::setAutoloadRoot( dirname( __FILE__ ).'/CModule.includes' );
		self::clear();

	}


};



CExampleModule::setExampleAutoloadRoot();

CModule::addAutoloadedClasses( array(

	'CAutoloadedExample' => '/CAutoloadedExample.php',

) );



echo( 'Проверим, подключится ли класс CAutoloadedExample через автозагрузку?'.PHP_EOL );

if( class_exists( 'CAutoloadedExample' ) ){

	echo( 'Класс подключен!'.PHP_EOL );

}else{

	echo( 'Класс не подключен, наверное автозагрузчик сломался'.PHP_EOL );

};

?>
