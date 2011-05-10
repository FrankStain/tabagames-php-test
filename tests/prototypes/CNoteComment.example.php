<?php
/**
 * Проверка работы комментов к заметкам
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-09
 */

error_reporting( E_ALL );

require_once( preg_replace( '/([\/\\\]tests[\/\\\].+)/', '/php.inc/include.php', __FILE__ ) );

echo( 'Проверяем работу класса заметок.'.PHP_EOL );

try{

	$oTestUsers = CRegistry::service( 'DB' )->note_comments->find( array( 'TEXT' => new MongoRegex( '/^test.+/' ) ) );
	foreach( $oTestUsers as $oId => $aUserRec ){

		CRegistry::service( 'DB' )->note_comments->remove( array( '_id' => new MongoId( $oId ) ) );
		echo( 'Удаляем коммент '.$oId.' - '.$aUserRec['TEXT'].'.'.PHP_EOL );

	};

	echo( 'Создаем тестового пользователя.'.PHP_EOL );
	$oUser = CRegistry::service( 'User' )->create();
	$oUser->setProperty( 'Login', 'test-User-for-CNote' );
	$oUser->setProperty( 'Email', 'test.mail@blogus.ru' );
	$oUser->setProperty( 'Passwd', '123456789' );
	$oUser->save();

	echo( 'Создаем тестовую заметку.'.PHP_EOL );
	$oNote =  CRegistry::service( 'Note' )->create();
	$oNote->setProperty( 'Author', $oUser->getId() );
	$oNote->setProperty( 'Title', 'test-Note-from-'.$oUser->getProperty( 'Login' ) );
	$oNote->setProperty( 'Text', 'Это просто тестовый текст для тестового сообщения.' );
	$oNote->setProperty( 'Tags', 'test TEST text user.' );
	$oNote->save();

	echo( 'Окружение создано, Id пользователя : '.$oUser->getId().'; ID заметки : '.$oNote->getId().'.'.PHP_EOL );

	echo( 'Создаем новый комментарий.'.PHP_EOL );
	$oComment =  CRegistry::service( 'NoteComment' )->create();

	if( $oComment instanceof CNoteComment ){

		echo( 'Созданный комментарий принадлежит классу CNoteComment.'.PHP_EOL );

	}else{

		echo( 'Созданный объект не имеет отношения к комментариям, класс: '.get_class( $oComment ).PHP_EOL );
		throw new CModelException( 'Надо поправить сервис NoteComment в реестре' );

	};

	echo( 'Заполняем комментарий...'.PHP_EOL );
    $oComment->setProperty( 'Author', $oUser->getId() );
    $oComment->setProperty( 'NoteId', $oNote->getId() );
    $oComment->setProperty( 'Text', 'test- Тестовый комментарий 1.' );

    $oComment->save();
	var_dump( $oComment );

	echo( 'Комментарий сохранен с ID : '.$oComment->getId().'. Теперь можно его считать из БД.'.PHP_EOL );
	try{

		echo( 'Достаем заметку по tе Id и сравниваем.'.PHP_EOL );
		$oComment2 = CRegistry::service( 'NoteComment' )->getById( $oComment->getId() );
		if( $oComment2->getProperty( 'Author', 'P' ) !== $oComment->getProperty( 'Author', 'T' ) ) throw new CModelException( 'Поля Author у объектов разные...' );
		if( $oComment2->getProperty( 'Text', 'P' ) !== $oComment->getProperty( 'Text', 'T' ) ) throw new CModelException( 'Поля Text у объектов разные...' );

	}catch( CModelException $e ){

		echo( 'Сравнить не удалось. '.$e->getMessage().PHP_EOL );
		throw new CModelException( 'Тест не прошел до конца' );

	};
	unset( $oComment2 );

	echo( 'Добавлям к имеющемуся комментарию еще один, в поддерево...'.PHP_EOL );
	$oSubComment = $oComment->createSubComment();
	$oSubComment->setProperty( 'Text', 'test- Тестовый ПОДкомментарий 1 к комментарию 1.' );
	$oSubComment->save();
	var_dump( $oSubComment );

	echo( 'А теперь рядом комментом с первым добавим еще один...'.PHP_EOL );
	$oComment3 =  CRegistry::service( 'NoteComment' )->create();
	$oComment3->setProperty( 'Author', $oUser->getId() );
    $oComment3->setProperty( 'NoteId', $oNote->getId() );
    $oComment3->setProperty( 'Text', 'test- Тестовый комментарий 2.' );
    $oComment3->save();
    var_dump( $oComment3 );

    echo( 'Остается только проверить выборку в виде дерева'.PHP_EOL );
    $aComments = CRegistry::service( 'NoteComment' )->getByNoteId( $oNote->getId() );

    if( 2 == count( $aComments ) ){

		echo( 'Основных комментариев у заметки 2, все верно.'.PHP_EOL );
		$oCommentWithNode = current( $aComments );

		if( 1 == count( $oCommentWithNode->getSubComments() ) ){

			echo( 'У первого основного комментария есть дочерний, это тоже хорошо.'.PHP_EOL );

		}else{

			echo( '$oCommentWithNode->getSubComments() должен содержать один комментарий, сейчас там :'.PHP_EOL );
			var_dump( $oCommentWithNode->getSubComments() );
			throw new CModelException( 'Надо поправить сервис NoteComment в реестре' );

		};

	}else{

		echo( '$aComments должен содержать один комментарий, сейчас там :'.get_class( $oComment ).PHP_EOL );
		var_dump( $aComments );
		throw new CModelException( 'Надо поправить сервис NoteComment в реестре' );

	};

	function printComment( $oComment, $iLevel = 1 ){

		$oUser = CRegistry::service( 'User' )->getById( $oComment->getProperty( 'Author' ) );

		echo( str_repeat( '   ', $iLevel ).'> от: '.$oUser->getProperty( 'Login' ).' ('.date( 'j.m.Y H:i:s', $oComment->getProperty( 'Date' ) ).')'.PHP_EOL );
		echo( str_repeat( '   ', $iLevel ).'> '.$oComment->getProperty( 'Text' ).PHP_EOL );

		$aNodes = $oComment->getSubComments();
		if( count( $aNodes ) ){

			foreach( $aNodes as $oNode ){

				printComment( $oNode, $iLevel + 1 );

			};

		};

	};

	echo( PHP_EOL.'Вот как все может выглядеть на деле : '.PHP_EOL );
	echo( '--- Заметка --------------------------------------------------------------'.PHP_EOL );
	echo( '> от: '.$oUser->getProperty( 'Login' ).' ('.date( 'j.m.Y H:i:s', $oNote->getProperty( 'Date' ) ).')'.PHP_EOL );
	echo( '> Тема: "'.$oNote->getProperty( 'Title' ).'"'.PHP_EOL );
	echo( $oNote->getProperty( 'Text' ).PHP_EOL );
	echo( '--- Комментарии ----------------------------------------------------------'.PHP_EOL );
	foreach( $aComments as $oComment ){

		printComment( $oComment );

	};
	echo( '--------------------------------------------------------------------------'.PHP_EOL );
	echo( PHP_EOL.'всё, пример использования завершен.'.PHP_EOL );


	echo( 'Удаляем заметку и пользователя...'.PHP_EOL );
	$oNote->delete();
	$oUser->delete();

}catch( CModelException $e ){

	echo( 'При работе выпало исключение '.get_class( $e ).' с текстом "'.$e->getMessage().'"'.PHP_EOL );

};

?>
