<?php
class User_SearchController extends Zend_Controller_Action
{
	private $form;
	private $namespace;
	private $defaultNamespace;

	public function init()
	{
		$this->namespace = new Zend_Session_Namespace('search');

		$this->defaultNamespace = new Zend_Session_Namespace('default');
		//XXX evtl. löschen:
		if (!isset($this->defaultNamespace->callingAction)) {
			$this->defaultNamespace->callingAction = '';
		}

		//instantiate User_Form_Edit, show search radio buttons
		$this->form = new User_Form_Edit(true);
		$this->form->removeElement(User::COL_ID);
		$this->form->removeElement(User::COL_ACTIVE);
		$this->form->removeElement(User::COL_PASSWORD);
		$this->form->removeElement(User_Form_Edit::PASSWORD_CLONE);
		//no reverse telephone no. search due to possible legal issues
		$this->form->removeElement(User::COL_PHONE);
		$this->form->removeElement(User::COL_FAX);
		$expElem = new Default_Form_Element_ExpertiseSelect(Expertise::COL_ID, array('label' => 'Expertise:'));
		$expElem->setOrder(8);
		$this->form->addElement($expElem, Expertise::COL_ID);
		$this->form->addElement('checkbox', 'listDetails', array('label' => 'List details', 'order' => 9));
		

		$this->form->removeElement('save');
		$this->form->addElement('submit', 'submit', array('label'=>'Search'
		));

		//set all elements to required=FALSE
		//unset all validators
		$formElements = $this->form->getElements();
		foreach ($formElements as $elem)
		{
			$elem->setRequired(false);
			$elem->clearValidators();
		}
		$this->form->setElements($formElements);
		unset($elem);
		unset($formElements);

		//$this->view->form = $this->form;
	}

	public function indexAction() {

		//$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/user/search');
		$request = $this->getRequest();
		$params = $request->getParams();

		$this->namespace->unsetAll();
		$this->namespace->searchParams = $params;

		if ($request->isPost() && $this->form->isValid($params))
		{
			$this->namespace->formValues = $this->form->getValues();
			$this->redirectTo('search');
		}
		else
		{
			//$this->form->setValues($params);
			//TODO follow-action setzen... HIER? nachfolgende Zeile zum Test...
			//$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/user/search/search');
			//$this->form->populate($params);
        //#####################new###################################         
                $this->form->setDecorators(array(
                'FormElements',
        array('HtmlTag', array('tag' => 'table', 'class' => 'login_form')),
        array('Description', array('placement' => 'prepend')),
                'Form'
                ));
                $this->form->setElementDecorators(array(
            'ViewHelper',
            'Errors',
                array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
                array(  'Label', array('tag' => 'td')),
                array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
                ));                
//                $this->form->submit->setDecorators(array(
//                array(
//            'decorator' => 'ViewHelper',
//            'options' => array('helper' => 'formSubmit')),
//                array(
//            'decorator' => array('td' => 'HtmlTag'),
//            'options' => array('tag' => 'td', 'colspan' => 2)),
//                array(
//            'decorator' => array('tr' => 'HtmlTag'),
//            'options' => array('tag' => 'tr')),
//                ));
          //###########################################################
			$this->view->form = $this->form;
		}
	}

