<?php
/**
 * Основной файл для подключения объектной модели "блога"
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-05
 */

// Определяем корневой каталог классов
define('BLOG_INCLUDE_ROOT', dirname(realpath(__FILE__)));

// Подключаен автозагрузчик
require_once(BLOG_INCLUDE_ROOT.'/common/CModule.php');

// Инициализируем библиотеку автозагрузчика
CModule::addAutoloadedClasses(array(

));


?>
