<?php
class InstallController extends Zend_Controller_Action
{

	public function indexAction()
	{
		$form = new Zend_Form();
		$form->addElement('text','seckey',array('label'=>'Security Key'));
		$form->addElement('submit','submit',array('label'=>'install'));
		$form->setMethod('post');

		if($this->getRequest()->isPost()){
			if(Zend_Registry::get("SECURITY_KEY") == "write what ever you want but make it unique!"){
				$this->view->massage = 'Warning: You have to change your security key at first in your config file!';
			} else {
				if($form->populate($this->getRequest()->getParams()) && $form->getValue('seckey') == Zend_Registry::get("SECURITY_KEY")){
					$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
					$error = false;
					$errorMassage = "";
					try {
						$tableList = $dbAdapter->listTables();
					} catch (Exception $e) {
						$error = true;
						$errorMassage = "Can't access the db.";
					}
					$params = $dbAdapter->getConfig();
					if(!$error){
						if(count($tableList) == 0){
							/*
							 * TODO for at script changing
							 * - remove security for the views
							 * - remove the last semicolon
							 * - remove comments
							 */
							$installScript = "
		        		    
		        		    SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
		        		    CREATE TABLE IF NOT EXISTS `annotations` (
							  `ANNO_ID` int(11) unsigned NOT NULL auto_increment,
							  `CEhIM_ID` int(10) unsigned NOT NULL,
							  `PARENT_ID` int(11) unsigned default NULL,
							  `PART_ID` int(8) unsigned NOT NULL,
							  `ANNO_COMMENT` text,
							  `ANNO_DATE` datetime default NULL,
							  `ANNO_GROUP` tinyint(1) unsigned NOT NULL default '0',
							  `ANNO_WS_REF` tinyint(1) unsigned NOT NULL default '0',
							  `ANNO_WEBGR_REF` tinyint(1) unsigned NOT NULL default '0',
							  `ANNO_COUNT` int(4) unsigned NOT NULL,
							  `ANNO_DECIMAL` decimal(7,0) NOT NULL,
							  `ANNO_SUB` varchar(10) default NULL,
							  `ANNO_BRIGHTNESS` decimal(10,0) default NULL,
							  `ANNO_CONTRAST` decimal(10,0) default NULL,
							  `ANNO_COLOR` decimal(10,0) default NULL,
							  `ANNO_MAGNIFICATION` decimal(10,0) default NULL,
							  `ANNO_FINAL` tinyint(1) NOT NULL,
							  `ANNO_CREATE_DATE` timestamp NOT NULL default CURRENT_TIMESTAMP,
							  PRIMARY KEY  (`ANNO_ID`),
							  KEY `ANNOTATIONS_FKIndex1` (`PART_ID`),
							  KEY `ANNOTATIONS_FKIndex2` (`PARENT_ID`),
							  KEY `ANNOTATIONS_FKIndex3` (`CEhIM_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42 ;
							
							CREATE TABLE IF NOT EXISTS `attribute_desc` (
							  `ATDE_ID` int(5) NOT NULL auto_increment,
							  `USER_ID` int(7) unsigned NOT NULL,
							  `ATDE_NAME` varchar(255) character set latin1 NOT NULL,
							  `ATDE_UNIT` varchar(255) character set latin1 default NULL,
							  `ATDE_DESCRIPTION` text character set latin1,
							  `ATDE_DEFAULT` varchar(4000) character set latin1 default NULL,
							  `ATDE_REQUIRED` tinyint(1) unsigned NOT NULL default '0',
							  `ATDE_IS_STANDARD` tinyint(1) unsigned NOT NULL default '0',
							  `ATDE_ACTIVE` tinyint(1) unsigned NOT NULL default '1',
							  `ATDE_DATATYPE` varchar(20) character set latin1 NOT NULL,
							  `ATDE_FORMTYPE` varchar(20) character set latin1 NOT NULL,
							  `ATDE_VALUELIST` tinyint(1) unsigned NOT NULL default '0',
							  `ATDE_SEQUENCE` int(3) unsigned default '0',
							  `ATDE_MULTIPLE` tinyint(1) unsigned NOT NULL default '0',
							  `ATDE_SHOWINLIST` tinyint(1) unsigned NOT NULL default '0',
							  `ATDE_GROUP` varchar(20) character set latin1 NOT NULL,
							  `ATDE_FILTERS` varchar(4000) character set latin1 default NULL,
							  `ATDE_VALIDATORS` varchar(4000) character set latin1 default NULL,
							  PRIMARY KEY  (`ATDE_ID`),
							  KEY `ATTRIBUTE_DESC_FISH_FKIndex1` (`USER_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=509 ;
							
							CREATE TABLE IF NOT EXISTS `caex_has_atde` (
							  `CAEX_ID` int(6) unsigned NOT NULL,
							  `ATDE_ID` int(5) NOT NULL,
							  PRIMARY KEY  (`CAEX_ID`,`ATDE_ID`),
							  KEY `CAEX_has_ATDEF_FKIndex1` (`CAEX_ID`),
							  KEY `CAEX_has_ATDEF_FKIndex2` (`ATDE_ID`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='shown attributes';
							
							CREATE TABLE IF NOT EXISTS `calibration_exercise` (
							  `CAEX_ID` int(6) unsigned NOT NULL auto_increment,
							  `KETA_ID` int(5) unsigned default NULL,
							  `WORK_ID` int(5) unsigned default NULL,
							  `EXPE_ID` int(5) unsigned default NULL,
							  `CAEX_NAME` varchar(255) NOT NULL,
							  `CAEX_DESCRIPTION` text,
							  `CAEX_COMPAREABLE` tinyint(1) unsigned NOT NULL default '0',
							  `CAEX_RANDOMIZED` int(1) unsigned default NULL,
							  `CAEX_IS_STOPPED` tinyint(1) unsigned NOT NULL default '1',
							  `CAEX_TRAINING` tinyint(1) unsigned NOT NULL default '0',
							  PRIMARY KEY  (`CAEX_ID`),
							  KEY `CALIBRATION_EXERCISE_FKIndex1` (`KETA_ID`),
							  KEY `CALIBRATION_EXERCISE_FKIndex2` (`EXPE_ID`),
							  KEY `CALIBRATION_EXERCISE_FKIndex3` (`WORK_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;
							
							CREATE TABLE IF NOT EXISTS `ce_has_image` (
							  `CEhIM_ID` int(10) unsigned NOT NULL auto_increment,
							  `CAEX_ID` int(6) unsigned NOT NULL,
							  `IMAGE_ID` int(9) unsigned NOT NULL,
							  PRIMARY KEY  (`CEhIM_ID`),
							  KEY `SUBSET_HAS_IMAGE_FKIndex1` (`IMAGE_ID`),
							  KEY `CE_HAS_IMAGE_FKIndex2` (`CAEX_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;
							
							CREATE TABLE IF NOT EXISTS `dots` (
							  `DOTS_ID` int(11) unsigned NOT NULL auto_increment,
							  `ANNO_ID` int(11) unsigned NOT NULL,
							  `DOTS_X` float default NULL,
							  `DOTS_Y` float default NULL,
							  `DOTS_SEQUENCE` int(2) unsigned NOT NULL,
							  PRIMARY KEY  (`DOTS_ID`),
							  KEY `DOTS_FKIndex1` (`ANNO_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=217 ;
							
							CREATE TABLE IF NOT EXISTS `expertise` (
							  `EXPE_ID` int(5) unsigned NOT NULL auto_increment,
							  `EXPE_SPECIES` varchar(255) NOT NULL,
							  `EXPE_AREA` varchar(255) NOT NULL,
							  `EXPE_SUBJECT` varchar(255) NOT NULL,
							  PRIMARY KEY  (`EXPE_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;
							
							CREATE TABLE IF NOT EXISTS `fish` (
							  `FISH_ID` int(9) unsigned NOT NULL auto_increment,
							  `FISH_SAMPLE_CODE` varchar(50) NOT NULL,
							  `USER_ID` int(7) unsigned NOT NULL COMMENT 'owner of data/metadata',
							  PRIMARY KEY  (`FISH_ID`),
							  UNIQUE KEY `FISH_SAMPLE_CODE_2` (`FISH_SAMPLE_CODE`),
							  KEY `FISH_SAMPLE_CODE` (`FISH_SAMPLE_CODE`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=463 ;
							
							CREATE TABLE IF NOT EXISTS `image` (
							  `IMAGE_ID` int(9) unsigned NOT NULL auto_increment,
							  `USER_ID` int(7) unsigned NOT NULL COMMENT 'owner of file and data/metadata',
							  `FISH_ID` int(9) unsigned NOT NULL,
							  `IMAGE_CHECKSUM` varchar(50) default NULL,
							  `IMAGE_GUID` char(36) NOT NULL,
							  `IMAGE_ORIGINAL_CHECKSUM` varchar(50) NOT NULL,
							  `IMAGE_ORIGINAL_FILENAME` varchar(255) NOT NULL,
							  `IMAGE_DIM_X` int(4) NOT NULL,
							  `IMAGE_DIM_Y` int(4) NOT NULL,
							  `IMAGE_RATIO_EXTERNAL` float default NULL COMMENT 'ratio (calculated WebGR external) between pixel and physical length in micrometer',
                              `IMAGE_RATIO_INTERNAL` float default NULL COMMENT 'ratio (calculated WebGR internal) between pixel and physical length in micrometer',
                              `IMAGE_SHRINKED_RATIO` float default NULL COMMENT 'ratio between shrinked working copy and original image',
							  PRIMARY KEY  (`IMAGE_ID`),
							  KEY `IMAGE_FKIndex2` (`FISH_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=144 ;
							
							CREATE TABLE IF NOT EXISTS `imageset_attributes` (
							  `IMAT_ID` int(6) unsigned NOT NULL auto_increment,
							  `ATDE_ID` int(5) NOT NULL,
							  `CAEX_ID` int(6) unsigned NOT NULL,
							  `VALI_ID` int(5) unsigned default NULL,
							  `VALUE` varchar(255) default NULL,
							  `IMAT_FROM` varchar(4000) default NULL,
							  `IMAT_TO` varchar(4000) default NULL,
							  PRIMARY KEY  (`IMAT_ID`),
							  KEY `COLLECTION_ATTRIBUTES_FKIndex4` (`VALI_ID`),
							  KEY `COLLECTION_ATTRIBUTES_FKIndex1` (`CAEX_ID`),
							  KEY `IMAGESET_ATTRIBUTES_FKIndex3` (`ATDE_ID`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
							
							CREATE TABLE IF NOT EXISTS `key_table` (
							  `KETA_ID` int(5) unsigned NOT NULL auto_increment,
							  `KETA_AREA` varchar(255) NOT NULL,
							  `KETA_SPECIES` varchar(255) NOT NULL,
							  `KETA_AGE` tinyint(1) unsigned default NULL,
							  `KETA_MATURITY` tinyint(1) unsigned default NULL,
							  `KETA_NAME` varchar(255) NOT NULL,
							  `KETA_SUBJECT` varchar(255) NOT NULL COMMENT 'replaces KETA_AGE and _MATURITY',
							  `KETA_FILENAME` varchar(259) character set utf8 collate utf8_unicode_ci NOT NULL,
							  PRIMARY KEY  (`KETA_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;
							
							CREATE TABLE IF NOT EXISTS `meta_data_fish` (
							  `MEDFI_ID` int(11) unsigned NOT NULL auto_increment,
							  `ATDE_ID` int(5) NOT NULL,
							  `FISH_ID` int(9) unsigned NOT NULL,
							  `MEDFI_VALUE` varchar(4000) default NULL,
							  PRIMARY KEY  (`MEDFI_ID`),
							  KEY `META_DATA_FKIndex3` (`FISH_ID`),
							  KEY `META_DATA_FISH_FKIndex2` (`ATDE_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=154 ;
							
							CREATE TABLE IF NOT EXISTS `meta_data_image` (
							  `MEDIM_ID` int(11) unsigned NOT NULL auto_increment,
							  `ATDE_ID` int(5) NOT NULL,
							  `IMAGE_ID` int(9) unsigned NOT NULL,
							  `MEDIM_VALUE` varchar(4000) default NULL,
							  PRIMARY KEY  (`MEDIM_ID`),
							  KEY `META_DATA_IMAGE_FKIndex1` (`IMAGE_ID`),
							  KEY `META_DATA_IMAGE_FKIndex2` (`ATDE_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=51 ;
							
							CREATE TABLE IF NOT EXISTS `participant` (
							  `PART_ID` int(8) unsigned NOT NULL auto_increment,
							  `CAEX_ID` int(6) unsigned NOT NULL,
							  `USER_ID` int(7) unsigned NOT NULL,
							  `PART_EXPERTISE_LEVEL` varchar(30) default NULL,
							  `PART_STOCK_ASSESSMENT` tinyint(1) unsigned NOT NULL default '0',
							  `PART_PARTICIPANT_ROLE` varchar(30) NOT NULL default 'Reader',
							  `PART_NUMBER` int(3) unsigned NOT NULL,
							  PRIMARY KEY  (`PART_ID`),
							  KEY `PARTICIPANT_FKIndex1` (`USER_ID`),
							  KEY `PARTICIPANT_FKIndex2` (`CAEX_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;
							
							CREATE TABLE IF NOT EXISTS `user` (
							  `USER_ID` int(7) unsigned NOT NULL auto_increment,
							  `USER_USERNAME` varchar(255) NOT NULL,
							  `USER_LASTNAME` varchar(255) NOT NULL,
							  `USER_FIRSTNAME` varchar(255) NOT NULL,
							  `USER_PASSWORD` varchar(255) NOT NULL,
							  `USER_E_MAIL` varchar(255) NOT NULL,
							  `USER_INSTITUTION` varchar(255) default NULL,
							  `USER_STREET` varchar(255) default NULL,
							  `USER_COUNTRY` varchar(255) default NULL,
							  `USER_PHONE` varchar(255) default NULL,
							  `USER_FAX` varchar(255) default NULL,
							  `USER_CITY` varchar(255) default NULL,
							  `USER_ACTIVE` tinyint(1) unsigned NOT NULL default '1',
							  `USER_ROLE` varchar(11) NOT NULL default 'reader',
							  `USER_GUID` varchar(40) default NULL,
							  PRIMARY KEY  (`USER_ID`),
							  UNIQUE KEY `USERNAME_UNIQUE` (`USER_USERNAME`),
							  UNIQUE KEY `USER_GUID` (`USER_GUID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;
							
							CREATE TABLE IF NOT EXISTS `user_has_expertise` (
							  `USER_ID` int(7) unsigned NOT NULL,
							  `EXPE_ID` int(5) unsigned NOT NULL,
							  PRIMARY KEY  (`USER_ID`,`EXPE_ID`),
							  KEY `USER_has_STOCK_FKIndex1` (`USER_ID`),
							  KEY `USER_has_EXPERTISE_FKIndex2` (`EXPE_ID`)
							) ENGINE=InnoDB DEFAULT CHARSET=utf8;
							
							CREATE TABLE IF NOT EXISTS `value_list` (
							  `VALI_ID` int(5) unsigned NOT NULL auto_increment,
							  `ATDE_ID` int(5) NOT NULL,
							  `VALI_NAME` varchar(255) default NULL,
							  `VALI_VALUE` varchar(4000) default NULL,
							  PRIMARY KEY  (`VALI_ID`),
							  KEY `VALUE_LIST_FKIndex1` (`ATDE_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1021 ;
							
							CREATE TABLE IF NOT EXISTS `v_all_annotations` (
							`ANNO_ID` int(11) unsigned
							,`CEhIM_ID` int(10) unsigned
							,`PARENT_ID` int(11) unsigned
							,`PART_ID` int(8) unsigned
							,`ANNO_COMMENT` text
							,`ANNO_DATE` datetime
							,`ANNO_GROUP` tinyint(1) unsigned
							,`ANNO_WS_REF` tinyint(1) unsigned
							,`ANNO_WEBGR_REF` tinyint(1) unsigned
							,`ANNO_COUNT` int(4) unsigned
							,`ANNO_DECIMAL` decimal(7,0)
							,`ANNO_SUB` varchar(10)
							,`ANNO_BRIGHTNESS` decimal(10,0)
							,`ANNO_CONTRAST` decimal(10,0)
							,`ANNO_COLOR` decimal(10,0)
							,`ANNO_MAGNIFICATION` decimal(10,0)
							,`ANNO_FINAL` tinyint(1)
							,`ANNO_CREATE_DATE` timestamp
							,`PART_NUMBER` int(3) unsigned
							,`IMAGE_ID` int(9) unsigned
							,`CAEX_ID` int(6) unsigned
							,`KETA_ID` int(5) unsigned
							,`WORK_ID` int(5) unsigned
							,`EXPE_ID` int(5) unsigned
							,`CAEX_NAME` varchar(255)
							,`CAEX_DESCRIPTION` text
							,`CAEX_COMPAREABLE` tinyint(1) unsigned
							,`CAEX_RANDOMIZED` int(1) unsigned
							,`CAEX_IS_STOPPED` tinyint(1) unsigned
							,`CAEX_TRAINING` tinyint(1) unsigned
							,`WORK_NAME` varchar(255)
							,`IMAGE_ORIGINAL_FILENAME` varchar(255)
							,`KETA_NAME` varchar(255)
							);

							CREATE TABLE IF NOT EXISTS `v_ce_list` (
							`CAEX_ID` int(6) unsigned
							,`CAEX_DESCRIPTION` text
							,`CAEX_NAME` varchar(255)
							,`CAEX_TRAINING` tinyint(1) unsigned
							,`KETA_ID` int(5) unsigned
							,`EXPE_ID` int(5) unsigned
							,`WORK_NAME` varchar(255)
							,`WORK_ID` int(5) unsigned
							);
							
							CREATE TABLE IF NOT EXISTS `v_fish_info` (
							`FISH_SAMPLE_CODE` varchar(50)
							,`MEDFI_VALUE` varchar(4000)
							,`ATDE_NAME` varchar(255)
							,`ATDE_UNIT` varchar(255)
							,`ATDE_VALUELIST` tinyint(1) unsigned
							,`VALI_NAME` varchar(255)
							,`CEhIM_ID` int(10) unsigned
							,`UNIT` varchar(4000)
							,`IMAGE_ID` int(9) unsigned
							);
							
							CREATE TABLE IF NOT EXISTS `v_imageset_info` (
							`IMAT_FROM` varchar(4000)
							,`IMAT_TO` varchar(4000)
							,`VALI_NAME` varchar(255)
							,`VALUE` varchar(255)
							,`ATDE_NAME` varchar(255)
							,`ATDE_UNIT` varchar(255)
							,`ATDE_VALUELIST` tinyint(1) unsigned
							,`CEhIM_ID` int(10) unsigned
							,`CAEX_ID` int(6) unsigned
							,`UNIT` varchar(4000)
							);
							
							CREATE TABLE IF NOT EXISTS `v_image_info` (
							`IMAGE_ORIGINAL_FILENAME` varchar(255)
							,`MEDIM_VALUE` varchar(4000)
							,`ATDE_NAME` varchar(255)
							,`ATDE_UNIT` varchar(255)
							,`ATDE_VALUELIST` tinyint(1) unsigned
							,`VALI_NAME` varchar(255)
							,`CEhIM_ID` int(10) unsigned
							,`UNIT` varchar(4000)
							,`IMAGE_ID` int(9) unsigned
							);
							
							CREATE TABLE IF NOT EXISTS `workshop` (
							  `WORK_ID` int(5) unsigned NOT NULL auto_increment,
							  `USER_ID` int(7) unsigned NOT NULL,
							  `WORK_NAME` varchar(255) NOT NULL,
							  `WORK_STARTDATE` date NOT NULL,
							  `WORK_ENDDATE` date default NULL,
							  `WORK_LOCATION` varchar(255) default NULL,
							  `WORK_HOST_ORGANISATION` varchar(255) default NULL,
							  PRIMARY KEY  (`WORK_ID`),
							  KEY `WORKSHOP_FKIndex1` (`USER_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
							
							CREATE TABLE IF NOT EXISTS `ws_info` (
							  `WSIN_ID` int(7) unsigned NOT NULL auto_increment,
							  `WORK_ID` int(5) unsigned NOT NULL,
							  `WSIN_TEXT` varchar(255) NOT NULL,
							  `WSIN_LINK` varchar(255) default NULL,
							  `WSIN_FILE` varchar(255) default NULL,
							  PRIMARY KEY  (`WSIN_ID`),
							  KEY `WS_INFO_FKIndex1` (`WORK_ID`)
							) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
							
							DROP TABLE IF EXISTS `v_all_annotations`;
							
							CREATE VIEW `" . $params['dbname'] . "`.`v_all_annotations` AS 
							(select `" . $params['dbname'] . "`.`annotations`.`ANNO_ID` AS `ANNO_ID`,
							`" . $params['dbname'] . "`.`annotations`.`CEhIM_ID` AS `CEhIM_ID`,
							`" . $params['dbname'] . "`.`annotations`.`PARENT_ID` AS `PARENT_ID`,
							`" . $params['dbname'] . "`.`annotations`.`PART_ID` AS `PART_ID`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_COMMENT` AS `ANNO_COMMENT`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_DATE` AS `ANNO_DATE`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_GROUP` AS `ANNO_GROUP`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_WS_REF` AS `ANNO_WS_REF`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_WEBGR_REF` AS `ANNO_WEBGR_REF`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_COUNT` AS `ANNO_COUNT`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_DECIMAL` AS `ANNO_DECIMAL`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_SUB` AS `ANNO_SUB`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_BRIGHTNESS` AS `ANNO_BRIGHTNESS`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_CONTRAST` AS `ANNO_CONTRAST`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_COLOR` AS `ANNO_COLOR`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_MAGNIFICATION` AS `ANNO_MAGNIFICATION`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_FINAL` AS `ANNO_FINAL`,
							`" . $params['dbname'] . "`.`annotations`.`ANNO_CREATE_DATE` AS `ANNO_CREATE_DATE`,
							`" . $params['dbname'] . "`.`participant`.`PART_NUMBER` AS `PART_NUMBER`,
							`" . $params['dbname'] . "`.`ce_has_image`.`IMAGE_ID` AS `IMAGE_ID`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID` AS `CAEX_ID`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`KETA_ID` AS `KETA_ID`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`WORK_ID` AS `WORK_ID`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`EXPE_ID` AS `EXPE_ID`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_NAME` AS `CAEX_NAME`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_DESCRIPTION` AS `CAEX_DESCRIPTION`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_COMPAREABLE` AS `CAEX_COMPAREABLE`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_RANDOMIZED` AS `CAEX_RANDOMIZED`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_IS_STOPPED` AS `CAEX_IS_STOPPED`,
							`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_TRAINING` AS `CAEX_TRAINING`,
							`" . $params['dbname'] . "`.`workshop`.`WORK_NAME` AS `WORK_NAME`,`" . $params['dbname'] . "`.
							`image`.`IMAGE_ORIGINAL_FILENAME` AS `IMAGE_ORIGINAL_FILENAME`,
							`" . $params['dbname'] . "`.`key_table`.`KETA_NAME` AS `KETA_NAME` 
							from ((((((`" . $params['dbname'] . "`.`annotations` 
							join `" . $params['dbname'] . "`.`participant` on((`" . $params['dbname'] . "`.`annotations`.`PART_ID` = `" . $params['dbname'] . "`.`participant`.`PART_ID`))) 
							join `" . $params['dbname'] . "`.`ce_has_image` on((`" . $params['dbname'] . "`.`annotations`.`CEhIM_ID` = `" . $params['dbname'] . "`.`ce_has_image`.`CEhIM_ID`))) 
							join `" . $params['dbname'] . "`.`image` on((`" . $params['dbname'] . "`.`ce_has_image`.`IMAGE_ID` = `" . $params['dbname'] . "`.`image`.`IMAGE_ID`))) 
							join `" . $params['dbname'] . "`.`calibration_exercise` on((`" . $params['dbname'] . "`.`ce_has_image`.`CAEX_ID` = `" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID`))) 
							left join `" . $params['dbname'] . "`.`workshop` on((`" . $params['dbname'] . "`.`calibration_exercise`.`WORK_ID` = `" . $params['dbname'] . "`.`workshop`.`WORK_ID`))) 
							join `" . $params['dbname'] . "`.`key_table` on((`" . $params['dbname'] . "`.`calibration_exercise`.`KETA_ID` = `" . $params['dbname'] . "`.`key_table`.`KETA_ID`))));
							
							DROP TABLE IF EXISTS `v_ce_list`;
							
							CREATE VIEW `" . $params['dbname'] . "`.`v_ce_list` AS 
							(select `" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID` AS `CAEX_ID`,`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_DESCRIPTION` AS `CAEX_DESCRIPTION`,`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_NAME` AS `CAEX_NAME`,`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_TRAINING` AS `CAEX_TRAINING`,`" . $params['dbname'] . "`.`calibration_exercise`.`KETA_ID` AS `KETA_ID`,`" . $params['dbname'] . "`.`calibration_exercise`.`EXPE_ID` AS `EXPE_ID`,`" . $params['dbname'] . "`.`workshop`.`WORK_NAME` AS `WORK_NAME`,`" . $params['dbname'] . "`.`workshop`.`WORK_ID` AS `WORK_ID` from ((`" . $params['dbname'] . "`.`calibration_exercise` left join `" . $params['dbname'] . "`.`workshop` on((`" . $params['dbname'] . "`.`calibration_exercise`.`WORK_ID` = `" . $params['dbname'] . "`.`workshop`.`WORK_ID`))) left join `" . $params['dbname'] . "`.`participant` on((`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID` = `" . $params['dbname'] . "`.`participant`.`CAEX_ID`))) group by `" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID`);
							
							DROP TABLE IF EXISTS `v_fish_info`;
							
							create or replace view v_fish_info as (
							SELECT 
							        fish.FISH_SAMPLE_CODE,
							        meta_data_fish.MEDFI_VALUE,
							        attribute_desc.ATDE_NAME,
							        attribute_desc.ATDE_UNIT,
							        attribute_desc.ATDE_VALUELIST,
							        value_list.VALI_NAME,
							        ce_has_image.CEhIM_ID,
							        unitlist.VALI_VALUE as UNIT,
							        image.IMAGE_ID
							from caex_has_atde
							join ce_has_image on caex_has_atde.CAEX_ID = ce_has_image.CAEX_ID
							join image on ce_has_image.IMAGE_ID = image.IMAGE_ID
							join fish on image.FISH_ID = fish.FISH_ID
							join meta_data_fish on fish.FISH_ID = meta_data_fish.FISH_ID AND meta_data_fish.ATDE_ID = caex_has_atde.ATDE_ID
							join attribute_desc on meta_data_fish.ATDE_ID = attribute_desc.ATDE_ID
							left join value_list on meta_data_fish.MEDFI_VALUE = value_list.VALI_ID
							left join value_list as unitlist on attribute_desc.ATDE_UNIT = unitlist.VALI_ID
							);
							
							DROP TABLE IF EXISTS `v_imageset_info`;
							
							CREATE VIEW `" . $params['dbname'] . "`.`v_imageset_info` AS 
							(select max(`" . $params['dbname'] . "`.`imageset_attributes`.`IMAT_FROM`) AS `IMAT_FROM`,max(`" . $params['dbname'] . "`.`imageset_attributes`.`IMAT_TO`) AS `IMAT_TO`,max(`" . $params['dbname'] . "`.`value_list`.`VALI_NAME`) AS `VALI_NAME`,max(`" . $params['dbname'] . "`.`imageset_attributes`.`VALUE`) AS `VALUE`,`" . $params['dbname'] . "`.`attribute_desc`.`ATDE_NAME` AS `ATDE_NAME`,`" . $params['dbname'] . "`.`attribute_desc`.`ATDE_UNIT` AS `ATDE_UNIT`,`" . $params['dbname'] . "`.`attribute_desc`.`ATDE_VALUELIST` AS `ATDE_VALUELIST`,`" . $params['dbname'] . "`.`ce_has_image`.`CEhIM_ID` AS `CEhIM_ID`,`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID` AS `CAEX_ID`,`unitlist`.`VALI_VALUE` AS `UNIT` from (((((`" . $params['dbname'] . "`.`imageset_attributes` join `" . $params['dbname'] . "`.`attribute_desc` on((`" . $params['dbname'] . "`.`imageset_attributes`.`ATDE_ID` = `" . $params['dbname'] . "`.`attribute_desc`.`ATDE_ID`))) left join `" . $params['dbname'] . "`.`value_list` `unitlist` on((`" . $params['dbname'] . "`.`attribute_desc`.`ATDE_UNIT` = `unitlist`.`VALI_ID`))) left join `" . $params['dbname'] . "`.`value_list` on((`" . $params['dbname'] . "`.`imageset_attributes`.`VALUE` = `" . $params['dbname'] . "`.`value_list`.`VALI_ID`))) join `" . $params['dbname'] . "`.`calibration_exercise` on((`" . $params['dbname'] . "`.`imageset_attributes`.`CAEX_ID` = `" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID`))) join `" . $params['dbname'] . "`.`ce_has_image` on((`" . $params['dbname'] . "`.`calibration_exercise`.`CAEX_ID` = `" . $params['dbname'] . "`.`ce_has_image`.`CAEX_ID`))) group by `" . $params['dbname'] . "`.`attribute_desc`.`ATDE_NAME`,`" . $params['dbname'] . "`.`ce_has_image`.`CEhIM_ID`);
							
							DROP TABLE IF EXISTS `v_image_info`;
							
							create or replace view v_image_info as (
							Select  image.IMAGE_ORIGINAL_FILENAME,
							        meta_data_image.MEDIM_VALUE,
							        attribute_desc.ATDE_NAME,
							        attribute_desc.ATDE_UNIT,
							        attribute_desc.ATDE_VALUELIST,
							        value_list.VALI_NAME,
							        ce_has_image.CEhIM_ID,
							        unitlist.VALI_VALUE as UNIT,
							        image.IMAGE_ID
							from caex_has_atde
							join ce_has_image on caex_has_atde.CAEX_ID = ce_has_image.CAEX_ID
							join image on ce_has_image.IMAGE_ID = image.IMAGE_ID
							join meta_data_image on image.IMAGE_ID = meta_data_image.IMAGE_ID AND meta_data_image.ATDE_ID = caex_has_atde.ATDE_ID
							join attribute_desc on meta_data_image.ATDE_ID = attribute_desc.ATDE_ID
							left join value_list as unitlist on attribute_desc.ATDE_UNIT = unitlist.VALI_ID
							left join value_list on meta_data_image.MEDIM_VALUE = value_list.VALI_ID
							);
							
							ALTER TABLE `annotations`
							  ADD CONSTRAINT `annotations_ibfk_1` FOREIGN KEY (`PART_ID`) REFERENCES `participant` (`PART_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `annotations_ibfk_2` FOREIGN KEY (`PARENT_ID`) REFERENCES `annotations` (`ANNO_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `annotations_ibfk_3` FOREIGN KEY (`CEhIM_ID`) REFERENCES `ce_has_image` (`CEhIM_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `attribute_desc`
							  ADD CONSTRAINT `attribute_desc_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
							
							ALTER TABLE `caex_has_atde`
							  ADD CONSTRAINT `caex_has_atde_ibfk_2` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `caex_has_atde_ibfk_3` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `calibration_exercise`
							  ADD CONSTRAINT `calibration_exercise_ibfk_1` FOREIGN KEY (`EXPE_ID`) REFERENCES `expertise` (`EXPE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `calibration_exercise_ibfk_3` FOREIGN KEY (`KETA_ID`) REFERENCES `key_table` (`KETA_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `calibration_exercise_ibfk_4` FOREIGN KEY (`WORK_ID`) REFERENCES `workshop` (`WORK_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `ce_has_image`
							  ADD CONSTRAINT `ce_has_image_ibfk_1` FOREIGN KEY (`IMAGE_ID`) REFERENCES `image` (`IMAGE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `ce_has_image_ibfk_2` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `dots`
							  ADD CONSTRAINT `dots_ibfk_1` FOREIGN KEY (`ANNO_ID`) REFERENCES `annotations` (`ANNO_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `image`
							  ADD CONSTRAINT `image_ibfk_1` FOREIGN KEY (`FISH_ID`) REFERENCES `fish` (`FISH_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
							
							ALTER TABLE `imageset_attributes`
							  ADD CONSTRAINT `imageset_attributes_ibfk_1` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `imageset_attributes_ibfk_2` FOREIGN KEY (`VALI_ID`) REFERENCES `value_list` (`VALI_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `imageset_attributes_ibfk_3` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `meta_data_fish`
							  ADD CONSTRAINT `meta_data_fish_ibfk_4` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `meta_data_fish_ibfk_5` FOREIGN KEY (`FISH_ID`) REFERENCES `fish` (`FISH_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `meta_data_image`
							  ADD CONSTRAINT `meta_data_image_ibfk_2` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `meta_data_image_ibfk_3` FOREIGN KEY (`IMAGE_ID`) REFERENCES `image` (`IMAGE_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `participant`
							  ADD CONSTRAINT `participant_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `participant_ibfk_2` FOREIGN KEY (`CAEX_ID`) REFERENCES `calibration_exercise` (`CAEX_ID`) ON DELETE CASCADE ON UPDATE NO ACTION;
							
							ALTER TABLE `user_has_expertise`
							  ADD CONSTRAINT `user_has_expertise_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
							  ADD CONSTRAINT `user_has_expertise_ibfk_2` FOREIGN KEY (`EXPE_ID`) REFERENCES `expertise` (`EXPE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
							
							ALTER TABLE `value_list`
							  ADD CONSTRAINT `value_list_ibfk_1` FOREIGN KEY (`ATDE_ID`) REFERENCES `attribute_desc` (`ATDE_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
							
							ALTER TABLE `workshop`
							  ADD CONSTRAINT `workshop_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
							
							ALTER TABLE `ws_info`
							  ADD CONSTRAINT `ws_info_ibfk_1` FOREIGN KEY (`WORK_ID`) REFERENCES `workshop` (`WORK_ID`) ON DELETE CASCADE ON UPDATE NO ACTION
		        		    ";

							$attributeScript = "
							
							SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
							
							--
							-- Datenbank: `webgr`
							--
							
							
							--
							-- Daten für Tabelle `user`
							--
							
							INSERT INTO `user` (`USER_ID`, `USER_USERNAME`, `USER_LASTNAME`, `USER_FIRSTNAME`, `USER_PASSWORD`, `USER_E_MAIL`, `USER_INSTITUTION`, `USER_STREET`, `USER_COUNTRY`, `USER_PHONE`, `USER_FAX`, `USER_CITY`, `USER_ACTIVE`, `USER_ROLE`, `USER_GUID`) VALUES
							(1, 'superuser@zadi.de', 'Lastname', 'Firstname', '{SHA}jme7JrNY4u0g/lUu1vuDLzl6UH0=', 'superuser@zadi.de', '255', 'Villichgasse', '234', '0228', '', 'Bonn', 1, 'admin', NULL);
							
							
							SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
							
							--
							-- Datenbank: `webgr`
							--
							
							-- --------------------------------------------------------
							
--
-- Daten für Tabelle `attribute_desc`
--

INSERT INTO `attribute_desc` (`ATDE_ID`, `USER_ID`, `ATDE_NAME`, `ATDE_UNIT`, `ATDE_DESCRIPTION`, `ATDE_DEFAULT`, `ATDE_REQUIRED`, `ATDE_IS_STANDARD`, `ATDE_ACTIVE`, `ATDE_DATATYPE`, `ATDE_FORMTYPE`, `ATDE_VALUELIST`, `ATDE_SEQUENCE`, `ATDE_MULTIPLE`, `ATDE_SHOWINLIST`, `ATDE_GROUP`, `ATDE_FILTERS`, `ATDE_VALIDATORS`) VALUES
(1, 1, 'LENGTH', '218', 'total length of the fish in millimeter', '', 0, 1, 1, 'decimal', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(2, 1, 'WEIGHT', '220', 'weight of the fish sample in gramm', '', 0, 1, 1, 'decimal', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(4, 1, 'RESOLUTION', '418', 'Image scan/print resolution in dots per inch', '', 0, 1, 1, 'decimal', 'text', 0, NULL, 0, 1, 'image', NULL, NULL),
(8, 1, 'STOCK', '', 'Individual information/classification about fish stock, refers to the spatial distribution of a population', '', 0, 1, 1, 'string', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(10, 1, 'ARCHIVING_CODE', '', 'Internal institute code to store the physical structure', '', 0, 1, 1, 'string', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(13, 1, 'SEX', '', 'gender/sex of fish', '', 0, 1, 1, 'integer', 'radio', 1, 2, 0, 1, 'fish', NULL, NULL),
(16, 1, 'AREA', '', 'referes to a geographic region, area code like ICES and NAFO', '', 0, 1, 1, 'string', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(17, 1, 'CAPTURE_DATE', '', 'Date of capture of fish, format YYYY-MM-DD', '', 0, 1, 1, 'date', 'text', 0, NULL, 0, 1, 'fish', NULL, NULL),
(24, 1, 'GEAR', '', '', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'fish', NULL, NULL),
(30, 1, 'FISH_COMMENT', '', 'just additional comment to this dataset', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'fish', NULL, NULL),
(31, 1, 'IMAGE_COMMENT', '', 'just additional comment to this dataset', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'image', NULL, NULL),
(32, 1, 'MAGNIFICATION', '', 'Magnification of subject for image creation', '', 0, 0, 1, 'decimal', 'text', 0, NULL, 0, 1, 'image', NULL, NULL),
(33, 1, 'PREPARATION_METHOD', '', 'Preparation method of subject shown on image', '', 0, 0, 1, 'string', 'text', 0, NULL, 0, 0, 'image', NULL, NULL),
(501, 1, 'SAMPLING_DATE', NULL, 'Date of sampling of fish, format YYYY-MM-DD', NULL, 0, 1, 1, 'date', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(502, 1, 'OBSERVED_MATURITY_STAGE', NULL, 'Maturity stage observed before processing inside WebGR annotation', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(503, 1, 'SAMPLING_INSTITUTE', NULL, 'institute of sampling the physical structure', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(504, 1, 'ARCHIVING_INSTITUTE', NULL, 'institute of archiving the physical structure', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(505, 1, 'RESPONSABLE_SCIENTIST', NULL, 'Responsable scientist to contact', NULL, 0, 0, 1, 'string', 'text', 0, 0, 0, 0, 'fish', NULL, NULL),
(506, 1, 'SAMPLING_SOURCE', NULL, 'Sampling source', NULL, 0, 0, 1, 'integer', 'select', 1, 0, 0, 1, 'fish', NULL, NULL),
(507, 1, 'LONGTITUDE', '1011', 'Longtitude of fish catch, measured in G  (= degree), no degree sign, western hemisphere has negative sign', NULL, 0, 0, 1, 'decimal', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(508, 1, 'LATITUDE', '1011', 'Latitude of fish catch, measured in G  (= degree), no degree sign, southern hemisphere has negative sign', NULL, 0, 0, 1, 'decimal', 'text', 0, 0, 0, 1, 'fish', NULL, NULL),
(601, 1, 'Location', '', '', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 0, 'system', NULL, NULL),
(602, 1, 'Country', '', '', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 0, 'system', NULL, NULL),
(603, 1, 'Institution', '', '', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 0, 'system', NULL, NULL),
(604, 1, 'UNIT', NULL, NULL, NULL, 0, 0, 1, 'integer', 'select', 1, 0, 0, 0, 'system', NULL, NULL),
(605, 1, 'SPECIES', '', 'Fish scientific name (latin)', '', 0, 0, 1, 'integer', 'select', 1, NULL, 0, 1, 'fish', NULL, NULL),
(606, 1, 'TYPE_OF_STRUCTURE', NULL, 'Subject of visual analysis (otolith, gonad etc.)', NULL, 0, 1, 1, 'integer', 'select', 1, 1, 0, 1, 'image', NULL, NULL);
							
							
							
							SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
							
							--
							-- Datenbank: `webgr`
							--
							
							-- --------------------------------------------------------
							
							
--
-- Daten für Tabelle `value_list`
--

INSERT INTO `value_list` (`VALI_ID`, `ATDE_ID`, `VALI_NAME`, `VALI_VALUE`) VALUES
(215, 601, 'Bonn', 'Bonn'),
(218, 604, 'mm', 'mm'),
(219, 604, 'm', 'm'),
(220, 604, 'g', 'g'),
(221, 604, 'kg', 'kg'),
(225, 602, 'Austria', 'Austria'),
(226, 602, 'Belgium', 'Belgium'),
(227, 602, 'Bulgaria', 'Bulgaria'),
(228, 602, 'Cyprus', 'Cyprus'),
(229, 602, 'Czech Republic', 'Czech Republic'),
(230, 602, 'Denmark', 'Denmark'),
(231, 602, 'Estonia', 'Estonia'),
(232, 602, 'Finland', 'Finland'),
(233, 602, 'France', 'France'),
(234, 602, 'Germany', 'Germany'),
(235, 602, 'Greece', 'Greece'),
(236, 602, 'Hungary', 'Hungary'),
(237, 602, 'Ireland', 'Ireland'),
(238, 602, 'Italy', 'Italy'),
(239, 602, 'Latvia', 'Latvia'),
(240, 602, 'Lithuania', 'Lithuania'),
(241, 602, 'Luxembourg', 'Luxembourg'),
(242, 602, 'Malta', 'Malta'),
(243, 602, 'Netherlands', 'Netherlands'),
(244, 602, 'Poland', 'Poland'),
(245, 602, 'Portugal', 'Portugal'),
(246, 602, 'Romania', 'Romania'),
(247, 602, 'Slovakia', 'Slovakia'),
(248, 602, 'Slovenia', 'Slovenia'),
(249, 602, 'Spain', 'Spain'),
(250, 602, 'Sweden', 'Sweden'),
(251, 602, 'United Kingdom', 'United Kingdom'),
(252, 603, 'Laboratório Nacional de Recursos Biológicos – IPIMAR (Portugal) –', 'Laboratório Nacional de Recursos Biológicos – IPIMAR (Portugal)'),
(253, 603, 'The Agri-Food & Biosciences Institute (UK)', 'The Agri-Food & Biosciences Institute (UK)'),
(254, 603, 'AZTI Foundation (Spain)', 'AZTI Foundation (Spain)'),
(255, 603, 'Federal Agency for Agriculture and Food (Germany)', 'Federal Agency for Agriculture and Food (Germany)'),
(256, 603, 'Johann Heinrich von Thünen Institute (Germany)', 'Johann Heinrich von Thünen Institute (Germany)'),
(257, 603, 'Hellenic Centre for Marine Research (Greece)', 'Hellenic Centre for Marine Research (Greece)'),
(258, 603, 'Instituto Español de Oceanografia (Spain)', 'Instituto Español de Oceanografia (Spain)'),
(259, 603, 'Institut français de recherche pour l’exploitation de la mer (France)', 'Institut français de recherche pour l’exploitation de la mer (France)'),
(260, 603, 'Wageningen IMARES (The Netherlands)', 'Wageningen IMARES (The Netherlands)'),
(261, 603, 'Institute of Marine Research (Norway)', 'Institute of Marine Research (Norway)'),
(262, 603, 'Swedish Board of Fisheries (Sweden)', 'Swedish Board of Fisheries (Sweden)'),
(263, 603, 'Italian Society for Marine Biology (Italy)', 'Italian Society for Marine Biology (Italy)'),
(266, 13, 'female', 'female'),
(267, 13, 'male', 'male'),
(268, 13, 'undefined', 'undefined'),
(269, 601, 'Sukarrieta', 'Sukarrieta'),
(401, 605, 'Clupea harengus', 'Clupea harengus'),
(402, 605, 'Engraulis encrasicolus', 'Engraulis encrasicolus'),
(403, 605, 'Gadus morhua', 'Gadus morhua'),
(404, 605, 'Limanda limanda', 'Limanda limanda'),
(405, 605, 'Melanogrammus aeglefiunus', 'Melanogrammus aeglefiunus'),
(406, 605, 'Merlangius merlangus', 'Merlangius merlangus'),
(407, 605, 'Merluccius merluccius', 'Merluccius merluccius'),
(408, 605, 'Micromesistius poutassou', 'Micromesistius poutassou'),
(409, 605, 'Platichthys flesus', 'Platichthys flesus'),
(410, 605, 'Pleuronectes platessa', 'Pleuronectes platessa'),
(411, 605, 'Psetta maxima', 'Psetta maxima'),
(412, 605, 'Sardina pilchardus', 'Sardina pilchardus'),
(413, 605, 'Scomber scombrus', 'Scomber scombrus'),
(414, 605, 'Scophthalmus rhombus', 'Scophthalmus rhombus'),
(415, 605, 'Solea solea', 'Solea solea'),
(416, 605, 'Sprattus sprattus', 'Sprattus sprattus'),
(417, 605, 'Trachurus trachurus', 'Trachurus trachurus'),
(418, 604, 'dpi', 'dpi'),
(419, 603, 'Guest', 'Guest'),
(420, 605, 'Mullus surmuletus', 'Mullus surmuletus'),
(421, 605, 'Pollachius virens', 'Pollachius virens'),
(426, 605, 'Glyptocephalus cynoglossus', 'Glyptocephalus cynoglossus'),
(1001, 606, 'gonad', 'gonad'),
(1002, 606, 'otolith', 'otolith'),
(1003, 606, 'spine', 'spine'),
(1004, 606, 'scale', 'scale'),
(1005, 606, 'operculum', 'operculum'),
(1006, 606, 'illicium', 'illicium'),
(1007, 606, 'egg', 'egg'),
(1008, 606, 'vertebra', 'vertebra'),
(1009, 606, 'fin rays', 'fin rays'),
(1010, 606, 'bone', 'bone'),
(1011, 604, 'degree', 'degree'),
(1012, 506, 'harbour', 'harbour'),
(1013, 506, 'survey', 'survey'),
(1014, 506, 'self sampling', 'self sampling'),
(1015, 506, 'on-board', 'on-board'),
(1016, 602, 'Norway', 'Norway'),
(1017, 603, 'CEFAS', 'CEFAS'),
(1019, 602, 'other', 'other'),
(1020, 605, 'Lepidorhombus whiffiagonis', 'Lepidorhombus whiffiagonis');";

							$installSqriptArray = explode(';',$installScript);
							$attributeScriptArray = explode(';',$attributeScript);

							try {
								$dbAdapter->beginTransaction();
								foreach ($installSqriptArray as $script) {
									$dbAdapter->query($script);
								}
								$dbAdapter->commit();
								try {
									foreach ($attributeScriptArray as $script) {
										if(!empty($script)){
										  $dbAdapter->query($script);
										}
									}
									$dbAdapter->commit();
								} catch (Exception $e) {
									$dbAdapter->rollBack();
									$error = true;
									$errorMassage = "Error while inserting the sample data: ".$e->getMessage();
								}
							} catch (Exception $e) {
								$dbAdapter->rollBack();
								$error = true;
								$errorMassage = "Error while installing the db structure: ".$e->getMessage();
							}
							if($error){
								$this->view->massage = $errorMassage;
							}else{
								$this->view->massage = '<span style="color:green">installed sucessful.</span>';
							}
						} else {
							$this->view->massage = 'Your database is not empty.';
						}
					}else{
						$this->view->massage = $errorMassage;
					}
				} else {
					$this->view->massage = 'The security key is not correct.';
				}
			}
		}
		$this->view->form = $form;
	}
}