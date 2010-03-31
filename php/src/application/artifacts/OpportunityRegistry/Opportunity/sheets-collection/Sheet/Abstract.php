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
 * @author Yegor Bugayenko <egor@tpc2.com>
 * @copyright Copyright (c) TechnoPark Corp., 2001-2009
 * @version $Id$
 *
 */

/**
 * One abstract sheet
 *
 * @package Artifacts
 */
abstract class Sheet_Abstract
{
    
    /**
     * Defaults
     *
     * @var array
     * @see __get()
     */
    protected $_defaults = array();
    
    /**
     * List of valued calculated by _get*() methods
     *
     * @var array
     * @see __get()
     */
    protected $_cached;

    /**
     * Configuration
     *
     * @var SimpleXMLElement
     */
    protected $_config;
    
    /**
     * Collection of sheets
     *
     * @var theSheetsCollection
     */
    protected $_sheets;

    /**
     * Construct the class
     *
     * @param SimpleXMLElement Configuration
     * @return void
     */
    private function __construct(SimpleXMLElement $config) 
    {
        validate($config instanceof SimpleXMLElement);
        $this->_config = $config;
        $this->_cached = array();
    }

    /**
     * Create new sheet and config it
     *
     * @param string Name of the sheet class
     * @param SimpleXMLElement Configuration
     * @return void
     * @throws Sheet_Abstract_InvalidNameException
     */
    public static function factory($name, SimpleXMLElement $config) 
    {
        if (!self::isValidName($name)) {
            FaZend_Exception::raise(
                'Sheet_Abstract_InvalidNameException', 
                "Invalid sheet name: '$name'"
            );
        } 
        
        $className = 'Sheet_' . ucfirst($name);
        return new $className($config);
    }
    
    /**
     * The name provided is valid?
     *
     * @param string Name of the sheet class
     * @return boolean
     */
    public static function isValidName($name) 
    {
        return file_exists(dirname(__FILE__) . '/' . ucfirst($name) . '.php');
    }
    
    /**
     * Inject dependency
     *
     * @param theSheetsCollection
     * @param boolean Initialize object?
     * @return $this
     * @see theSheetsCollection::offsetSet()
     * @see _init()
     */
    public function setSheetsCollection(theSheetsCollection $sheets, $init = true) 
    {
        $this->_sheets = $sheets;
        if ($init) {
            $this->_init();
        }
        return $this;
    }
    
    /**
     * Return list of properties to save
     *
     * @return array
     */
    public function __sleep() 
    {
        $this->_xml = $this->_config->asXml();

        $rc = new ReflectionClass($this);
        $toSerialize = array();
        foreach ($rc->getProperties() as $property) {
            if (!in_array($property->getName(), array('_config', '_cached', '_sheets', '_defaults'))) {
                $toSerialize[] = $property->getName();
            }
        }
        $toSerialize[] = '_xml';
        return $toSerialize;
    }
    
    /**
     * Restore object
     *
     * @return void
     */
    public function __wakeup() 
    {
        $this->_config = simplexml_load_string($this->_xml);
        $this->_cached = array();
    }
    
    /**
     * Getter dispatcher
     *
     * @param string Name of property to get
     * @return mixed
     * @throws Opportunity_PropertyOrMethodNotFound
     */
    public final function __get($name) 
    {
        $method = '_get' . ucfirst($name);
        if (method_exists($this, $method)) {
            if (array_key_exists($method, $this->_cached)) {
                return $this->_cached[$method];
            } else {
                // logg('cached %s to %s', $method, $this->$method());
                return $this->_cached[$method] = $this->$method();
            }
        }
            
        $var = '_' . $name;
        if (property_exists($this, $var)) {
            return $this->$var;
        }
        
        $items = $this->_config->xpath("//item[@name='{$name}']");
        if (count($items) > 0) {
            if (isset($items[0]['value'])) {
                return strval($items[0]['value']);
            } else {
                return $items[0];
            }
        }
        
        if (array_key_exists($name, $this->_defaults)) {
            return $this->_defaults[$name];
        }
        
        FaZend_Exception::raise(
            'Sheet_Abstract_PropertyOrMethodNotFound', 
            "Can't find what is '$name' in " . get_class($this)
        );
    }
    
    /**
     * This variable exists in config?
     *
     * @param string Name
     * @return boolean
     */
    public function exists($name) 
    {
        if (!array_key_exists($name, $this->_defaults)) {
            return false;
        }
        return (bool)count($this->_config->xpath("//item[@name='{$name}']"));
    }
    
    /**
     * Get sheet in latex
     *
     * @return string LaTeX source
     * @throws Sheet_Abstract_RenderingProhibited
     * @throws Sheet_Abstract_TemplateMissedException
     */
    public final function getLatex($template = null) 
    {
        if (is_null($template)) {
            $template = $this->getTemplateFile();
        }
        if (is_null($this->sheets)) {
            FaZend_Exception::raise(
                'Sheet_Abstract_RenderingProhibited', 
                "You can't render '{$this->name}' outside of collection"
            );
        }
        if (!self::isTemplateExists($template)) {
            FaZend_Exception::raise(
                'Sheet_Abstract_TemplateMissedException', 
                "Template '{$template}' not found"
            );
        }
        return $this->sheets->getView()
            ->assign('sheet', $this)
            ->render($template);
    }
    
    /**
     * Get short proposal paragraph
     *
     * @return string LaTeX source
     */
    public final function getProposal() 
    {
        return $this->getLatex($this->getProposalFile());
    }
    
    /**
     * Given template exists?
     *
     * @param string Name of template, like "Vision.tex" or "promo/FinanceInfo.tex"
     * @return void
     */
    public static function isTemplateExists($name) 
    {
        return file_exists(dirname(__FILE__) . '/../templates/' . $name);
    }
    
    /**
     * Get name of sheet, like "Vision", "ROM", etc.
     *
     * @return string
     */
    public function getSheetName() 
    {
        return preg_replace('/Sheet_/', '', get_class($this));
    }
    
    /**
     * Get name of the template file, like "Vision.tex", "ROM.tex", etc.
     *
     * @return string
     */
    public function getTemplateFile() 
    {
        return $this->getSheetName() . '.tex';
    }
    
    /**
     * Get name of the template file, like "Vision.tex", "ROM.tex", etc.
     *
     * @return string
     */
    public function getProposalFile() 
    {
        return 'proposals/' . $this->getSheetName() . '.tex';
    }
    
    /**
     * Initialize the clas
     *
     * @return void
     */
    protected function _init() 
    {
        // to override it
    }
    
    /**
     * Get name of the sheet
     *
     * @return string
     */
    protected function _getName() 
    {
        return substr(get_class($this), strlen('Sheet_'));
    }

}