	public function searchAction ()
	{
		//display only active users with their assigned expertise
		$request = $this->getRequest();
		$params = $this->namespace->searchParams;
		$formValues = $this->namespace->formValues;
		$userTable = new User();
		$valueTable1 = new ValueList();
		$valueTable2 = new ValueList();

		$select = $userTable->getAdapter()->select();
		$tableAdapter = $userTable->getAdapter();
		$select->from(array('user' => $userTable->getTableName()));
		//XXX remove leftJoin, only Alpha
		$select->joinLeft(array('val1' => ValueList::TABLE_NAME),
						'user.'.User::COL_INSTITUTION.'='.'val1.'.ValueList::COL_ID,
		array('Institution' => ValueList::COL_NAME));
		$select->joinLeft(array('val2' => ValueList::TABLE_NAME),
						'user.'.User::COL_COUNTRY.'='.'val2.'.ValueList::COL_ID,
		array('Country' => ValueList::COL_NAME));
		$select->joinLeft(array('userHasExpe' => UserHasExpertise::TABLE_NAME),
						'user.'.User::COL_ID.'='.'userHasExpe.'.UserHasExpertise::COL_USER_ID,
		array('expertiseId' => UserHasExpertise::COL_EXPE_ID));
		$select->joinLeft(array('expe' => Expertise::TABLE_NAME),
						'userHasExpe.'.UserHasExpertise::COL_EXPE_ID.'='.'expe.'.Expertise::COL_ID,
		array(	'eSpec' => Expertise::COL_SPECIES,
								'eArea' => Expertise::COL_AREA,
								'eSubj' => Expertise::COL_SUBJECT));
		$select->where('user.'.User::COL_ACTIVE.' = ?', 1);

		//echo $select.'<br>';

		//list details
		//handle multi select/multi checkbox values
		//concatenate strings and decorate with HTML tags for list presentation
		if ($params['listDetails'] == 1) {
			$rowSet = $tableAdapter->fetchAll($select);
			if (count($rowSet) > 0) {
				$userHasExpAsArray = array();
				foreach ($rowSet as $row) {
					if (!isset($userHasExpAsArray[$row[User::COL_ID]])) {
						$userHasExpAsArray[$row[User::COL_ID]] = '';
					}
					if ($row['expertiseId'] != NULL) {
						//$attrConcat = '<td>'.$row['eSpec'].','.$row['eArea'].','.$row['eSubj'].'</td>';
						$attrConcat = '<li>'.$row['eSpec'].','.$row['eArea'].','.$row['eSubj'].'</li>';
						$userHasExpAsArray[$row[User::COL_ID]] = $userHasExpAsArray[$row[User::COL_ID]].$attrConcat;
					}
				}
				foreach ($userHasExpAsArray as &$expList) {
					//$expList = "<table border = 'solid'><tr>".$expList.'</tr></table>';
					$expList = '<ul>'.$expList.'</ul>';
				}
				$this->view->userHasExpAsArray = $userHasExpAsArray;
			}
		}

		//handle AND/OR search
		if ($params['kind'] == 'and') {
			foreach ($formValues as $key => $value) {
				if ($key != null && $value != null && $key != 'kind' && $key != 'submit' && $key != 'listDetails')
				{
					if ($key == UserHasExpertise::COL_EXPE_ID) {
						//$tableRow = $tableAdapter->quoteIdentifier($key);
						$partStatement = $tableAdapter->quoteInto('userHasExpe.'.UserHasExpertise::COL_EXPE_ID.' = ?', $value);
						$select->where($partStatement);
					}
					elseif ($key == User::COL_COUNTRY || $key == User::COL_INSTITUTION) {
						$tableRow = $tableAdapter->quoteIdentifier($key);
						$partStatement = $tableAdapter->quoteInto($tableRow.' = ?', $value);
						$select->where($partStatement);
					} else {
						$tableRow = $tableAdapter->quoteIdentifier($key);
						$partStatement = $tableAdapter->quoteInto($tableRow.' LIKE ?', '%'.$value.'%');
						$select->where($partStatement);
					}
				}
			}
		}
		if ($params['kind'] == 'or') {
			$orWhere = '';
			foreach ($formValues as $key => $value) {
				if ($key != null && $value != null && $key != 'kind' && $key != 'submit' && $key != 'listDetails')
				{
					if ($key == UserHasExpertise::COL_EXPE_ID) {
						//$tableRow = $tableAdapter->quoteIdentifier($key);
						$partStatement = $tableAdapter->quoteInto('userHasExpe.'.UserHasExpertise::COL_EXPE_ID.' = ?', $value);
					}
					elseif ($key == User::COL_COUNTRY || $key == User::COL_INSTITUTION) {
						$tableRow = $tableAdapter->quoteIdentifier($key);
						$partStatement = $tableAdapter->quoteInto($tableRow.' = ?', $value);
					} else {
						$tableRow = $tableAdapter->quoteIdentifier($key);
						$partStatement = $tableAdapter->quoteInto($tableRow.' LIKE ?', '%'.$value.'%');
					}
					//append the where to the "where or where" container
					if (isset($partStatement)) {
						if ($orWhere == '') {
							$orWhere = $partStatement;
						} else {
							$orWhere = $orWhere.' OR '.$partStatement;
						}
					}
					unset($partStatement);
				}
			}
			//finally append the where to the select(whole metadata)
			if ($orWhere != '') {
				$select->where($orWhere);
			}
		}

		//filter double datasets caused by multiple meta data
		$select->group('user.'.User::COL_ID);

		//for setting ws-manager filter low user roles 
		if ($this->defaultNamespace->callingAction == 'workshop/edit/update') {
			$select->where('(user.'.User::COL_ROLE.' = ?', 'ws-manager');
			$select->orWhere('user.'.User::COL_ROLE.' = ?)', 'admin');
		}
		//echo $select;

		//get already assigned datasets for setting disabled in view
		if ($this->defaultNamespace->callingAction == 'ce/editparticipants/index') {
			$ceId = $this->defaultNamespace->callingActionId;
			$part = new Participant();
			$rowSet = $part->fetchAll(Participant::COL_CE_ID.'='.$ceId);
			if (count($rowSet) > 0) {
				$participants = array();
				foreach ($rowSet as $row) {
					$participants[$row[Participant::COL_USER_ID]] = TRUE;
				}
				$this->view->participants = $participants;
			}
		}

		/**
		 * Pagination control
		 */
		$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
		$paginator->setHeader(array(array('raw'=>User::COL_USERNAME,'name'=>'Username'),
		array('raw'=>User::COL_ROLE,'name'=>'User role'),
		array('raw'=>User::COL_FIRSTNAME,'name'=>'First name'),
		array('raw'=>User::COL_LASTNAME,'name'=>'Last name'),
		array('raw'=>User::COL_EMAIL,'name'=>'E-mail'),
		array('raw'=>'Institution','name'=>'Institution'),
		array('raw'=>User::COL_STREET,'name'=>'Street'),
		array('raw'=>User::COL_CITY,'name'=>'City'),
		array('raw'=>'Country','name'=>'Country'),
		//array('raw'=>'expertiseId','name'=>'expertiseId')
		));
		$paginator	->setCurrentPageNumber($this->getRequest()->getParam('page'))
		->setItemCountPerPage(1000)//$this->_getParam('itemCountPerPage'))
		->setPageRange(10)
		->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));

		Zend_View_Helper_PaginationControl::setDefaultViewPartial(
	                          'partials/list_pagination_control.phtml'); 
		$this->view->paginator = $paginator;

		// TODO im Plugin in die registry setzen, leichterer Aufruf
		// Get user_role
		$auth = Zend_Auth::getInstance();
		$storage = $auth->getStorage();
		$constUserRole = User::COL_ROLE;
		$userRole = $storage->read()->$constUserRole;
		$this->view->userRole = $userRole;

		$this->view->callingAction = $this->defaultNamespace->callingAction;
		$this->view->callingActionId = $this->defaultNamespace->callingActionId;
		//		}
	}

	public function redirectTo($action , array $params = array())
	{
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto($action,'Search','user', $params);
	}

}