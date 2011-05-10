<?php
/**
 * Реализация модели сущности "Коментарий к заметке"
 *
 * @author		Evgeniy Shatunov
 * @since		2011-05-10
 */


class CNoteComment extends CAbstractMessage {


	/** @var array								Список комментариев к данному */
	protected $aSubComments = array();


	/**
	 * Конструктор.
	 * К уже заданным свойствам добавляем еще парочку.
	 *
	 */
	public function __construct(){

		parent::__construct();

		$this->aProperties['NoteId'] = array(
			'code'		=> 'NOTE',
			'value'		=> '',
			'modified'	=> false
		);
		$this->aProperties['ParentComment'] = array(
			'code'		=> 'PARENT',
			'value'		=> '',
			'modified'	=> false
		);

	}


	/**
	 * Отдает поддерево обсуждения
	 * Поддерево может быть и пустым, но это всегда будет массив.
	 *
	 * @return array							Поддерево комментариев
	 */
	public function getSubComments(){

		return $this->aSubComments;

	}


	/**
	 * Добавляет коментарий в поддерево текущего коммента
	 *
	 * @param string		$sUAuthorId			Идентификатор пользователя - автора нового комментария.
	 * Если его не указать, авторство будет присвоено автору текущего комментария.
	 *
	 * @return CNoteComment						Подкомментарий
	 */
	public function createSubComment( $sUAuthorId = false ){

		if( $this->isNew() ) throw new CModelException( 'Can not create subcomment for nonsaved coment' );

		$oSubComment = self::create();
		$sAuthorId = ( is_string( $sUAuthorId ) )? $sUAuthorId : $this->getProperty( 'Author' );
		$oSubComment->setProperty( 'NoteId', $this->getProperty( 'NoteId' ) );
		$oSubComment->setProperty( 'Author', $sAuthorId );
		$oSubComment->setProperty( 'ParentComment', $this->getId() );

		return $oSubComment;

	}


	/**
	 * Создает объект сущности.
	 * (фабричный метод)
	 *
	 * @todo Была бы моя воля, яб весь этот копи-паст оставил бы в AbstractModel!!! У PHP проблемы с наследованием статических методов...
	 *
	 * @return object							Объект конкретной сущности.
	 */
	static public function create(){

		return new self();

	}


	/**
	 * Возвращает объект с данными по идентификатору.
	 * (фабричный метод)
	 *
	 * @todo Надо избавляться... Это все приходится таскать лишь потому, что в CAbstractModel self::create не срабатывает как положено.
	 *
	 * @param string		$sId				Идентификатор нуного объекта.
	 *
	 * @return object							Объект с данными
	 */
	static public function getById( $sId ){

		return self::getModelById( self::create(), $sId );

	}


	/**
	 * Механизм выборки списка по фильтру. Без фильтра выдаст кучу всех объектов коллекции.
	 * (фабричный метод)
	 *
	 * @param array			$aFilter			Список фильтруемых свойств
	 * @param int			$iLimit				Максимальное число выбираемых элементов
	 * @param int			$iOffset			Порядковый номер элемента, начиная с которого надо вывести список.
	 *
	 * @return array							Список объектов, полученных по фильтру, может быть и пустым
	 */
	static public function getList( $aFilter = array(), $iLimit = false, $iOffset = false ){

		return self::getModelList( self::create(), $aFilter, $iLimit, $iOffset );

	}


	/**
	 * Выборка всех комментариев для заметки в виде дерева.
	 * Результат выборки - дерево комментариев.
	 *
	 * @param string		$sNoteId			Идентификатор комментария
	 *
	 * @return array							Список комментариев к заметке в виде дерева.
	 */
	static public function getByNoteId( $sNoteId ){

		$aResult = array();

		$aComments = self::getList( array( 'NoteId' => $sNoteId ) );

		foreach( $aComments as $sId => $oComment ){

			$sParentId = $oComment->getProperty( 'ParentComment' );
			if( $sParentId ){

				$aComments[ $sParentId ]->aSubComments[ $sId ] = $oComment;

			}else{

				$aResult[ $sId ] = $oComment;

			};

		};

		return $aResult;

	}


	/**
	 * Привязка модели пользователя к колекции 'notes'
	 *
	 * @return string						Назание коллекции класса заметок
	 */
	protected function getCollectionName(){

		return 'note_comments';

	}


};
?>
