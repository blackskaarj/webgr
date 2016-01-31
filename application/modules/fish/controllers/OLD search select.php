<?php 
//aliasArray is available after getSelectForGroups() call
        $aliasArray = $metaData->aliasArray;

        $tableAdapter = Zend_Registry::get('DB_CONNECTION1');

        //handle AND/OR search
        if ($formValues['kind'] == 'and') {
            foreach ($formValues as $key => $value) {
                if ($this->formKeyHasValue($key, $value)) {
                    //search for data sets with NULL values - e.g. old data sets before introduction of new attributes - isn't possible at the moment
                    //process possible meta data attributes
                    if (substr_compare($key, 'ATDE_', 0, 4, TRUE) == 0) {
                        //cut off ATDE_ to get only ID for querying in table
                        $keyAtDeId = substr($key, 5);
                        foreach ($aliasArray as $atDeId => $aliasTableAndColumn) {
                            if ($keyAtDeId == $atDeId) {

                                //Boolean Expressions: Int=0=>FALSE , Int=1=>TRUE
                                $atDeTable = new AttributeDescriptor();
                                $rowset = $atDeTable->find($atDeId);
                                if (count($rowset)==1) {
                                    $rowsetArray = $rowset->toArray();
                                    $atDe = $rowsetArray[0];
                                    if ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'select') {
                                        //OLD
                                        //$partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);
                                        //$partStatement = $aliasTableAndColumn.' = '.$value;

                                        //NEW
                                        //-------------------------------------------------------------
                                        //NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
                                        //      checkbox is always submitted
                                        //      standard zend element multicheckbox sets no value for unchecked
                                        //      multicheckbox without checked boxes is not submitted

                                        $partStatement = '';
                                        //handle last item differently
                                        //credit:grobemo
                                        //24-Apr-2009 08:13
                                        //http://de3.php.net/manual/en/control-structures.foreach.php
                                        $last_item = end($value);
                                        foreach ($value as $val) {
                                            if ($val == $last_item) {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
                                            }
                                            else {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
                                            }
                                        }
                                        //-------------------------------------------------------------
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'text') {
                                        if ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {

                                            switch ($atDe[AttributeDescriptor::COL_DATA_TYPE]) {
                                                case 'integer':
                                                    $sqlDatatype = 'int';
                                                    break;
                                                case 'decimal':
                                                    $sqlDatatype = 'dec';
                                                    break;
                                                    //TODO handle other datatypes
                                            }

                                            if ($value['fromValue'] == NULL || $value['toValue'] == NULL ) {
                                                //FROM or TO value empty
                                                $atDeName = $atDe[AttributeDescriptor::COL_NAME];
                                                echo "Info: FROM or TO value empty, $atDeName not used<br>";
                                                $keyProcessed = TRUE;
                                                break;
                                            } else {
                                                //$partStatement = $aliasTableAndColumn.' >= '.$value['fromValue'].' AND ';
                                                //$partStatement = $partStatement.$aliasTableAndColumn.' <= '.$value['toValue'];
                                                $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' >= ? AND ', $value['fromValue'], $sqlDatatype);
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' <= ?', $value['toValue'], $sqlDatatype);
                                                unset($sqlDatatype);
                                            }
                                        }
                                        elseif ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'string') {
                                            $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
                                        }
                                        else {
                                            throw new Zend_Exception("Error: processing search parameters");
                                        }
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox') {
                                        //NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
                                        //      checkbox is always submitted
                                        //      standard zend element multicheckbox sets no value for unchecked
                                        //      multicheckbox without checked boxes is not submitted
                                        //schema-definition specific
                                        if ($value == '1') {
                                            //checkbox is on
                                            $partStatement = $aliasTableAndColumn.' = 1';
                                        }
                                        elseif ($value == '0' || $value == NULL) {
                                            //checkbox is off
                                            //do nothing to handle 0 and NULL (off and not defined yet)
                                            //$partStatement = $aliasTableAndColumn.' = 0';
                                        }
                                        else {
                                            throw new Zend_Exception("Error: processing search parameters");
                                        }
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'textarea') {
                                        $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'radio') {
                                        $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multiselect' ||
                                    $atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multicheckbox') {
                                        //NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
                                        //      checkbox is always submitted
                                        //      standard zend element multicheckbox sets no value for unchecked
                                        //      multicheckbox without checked boxes is not submitted
                                            
                                        $partStatement = '';
                                        //handle last item differently
                                        //credit:grobemo
                                        //24-Apr-2009 08:13
                                        //http://de3.php.net/manual/en/control-structures.foreach.php
                                        $last_item = end($value);
                                        foreach ($value as $val) {
                                            if ($val == $last_item) {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
                                            }
                                            else {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
                                            }
                                        }
                                    }

                                    else {
                                        throw new Zend_Exception("Error: processing search parameters");
                                    }
                                    //finally append the where to the select(whole metadata)
                                    if (isset($partStatement)) {
                                        $select->where($partStatement);
                                    }
                                    unset($partStatement);

                                } else {
                                    throw new Zend_Exception("Error: count(rowset) from attribute_desc where ATDE_ID = $atdeId is not 1");
                                }



                                //$partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' like ?', '%'.$value.'%');
                                //$select->where($partStatement);

                                //set to TRUE jumps to next key
                                $keyProcessed = TRUE;
                                break;
                            } else {
                                $keyProcessed = FALSE;
                            }
                        }
                    } else {
                        //no ATDE_ID attribute key
                        $keyProcessed = FALSE;
                    }

                    //process direct attributes
                    //only if key was not processed already
                    if (!$keyProcessed) {
                        //$tableRow = $tableAdapter->quoteIdentifier($key);
                        $partStatement = $tableAdapter->quoteInto($key.' like ?', '%'.$value.'%');
                        $select->where($partStatement);
                    }
                }
                //end, process next key
            }
        }

        if ($formValues['kind'] == 'or') {
            //due to whole sql-statement
            //don't use select->orWhere() method, instead add strings with OR
            //to reduce usage of brackets
            //and mixed usage of where / orWhere (first condition where, second and more conditions orWhere)
            $orWhere = '';
            foreach ($formValues as $key => $value) {
                if ($this->formKeyHasValue($key, $value)) {
                    //search for data sets with NULL values - e.g. old data sets before introduction of new attributes - isn't possible at the moment
                    //process possible meta data attributes
                    if (substr_compare($key, 'ATDE_', 0, 4, TRUE) == 0) {
                        $keyAtDeId = substr($key, 5);
                        foreach ($aliasArray as $atDeId => $aliasTableAndColumn) {
                            //cut off ATDE_ to get only ID for querying in table
                            if ($keyAtDeId == $atDeId) {

                                //Boolean Expressions: Int=0=>FALSE , Int=1=>TRUE
                                $atDeTable = new AttributeDescriptor();
                                $rowset = $atDeTable->find($atDeId);
                                if (count($rowset)==1) {
                                    $rowsetArray = $rowset->toArray();
                                    $atDe = $rowsetArray[0];
                                    if ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'select') {
                                        //OLD
                                        //$partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);
                                        //$partStatement = $aliasTableAndColumn.' = '.$value;

                                        //NEW
                                        //-------------------------------------------------------------
                                        //NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
                                        //      checkbox is always submitted
                                        //      standard zend element multicheckbox sets no value for unchecked
                                        //      multicheckbox without checked boxes is not submitted

                                        $partStatement = '';
                                        //handle last item differently
                                        //credit:grobemo
                                        //24-Apr-2009 08:13
                                        //http://de3.php.net/manual/en/control-structures.foreach.php
                                        $last_item = end($value);
                                        foreach ($value as $val) {
                                            if ($val == $last_item) {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
                                            }
                                            else {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
                                            }
                                        }
                                        //-------------------------------------------------------------
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'text') {
                                        if ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
                                        $atDe[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {

                                            switch ($atDe[AttributeDescriptor::COL_DATA_TYPE]) {
                                                case 'integer':
                                                    $sqlDatatype = 'int';
                                                    break;
                                                case 'decimal':
                                                    $sqlDatatype = 'dec';
                                                    break;
                                                    //TODO handle other datatypes
                                            }

                                            if ($value['fromValue'] == NULL || $value['toValue'] == NULL ) {
                                                //FROM or TO value empty
                                                $atDeName = $atDe[AttributeDescriptor::COL_NAME];
                                                echo "Info: FROM or TO value empty, $atDeName not used<br>";
                                                $keyProcessed = TRUE;
                                                break;
                                            } else {
                                                //$partStatement = $aliasTableAndColumn.' >= '.$value['fromValue'].' AND ';
                                                //$partStatement = $partStatement.$aliasTableAndColumn.' <= '.$value['toValue'];
                                                $partStatement = '(';
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' >= ? AND ', $value['fromValue'], $sqlDatatype);
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' <= ?)', $value['toValue'], $sqlDatatype);
                                            }
                                        }
                                        elseif ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'string') {
                                            $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
                                        }
                                        else {
                                            throw new Zend_Exception("Error: processing search parameters");
                                        }
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox') {
                                        //NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
                                        //      checkbox is always submitted
                                        //      standard zend element multicheckbox sets no value for unchecked
                                        //      multicheckbox without checked boxes is not submitted
                                        //schema-definition specific
                                        if ($value == '1') {
                                            //checkbox is on
                                            $partStatement = $aliasTableAndColumn.' = 1';
                                        }
                                        elseif ($value == '0' || $value == NULL) {
                                            //checkbox is off
                                            //do nothing to handle 0 and NULL (off and not defined yet)
                                            //$partStatement = $aliasTableAndColumn.' = 0';
                                        }
                                        else {
                                            throw new Zend_Exception("Error: processing search parameters");
                                        }
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'textarea') {
                                        $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'radio') {
                                        $partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);
                                    }

                                    elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multiselect' ||
                                    $atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multicheckbox') {
                                        //NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
                                        //      checkbox is always submitted
                                        //      standard zend element multicheckbox sets no value for unchecked
                                        //      multicheckbox without checked boxes is not submitted
                                            
                                        $partStatement = '(';
                                        //handle last item differently
                                        //credit:grobemo
                                        //24-Apr-2009 08:13
                                        //http://de3.php.net/manual/en/control-structures.foreach.php
                                        $last_item = end($value);
                                        foreach ($value as $val) {
                                            if ($val == $last_item) {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ?)', $val);
                                            }
                                            else {
                                                $partStatement = $partStatement.$tableAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
                                            }
                                        }
                                    }

                                    else {
                                        throw new Zend_Exception("Error: processing search parameters");
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

                                } else {
                                    throw new Zend_Exception("Error: count(rowset) from attribute_desc where ATDE_ID = $atdeId is not 1");
                                }



                                //$partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' like ?', '%'.$value.'%');
                                //$select->where($partStatement);

                                //set to TRUE jumps to next key
                                $keyProcessed = TRUE;
                                break;
                            } else {
                                $keyProcessed = FALSE;
                            }
                        }
                    } else {
                        //no ATDE_ID attribute key
                        $keyProcessed = FALSE;
                    }

                    //process direct attributes
                    //only if key was not processed already
                    if (!$keyProcessed) {
                        //$tableRow = $tableAdapter->quoteIdentifier($key);
                        $partStatement = $tableAdapter->quoteInto($key.' like ?', '%'.$value.'%');
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
                //end, process next key
            }
            //finally append the where to the select(whole metadata)
            if ($orWhere != '') {
                $select->where($orWhere);
            }
        }
        ?>