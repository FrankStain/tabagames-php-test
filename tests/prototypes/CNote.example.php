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

		CRegistry::service( 'DB' )->notes->remove( array( '_id' => new MongoId( $oId ) ) );
		echo( 'Удаляем заметку '.$oId.' - '.$aUserRec['TITLE'].'.'.PHP_EOL );

	};

	echo( 'Создаем тестового пользователя.'.PHP_EOL );
	$oUser = CRegistry::service( 'User' )->create();
	$oUser->setProperty( 'Login', 'test-User-for-CNote' );
	$oUser->setProperty( 'Email', 'test.mail@blogus.ru' );
	$oUser->setProperty( 'Passwd', '123456789' );
	$oUser->save();

	echo( 'Создаем новую заметку.'.PHP_EOL );
	$oNote =  CRegistry::service( 'Note' )->create();

	if( $oNote instanceof CNote ){

		echo( 'Созданная заметка принадлежит классу CNote.'.PHP_EOL );

	}else{

		echo( 'Созданный объект не имеет отношения к заметкам, класс: '.get_class( $oNote ).PHP_EOL );
		throw new CModelException( 'Надо поправить сервис Note в реестре' );

	};

	echo( 'Заполняем заметку...'.PHP_EOL );
	$oNote->setProperty( 'Author', $oUser->getId() );
	$oNote->setProperty( 'Title', 'test-Note-from-'.$oUser->getProperty( 'Login' ) );
	$oNote->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote->setProperty( 'Tags', 'test TEST text user.' );
	// А Date указывается автоматом при создании объекта.

	$oNote->save();
	var_dump( $oNote );

	echo( 'Заметка сохранена, теперь можно прочитать ее по Id или логину пользователя.'.PHP_EOL );

	try{

		echo( 'Достаем заметку по tе Id и сравниваем.'.PHP_EOL );
		$oNote2 = CRegistry::service( 'Note' )->getById( $oNote->getId() );
		if( $oNote->getProperty( 'Author', 'P' ) !== $oNote2->getProperty( 'Author', 'T' ) ) throw new CModelException( 'Поля Author у объектов разные...' );
		if( $oNote->getProperty( 'Title', '@#$%^&' ) !== $oNote2->getProperty( 'Title', '(*&%^$%' ) ) throw new CModelException( 'Поля Title у объектов разные...' );
		if( $oNote->getProperty( 'Text', 'P' ) !== $oNote2->getProperty( 'Text', 'T' ) ) throw new CModelException( 'Поля Text у объектов разные...' );
		if( $oNote->getProperty( 'Tags', '@#$%^&' ) !== $oNote2->getProperty( 'Tags', '(*&%^$%' ) ) throw new CModelException( 'Поля Tags у объектов разные...' );

	}catch( CModelException $e ){

		echo( 'Сравнить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};
	unset( $oNote2 );

	try{

		echo( 'Достаем заметку по Id пользователя и сравниваем.'.PHP_EOL );
		$oNote2 = array_pop( CRegistry::service( 'Note' )->getListByAuthorId( $oUser->getId() ) );
		if( !is_object( $oNote2 ) ) throw new CModelException( 'А Note2 даже и не объект, как выяснилось...' );
		if( $oNote->getId() !== $oNote2->getId() ) throw new CModelException( 'У объектов разные Id...' );

	}catch( CModelException $e ){

		echo( 'Сравнить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};
	unset( $oNote2 );

	$aNotes = array();

	$aNotes[] = $oNote2 = CRegistry::service( 'Note' )->create();
	$oNote2->setProperty( 'Author', $oUser->getId() );
	$oNote2->setProperty( 'Title', 'test-Note-1-from-'.$oUser->getProperty( 'Login' ) );
	$oNote2->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote2->setProperty( 'Tags', 'test apple' );
	$oNote2->setProperty( 'Date', $oNote->getProperty( 'Date' ) + 16 );
	$oNote2->save();

	$aNotes[] = $oNote2 = CRegistry::service( 'Note' )->create();
	$oNote2->setProperty( 'Author', $oUser->getId() );
	$oNote2->setProperty( 'Title', 'test-Note-2-from-'.$oUser->getProperty( 'Login' ) );
	$oNote2->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote2->setProperty( 'Tags', 'apple red' );
	$oNote2->setProperty( 'Date', $oNote->getProperty( 'Date' ) + 24 );
	$oNote2->save();

	$aNotes[] = $oNote2 = CRegistry::service( 'Note' )->create();
	$oNote2->setProperty( 'Author', $oUser->getId() );
	$oNote2->setProperty( 'Title', 'test-Note-3-from-'.$oUser->getProperty( 'Login' ) );
	$oNote2->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote2->setProperty( 'Tags', 'test red' );
	$oNote2->setProperty( 'Date', $oNote->getProperty( 'Date' ) + 32 );
	$oNote2->save();

	$aNotes[] = $oNote2 = CRegistry::service( 'Note' )->create();
	$oNote2->setProperty( 'Author', $oUser->getId() );
	$oNote2->setProperty( 'Title', 'test-Note-4-from-'.$oUser->getProperty( 'Login' ) );
	$oNote2->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote2->setProperty( 'Tags', 'русотест apple' );
	$oNote2->setProperty( 'Date', $oNote->getProperty( 'Date' ) + 40 );
	$oNote2->save();

	$aNotes[] = $oNote2 = CRegistry::service( 'Note' )->create();
	$oNote2->setProperty( 'Author', $oUser->getId() );
	$oNote2->setProperty( 'Title', 'test-Note-5-from-'.$oUser->getProperty( 'Login' ) );
	$oNote2->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote2->setProperty( 'Tags', 'reds руссотест' );
	$oNote2->setProperty( 'Date', $oNote->getProperty( 'Date' ) + 48 );
	$oNote2->save();

	try{

		echo( 'Достаем заметки за промежуток времени.'.PHP_EOL );
		$aNotesOnDate = CRegistry::service( 'Note' )->getListByDateRange( $oNote->getProperty( 'Date' ) + 18, $oNote->getProperty( 'Date' ) + 44 );
		if( !is_array( $aNotesOnDate ) ) throw new CModelException( 'В $aNotesOnDate что то другое, но не список заметок.' );
		if( 3 != count( $aNotesOnDate ) ) throw new CModelException( 'Было задано ровно 3 заметки в этом промежутке, а выбралось '.count( $aNotesOnDate ) );
		foreach( $aNotesOnDate as $sId => $oTimedNote ){

			echo( 'ID: '.$sId.'; Title : '.$oTimedNote->getProperty( 'Title' ).'.'.PHP_EOL );

		};

	}catch( CModelException $e ){

		echo( 'Проверить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};

	try{

		echo( 'Достаем заметки по тегу red.'.PHP_EOL );
		$aTaggedNotes = CRegistry::service( 'Note' )->getListByTags( array( 'red' ) );
		if( !is_array( $aTaggedNotes ) ) throw new CModelException( 'В $aTaggedNotes что то другое, но не список заметок.' );
		if( 3 != count( $aTaggedNotes ) ) throw new CModelException( 'Было задано ровно 3 заметки в этом промежутке, а выбралось '.count( $aTaggedNotes ) );
		foreach( $aTaggedNotes as $sId => $oTaggedNote ){

			echo( 'ID: '.$sId.'; Title : '.$oTaggedNote->getProperty( 'Title' ).'; Tags: '.$oTaggedNote->getProperty( 'Tags' ).'.'.PHP_EOL );

		};

	}catch( CModelException $e ){

		echo( 'Проверить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};

	echo( 'Удаляем тестового пользователя.'.PHP_EOL );

	$oUser->delete();

}catch( CModelException $e ){

	echo( 'При работе выпало исключение '.get_class( $e ).' с текстом "'.$e->getMessage().'"'.PHP_EOL );

};

?>
