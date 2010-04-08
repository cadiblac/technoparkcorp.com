<?php
/**
 * thePanel v2.0, Project Management Software Toolkit
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are PROHIBITED without prior written permission from 
 * the author. This product may NOT be used anywhere and on any computer 
 * except the server platform of TechnoPark Corp. located at 
 * www.technoparkcorp.com. If you received this code occasionally and 
 * without intent to use it, please report this incident to the author 
 * by email: privacy@technoparkcorp.com or by mail: 
 * 568 Ninth Street South 202, Naples, Florida 34102, USA
 * tel. +1 (239) 935 5429
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id$
 * @category FaZend
 */

/**
 * Table
 *
 * @package helpers
 */
class Helper_Table extends FaZend_View_Helper
{

    /**
     * Columns to show
     *
     * @var array
     */
    protected $_columns;

    /**
     * Links with names
     *
     * Associative array, where key = name of link, value = mnemo of link
     *
     * @var array
     */
    protected $_links;

    /**
     * Name of column that was added lately
     *
     * @var string
     */
    protected $_predecessor;

    /**
     * Html table helper instance
     *
     * @var FaZend_View_Helper_HtmlTable
     */
    protected $_table;
    
    /**
     * Id of the next table to show
     *
     * @var integer
     */
    protected static $_tableId = 0;

    /**
     * Builds the html table
     *
     * @return Helper_Table
     */
    public function table()
    {
        // just get next table
        $this->_table = $this->getView()->htmlTable(self::$_tableId++);
        $this->_links = array();
        $this->_columns = array();
        $this->_predecessor = false;
        return $this;
    }

    /**
     * Converts it to HTML
     *
     * @return string HTML
     */
    public function __toString()
    {
        $this->_table->setNoDataMessage('<p class="empty">The table is empty, no data.</p>');
        
        // configure CSS for this gallery
        $this->getView()->includeCSS('helper/table.css');

        $this->_table->showColumns($this->_columns);

        return $this->_table->__toString() .
        $this->getView()->paginator;
    }

    /**
     * Set data source
     *
     * @param Iterator
     * @return Helper_Table
     */
    public function setSource(Iterator $iterator)
    {
        validate()
            ->instanceOf($iterator, 'Iterator', "Source should be an instance of Iterator");
            
        FaZend_Paginator::addPaginator(
            $iterator, 
            $this->getView(), 
            Zend_Controller_Front::getInstance()->getRequest()->get('pg'), 
            'paginator'
        );

        $this->getView()->paginator->setItemCountPerPage(25);
        $this->_table->setPaginator($this->getView()->paginator);
        
        return $this;
    }

    /**
     * Inject variable
     *
     * @return $this
     */
    public function setInjection($name, $var) 
    {
        $this->_table->setInjection($name, $var);
        return $this;
    }

    /**
     * Add new column
     *
     * @param string|false Name of the object property to get, false = KEY
     * @param string Header to show
     * @return Helper_Table
     */
    public function addColumn($name, $header = null)
    {
        // remember the name of this column added
        $this->_columns[] = $name;

        // add column to the htmlTable()
        $this->_table->addColumn($name, $this->_predecessor);

        // reconfigure header, if the name is given
        if (!is_null($header)) {
            $this->_table->setColumnTitle($name, $header);
        }

        // set predecessor to make sure we allocate them consequently
        $this->_predecessor = $name;

        // return itself, to allow fluent interface
        return $this;
    }

    /**
     * Add new option
     *
     * @param string Name of the option
     * @param string Link to the operation
     * @param mixed Callback to skip option
     * @return Helper_Table
     */
    public function addOption($name, $link, $skip = null)
    {
        // this params will be sent to the htmlTable() helper
        $urlParams = array(
            'doc' => FaZend_Callback::factory(array($this, 'resolveDocumentName'))
        );

        // add the link to $this
        $this->_links[$name] = $link;

        // user func call params
        $params = array($name, null, null, $urlParams, 'panel', true, false);

        // it will automatically understand whether the option should
        // stay in 'OPTIONS' column, or should be attached to the data column
        if (in_array($name, $this->_columns)) {
            $func = 'addColumnLink';
        } else {
            $func = 'addOption';
        }

        // attach option to the htmlTable helper
        call_user_func_array(array($this->_table, $func), $params);
        
        if (!is_null($skip)) {
            $this->_table->skipOption($name, $skip);
        }

        // return itself, to allow fluent interface
        return $this;
    }

    /**
     * Add converter to the column
     *
     * @param mixed Callback
     * @return Helper_Table
     */
    public function addConverter($callback)
    {
        // add column to the htmlTable()
        $this->_table->addConverter($this->_predecessor, $callback);
        return $this;
    }
    
    /**
     * Allow raw html
     *
     * @return Helper_Table
     */
    public function allowRawHtml()
    {
        // add column to the htmlTable()
        $this->_table->allowRawHtml($this->_predecessor);
        return $this;
    }
    
    /**
     * Add formatter to the column
     *
     * @param mixed Callback
     * @param string|null Style
     * @return Helper_Table
     */
    public function addFormatter($callback, $style = null)
    {
        // add formatter to the htmlTable()
        $this->_table->addFormatter($this->_predecessor, $callback, $style);
        return $this;
    }
    
    /**
     * Resolve document name by data
     *
     * This method is called by FaZend_View_Helper_HtmlTable when configured
     * by addOption() method in this class.
     *
     * @param string Name of the link/column
     * @param array Row from the table
     * @param mixed Key of the row
     * @return string
     */
    public function resolveDocumentName($name, $row, $key)
    {
        return Model_Pages::resolveLink($this->_links[$name], $row, $key);
    }

}