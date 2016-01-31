<?php
class Default_Form_AttributeSelectContainer extends Zend_Form {

	//show the attributes for a specific entity
	//add and delete specific attributes for an entity
	//uses cross table
	//e.g. ce, ce_has_atde
	//
	//entity: name of entity that contains attributes
	//entityId: primary key

	public function __construct($table, $tableRow, $entityId, $group = '')
	{
		parent::__construct();
		
		$this->addElement(new Default_Form_Element_AttributeSelect('attr', '', $group));
		$this->addElement('submit','submit',array('label'=>'Add attribute to list'));
		
		//getElement -> get selectbox to delete selectbox
		//setElement -> update selected from selectbox

		//select entity
		//foreach row addelem attributeselect AND optional constrain array for key value, depending on attributedescription

		//return elementarray + links "delete"
		//return selectbox + link "add"
//		$tableAdapter = $table->getAdapter();
//		$select = $tableAdapter->select();
//		$select->from($table->getTableName());
//		$select->join(AttributeDescriptor::TABLE_NAME, $table->getTableName().'.ATDE_ID = '.AttributeDescriptor::TABLE_NAME.'.ATDE_ID', array(AttributeDescriptor::COL_NAME));
//		$partStatement = $tableAdapter->quoteInto($tableRow." = ?", $entityId);
//		$select->where($partStatement);
//		//show only attributes that are allowed to be shown in list
//		$partStatement = $tableAdapter->quoteInto(AttributeDescriptor::COL_SHOW_IN_LIST." = ?", 1);
//		$select->where($partStatement);
//		$result = $tableAdapter->fetchAll($select);

		//TODO handle empty result
		/*		if (($result)==NULL)
		 {
			return false;
			}
			else
			{
			return true;
			}*/

		//neue selectbox mit decorator link
//		
//		$attr->setDescription("<a href='/default/attribute/add/'>Add to list</a>");
//		->setDecorators(array(
//        	'ViewHelper',
//		array('Description', array('escape' => false, 'tag' => false)),
//		array('HtmlTag', array('tag' => 'dd')),
//		array('Label', array('tag' => 'dt')),
//        	'Errors',
//		));
//		$this->addElement($attr);
	}
}