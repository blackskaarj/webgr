<?php

class Admin_UserController extends Zend_Controller_Action {

    private $form;
    
    public function init() {
        $this->form = new User_Form_Edit();
        $this->form->removeElement(User::COL_PASSWORD);
        $this->form->removeElement(User_Form_Edit::PASSWORD_CLONE);
        
        $roleElement = new Zend_Form_Element_Select(User::COL_ROLE);
        $roleElement->setRequired(true);
        $roleElement->setOrder(1);
        $roleElement->setMultiOptions(array('reader'=>'reader',
                                            'datamanager'=>'datamanager',
                                            'ws-manager'=>'ws-manager',
                                            'admin'=>'admin'));
        $this->form->addElement($roleElement,null,array('pos'=>1));
        
        $this->view->form = $this->form;
    }
    
    public function updateAction() {
        
        $userTable = new User();
        
        if ($this->getRequest()->isPost()){
            if($this->form->isValid($this->getRequest()->getParams())) {
                 $data = array(  User::COL_CITY => $this->form->getValue(User::COL_CITY),
                                 User::COL_USERNAME => $this->form->getValue(User::COL_USERNAME),
                                 User::COL_EMAIL => $this->form->getValue(User::COL_USERNAME),
                                 User::COL_COUNTRY => $this->form->getValue(User::COL_COUNTRY),
                                 User::COL_FAX => $this->form->getValue(User::COL_FAX),
                                 User::COL_FIRSTNAME => $this->form->getValue(User::COL_FIRSTNAME),
                                 User::COL_LASTNAME => $this->form->getValue(User::COL_LASTNAME),
                                 User::COL_INSTITUTION => $this->form->getValue(User::COL_INSTITUTION),
                                 User::COL_PHONE => $this->form->getValue(User::COL_PHONE),
                                 User::COL_ACTIVE => $this->form->getValue(User::COL_ACTIVE),
                                 User::COL_ROLE => $this->form->getValue(User::COL_ROLE),
                                 User::COL_STREET => $this->form->getValue(User::COL_STREET));
                 $updateResult = 0;
                 try{
                     $updateResult = @$userTable->update($data,$userTable->getAdapter()->quoteInto(User::COL_ID . "=?",$this->form->getValue(User::COL_ID)));
                 }
                 catch(Exception $e){
                     if ($updateResult == 0){
                        $this->view->message = 'Please try another username!';
                     }
                 }
            }
        }else{
            $userResult = $userTable->find($this->getRequest()->getParam(User::COL_ID))->current();
            $this->form->populate($userResult->toArray());
        }
        
    }
}
?>