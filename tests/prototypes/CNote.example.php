<?php
/**
 * Проверка работы заметок
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-09
 */

error_reporting( E_ALL );

require_once( preg_replace( '/([\/\\\]tests[\/\\\].+)/', '/php.inc/include.php', __FILE__ ) );

echo( 'Проверяем работу класса заметок.'.PHP_EOL );

try{

	$oTestUsers = CRegistry::service( 'DB' )->notes->find( array( 'TITLE' => new MongoRegex( '/^test.+/' ) ) );
	foreach( $oTestUsers as $oId => $aUserRec ){

		CRegistry::service( 'DB' )->users->remove( array( '_id' => new MongoId( $oId ) ) );
		echo( 'Удаляем пользователя '.$oId.' - '.$aUserRec['LOGIN'].'.'.PHP_EOL );

	};

	echo( 'Создаем тестоого пользователя.'.PHP_EOL );
	$oUser = CRegistry::service('User')->create();
	$oUser->setProperty( 'Login', 'test-User-for-CNote' );
	$oUser->setProperty( 'Email', 'test.mail@blogus.ru' );
	$oUser->setProperty( 'Passwd', '123456789' );
	$oUser->save();



	echo( 'Загружаем сохраненного пользователя через id и сравниваем.'.PHP_EOL );



}catch( CModelException $e ){

	echo( 'При работе выпало исключение '.get_class( $e ).' с текстом "'.$e->getMessage().'"'.PHP_EOL );

};

?>
