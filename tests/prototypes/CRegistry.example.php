<?php
/**
 * Проверка работы реестра
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-06
 */

error_reporting( E_ALL );

require_once( preg_replace( '/([\/\\\]tests[\/\\\].+)/', '/php.inc/include.php', __FILE__ ) );


echo( 'Проверяем работу реестра.'.PHP_EOL );
echo( 'Проверим, находится ли объект MongoDB в реестре с ключом DB'.PHP_EOL );

echo( ( ( CRegistry::service( 'DB' ) instanceof MongoDB )? 'Сервис DB - это MongoDB' : 'Там что то другое' ).PHP_EOL );

echo( 'Добавим еще один сервис.'.PHP_EOL );

class CExampleClass {


	static public function exampleStatic(){

		return 'static';

	}

	public function exampleDinamic(){

		return 'dinamic';

	}


};


try{

	echo( 'Добавим объект CExampleClass как сервис exObject.'.PHP_EOL );

	CRegistry::setService( 'exObject', new CExampleClass() );

	echo( 'Проверяем, получится ли получить сервис exObject.'.PHP_EOL );

	echo( 'Сервис exObject '.( ( CRegistry::service( 'exObject' ) instanceof CExampleClass )? '' : 'не ' ).'доступен'.PHP_EOL );
	echo( 'Вызов exObject::exampleStatic() '.( ( 'static' == CRegistry::service( 'exObject' )->exampleStatic() )? 'вернул нужный результат' : 'сработал неверно' ).PHP_EOL );
	echo( 'Вызов exObject::exampleDinamic() '.( ( 'dinamic' == CRegistry::service( 'exObject' )->exampleDinamic() )? 'вернул нужный результат' : 'сработал неверно' ).PHP_EOL );


	echo( 'Добавим имя класса CExampleClass как сервис exReference.'.PHP_EOL );

	CRegistry::setService( 'exReference', 'CExampleClass' );

	echo( 'Проверяем, получится ли получить сервис exReference.'.PHP_EOL );

	echo( 'Сервис exReference '.( ( CRegistry::service( 'exReference' ) instanceof CExampleClass )? '' : 'не ' ).'доступен'.PHP_EOL );
	echo( 'Вызов exReference::exampleStatic() '.( ( 'static' == CRegistry::service( 'exReference' )->exampleStatic() )? 'вернул нужный результат' : 'сработал неверно' ).PHP_EOL );
	echo( 'Вызов exReference::exampleDinamic() '.( ( 'dinamic' == CRegistry::service( 'exReference' )->exampleDinamic() )? 'вернул нужный результат' : 'сработал неверно' ).PHP_EOL );

	echo( 'Теперь попробуем получить доступ к сервису, которого нет.'.PHP_EOL );

	try{

		CRegistry::service( 'habrahabr' )->exampleStatic();
		echo( 'Сервис доступен? О__о Быть такого не должно!'.PHP_EOL );

	}catch( CRegistryException $e ){

		echo( 'Выпало исключение ('.get_class( $e ).'): "'.$e->getMessage().'", значит все в порядке.'.PHP_EOL );

	};

}catch( CRegistryException $e ){

	echo( 'При запросе сервиса выпало исключение : '.$e->getMessage() );

};


?>
