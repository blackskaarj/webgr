<?php
//only new CE with basic dates, for creating referenced tables see Ce_EditController
class Ce_NewController extends Zend_Controller_Action
{
	private $form;
	private $callingWorkshopId;

	public function init()
	{
		//get workshop ID from calling workshop
		$this->callingWorkshopId = $this->_getParam(Workshop::COL_ID);

		if ($this->callingWorkshopId == NULL)
		{
			$this->_forward("index","index");
		}
			$this->form = new Ce_Form_EditBasicElements();
			$this->form->removeElement(CalibrationExercise::COL_ID);
			$this->view->form = $this->form;
	}

	public function indexAction()
	{
		$namespace = new Zend_Session_Namespace('ce');
		if ($this->getRequest()->isPost())
		{
			if ($this->form->isValid($this->getRequest()->getParams()))
			{
				if ($this->form->getValue('Token') == $namespace->Token)
				{
					$ceTable = new CalibrationExercise();
					$data = array(CalibrationExercise::COL_NAME=>$this->form->getValue(CalibrationExercise::COL_NAME),
					CalibrationExercise::COL_DESCRIPTION=>$this->form->getValue(CalibrationExercise::COL_DESCRIPTION),
					//TODO: set wsID from namespace or set specialID for training exercises
					CalibrationExercise::COL_WORKSHOP_ID=>$this->callingWorkshopId);
					$ceId = $ceTable->insert($data);
					$namespace->Token = '';
                                        $this->_forward("index","edit","ce",array("CAEX_ID" => $ceId));
				}
				else
				{
					//form token is not equal session token
					$this->form->reset();

					$this->redirectTo('index');
				}
			}
			else
			{
				//not valid
			}

		}
		else
		{
			//not post
			
			
			if ($this->form->getValue('Token') == null)
			{
				$guid = new Ble422_Guid();
				$namespace->Token = $guid->__toString();
				$this->form->getElement('Token')->setValue($guid->__toString());
				$this->view->form = $this->form;
			}
		}
	}

	public function successAction() {
		echo '-sucessAction-';
	}

	public function redirectTo($action)
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto($action,'new','ce');
	}
}
?>