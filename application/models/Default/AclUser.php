<?php
/**
 *
 * Last change: $Date$
 * Revision: $Date: 2006-07-22 21:42:37 -0700 (Sat, 22 Jul 2006) $
 * Author:   $Author$
 *
 */
class Default_AclUser extends Zend_Acl {
	
	public function __construct() {
        
		/**
		 * adding ressources
		 */
		$this->add(new Zend_Acl_Resource('CE-NEW'));
        $this->add(new Zend_Acl_Resource('CE-EDIT'));
        $this->add(new Zend_Acl_Resource('CE-EDITKEYTABLE'));
        $this->add(new Zend_Acl_Resource('CE-EDITEXPERTISE'));
        $this->add(new Zend_Acl_Resource('CE-EDITPARTICIPANTS'));
        $this->add(new Zend_Acl_Resource('CE-SEARCH'));
        $this->add(new Zend_Acl_Resource('CE-STATISTIC'));
        $this->add(new Zend_Acl_Resource('ANNOTATION-MAKE'));
        $this->add(new Zend_Acl_Resource('IMAGE-INDEX'));
        $this->add(new Zend_Acl_Resource('IMAGE-UPLOAD'));
        $this->add(new Zend_Acl_Resource('IMAGE-EDIT'));
        $this->add(new Zend_Acl_Resource('IMAGE-SEARCH'));
        $this->add(new Zend_Acl_Resource('IMAGE-BATCH'));
        $this->add(new Zend_Acl_Resource('USER-EDIT'));
        $this->add(new Zend_Acl_Resource('USER-NEW'));
        $this->add(new Zend_Acl_Resource('USER-SEARCH'));
        $this->add(new Zend_Acl_Resource('WORKSHOP-EDIT'));
        $this->add(new Zend_Acl_Resource('SERVICE-INDEX'));
        $this->add(new Zend_Acl_Resource('ADMIN-ATTRIBUTE'));
        $this->add(new Zend_Acl_Resource('ADMIN-READATTRIBUTE'));
        $this->add(new Zend_Acl_Resource('ADMIN-VALUELIST'));
        $this->add(new Zend_Acl_Resource('ADMIN-USER'));
        $this->add(new Zend_Acl_Resource('WORKSHOP-SEARCH'));
        $this->add(new Zend_Acl_Resource('WORKSHOP-INFO'));
        $this->add(new Zend_Acl_Resource('FISH-EDIT'));
        $this->add(new Zend_Acl_Resource('FISH-SEARCH'));
        $this->add(new Zend_Acl_Resource('CE-TRAINING'));
        $this->add(new Zend_Acl_Resource('ANNOTATION-BROWSE'));
        
        /**
         * adding roles
         */
		$this->addRole(new Zend_Acl_Role('reader'));
		$parent = array('reader');
		$this->addRole(new Zend_Acl_Role('datamanager'), $parent);
		array_push($parent,'datamanager');
		$this->addRole(new Zend_Acl_Role('ws-manager'), $parent);
		array_push($parent,'ws-manager');
		$this->addRole(new Zend_Acl_Role('admin'), $parent);

		/**
		 * creating the acl
		 */
		$this->allow('reader', 'USER-EDIT','UPDATE');
		$this->allow('reader', 'USER-EDIT');
		//for own training CEs
		$this->allow('reader', 'CE-EDIT', 'MYDELETERECURSIVE');
		$this->allow('reader', 'CE-SEARCH');
		$this->allow('reader', 'CE-TRAINING');
		$this->allow('reader', 'CE-STATISTIC');
		$this->allow('reader', 'ANNOTATION-MAKE');
		$this->allow('reader', 'ANNOTATION-BROWSE');
		$this->allow('reader', 'WORKSHOP-SEARCH');
		$this->allow('reader', 'FISH-SEARCH');
		$this->allow('reader', 'IMAGE-SEARCH');
		$this->allow('reader', 'USER-SEARCH');
		$this->allow('reader', 'ADMIN-READATTRIBUTE');
		$this->allow('reader', 'ADMIN-VALUELIST', 'SHOW');
		$this->allow('reader', 'SERVICE-INDEX');
		
		$this->allow('datamanager','IMAGE-UPLOAD');
		$this->allow('datamanager','IMAGE-EDIT');
		$this->allow('datamanager', 'IMAGE-INDEX');
		$this->allow('datamanager','CE-EDITKEYTABLE');
		$this->allow('datamanager','CE-EDITEXPERTISE');
		$this->allow('datamanager','IMAGE-BATCH');
		$this->allow('datamanager','FISH-EDIT');
		$this->allow('datamanager', 'CE-EDITPARTICIPANTS');
		$this->allow('datamanager', 'ADMIN-ATTRIBUTE', 'CREATEATTRIBUTECSV');
		
		
		$this->allow('ws-manager', 'CE-NEW');
		$this->allow('ws-manager', 'CE-EDIT');
//		$this->allow('ws-manager', 'CE-EDITKEYTABLE');
//		$this->allow('ws-manager', 'CE-EDITEXPERTISE');
		$this->allow('ws-manager', 'WORKSHOP-EDIT','UPDATE');
		$this->allow('ws-manager', 'WORKSHOP-EDIT','NEW');
		$this->allow('ws-manager', 'WORKSHOP-INFO');
		
		$this->allow('admin');
		
	}
}


?>