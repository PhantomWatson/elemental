<?php
App::uses('AppController', 'Controller');
/**
 * Articles Controller
 *
 * @property Article $Article
 */
class ArticlesController extends AppController {
	public $helpers = array('Tinymce');
	
	public $paginate = array(
		'limit' => 25,
		'order' => array(
			'Article.created' => 'desc'
        )
    );
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny(array('add', 'edit', 'delete', 'manage'));
	}

	public function isAuthorized($user) {
		return parent::isAuthorized($user);
	}
	
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Article->recursive = 0;
		$this->set(array(
			'articles' => $this->paginate(),
			'title_for_layout' => 'Elemental News'
		));
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Article->id = $id;
		if (!$this->Article->exists()) {
			throw new NotFoundException(__('Invalid article'));
		}
		$article = $this->Article->read(null, $id);
		$this->set(array(
			'article' => $article,
			'title_for_layout' => $article['Article']['title']
		));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Article->create();
			$this->request->data['Article']['user_id'] = $this->Auth->user('id');
			if ($this->Article->save($this->request->data)) {
				$this->Flash->success('The article has been saved');
				$this->redirect(array(
					'action' => 'view', 
					'id' => $this->Article->id
				));
			} else {
				$this->Flash->error('The article could not be saved. Please, try again.');
			}
		}
		$this->set('title_for_layout', 'Post New Article');
		$this->render('form');
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Article->id = $id;
		if (!$this->Article->exists()) {
			throw new NotFoundException(__('Invalid article'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Article->save($this->request->data)) {
				$this->Flash->success('The article has been saved');
				$this->redirect(array('action' => 'view', 'id' => $id));
			} else {
				$this->Flash->error('The article could not be saved. Please, try again.');
			}
		} else {
			$this->request->data = $this->Article->read(null, $id);
		}
		$users = $this->Article->User->find('list');
		$this->set(compact('users'));
		$this->set('title_for_layout', 'Edit Article');
		$this->render('form');
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Article->id = $id;
		if (!$this->Article->exists()) {
			throw new NotFoundException(__('Invalid article'));
		}
		if ($this->Article->delete()) {
			$this->Flash->success(__('Article deleted'));
			$this->redirect(array('action' => 'manage'));
		}
		$this->Flash->error(__('Article was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

	public function manage() {
		$this->Article->recursive = 0;
		$this->set(array(
			'title_for_layout' => 'Manage Articles',
			'articles' => $this->paginate()
		));
	}
}
