<?php
/**
 * Проверка работы класса пользователей
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-09
 */

error_reporting( E_ALL );

require_once( preg_replace( '/([\/\\\]tests[\/\\\].+)/', '/php.inc/include.php', __FILE__ ) );

echo( 'Проверяем работу класса пользователей.'.PHP_EOL );

try{

	$oTestUsers = CRegistry::service( 'DB' )->users->find( array( 'LOGIN' => new MongoRegex( '/^test.+/' ) ) );
	foreach( $oTestUsers as $oId => $aUserRec ){

		CRegistry::service( 'DB' )->users->remove( array( '_id' => new MongoId( $oId ) ) );
		echo( 'Удаляем пользователя '.$oId.' - '.$aUserRec['LOGIN'].'.'.PHP_EOL );

	};

	echo( 'Создаем нового пользователя.'.PHP_EOL );
	$oUser = CRegistry::service('User')->create();

	echo( 'Объект создался, теперь надо проверить, что это именно CUser.'.PHP_EOL );
	if( $oUser instanceof CUser ){

		echo( 'Да, объект имеет принадлежность к классу CUser.'.PHP_EOL );

	}else{

		echo( 'Нет, созданный объект - это что-то другое, класс: '.get_class( $oUser ).PHP_EOL );
		throw new CModelException( 'Черти-что с этим сервисом User, надо разобраться с ним в классе CRegistry.' );

	};

	echo( 'Создадим тестового пользователя.'.PHP_EOL );
	$oUser->setProperty( 'Login', 'test-User' );
	$oUser->setProperty( 'Email', 'test.mail@blogus.ru' );
	$oUser->setProperty( 'Passwd', '123456789' );
	var_dump( $oUser );

	echo( 'Теперь сохраним его.'.PHP_EOL );
	$oUser->save();
	var_dump( $oUser );

	echo( 'Загружаем сохраненного пользователя через id и сравниваем.'.PHP_EOL );

	$oUser2 = CRegistry::service('User')->getById( $oUser->getId() );
	try{

		if( $oUser->getProperty( 'Login', 'P' ) !== $oUser2->getProperty( 'Login', 'T' ) ) throw new CModelException( 'Поля Login у объектов разные...' );
		if( $oUser->getProperty( 'Email', '@#$%^&' ) !== $oUser2->getProperty( 'Email', '(*&%^$%' ) ) throw new CModelException( 'Поля Email у объектов разные...' );

	}catch( CModelException $e ){

		echo( 'Сравнить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};
	unset( $oUser2 );

	echo( 'Загружаем сохраненного пользователя через login и сравниваем.'.PHP_EOL );

	$oUser3 = CRegistry::service('User')->getByLogin( $oUser->getProperty( 'Login' ) );
	try{

		if( $oUser->getId() !== $oUser3->getId() ) throw new CModelException( 'У объектов разные Id...' );
		if( $oUser->getProperty( 'Email', 'P' ) !== $oUser3->getProperty( 'Email', 'T' ) ) throw new CModelException( 'Поля Email у объектов разные...' );

	}catch( CModelException $e ){

		echo( 'Сравнить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};
	unset( $oUser3 );

	echo( 'Удаляем пользователя.'.PHP_EOL );
	$sUserID = $oUser->getId();
	if( !$oUser->delete() ){

		echo( 'Удалить не получилось.'.PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};

	echo( 'Пользователь удален, теперь стоит попробовать словить ошибку при его загрузке.'.PHP_EOL );
	try{

		$oUser2 = CRegistry::service('User')->getById( $sUserID );
		echo( 'Эээ... ошибка не поймалась.'.PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	}catch( CModelException $e ){

		echo( 'Попалась ошибка '.get_class( $e ).' с текстом "'.$e->getMessage().'".'.PHP_EOL );

	};

	echo( 'Осталось толкьо проверить метод верификации пользователя по паролю.'.PHP_EOL );
	if( !$oUser->checkPasswd( '123456789' ) ){

		echo( 'Проверка пароля с правильным словом не сработала...'.PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};

	echo( 'Проверка пароля с правильным словом прошла нормально.'.PHP_EOL );

	if( $oUser->checkPasswd( '013547867' ) ){

		echo( 'Проверка пароля с непраильным словом прошла успешно, это не хорошо.'.PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};

	echo( 'Проверка пароля с неправильным словом прошла как надо.'.PHP_EOL );

}catch( CModelException $e ){

	echo( 'При работе выпало исключение '.get_class( $e ).' с текстом "'.$e->getMessage().'"'.PHP_EOL );

};

?>
