<?php
/**
 *
 * Copyright (c) 2008, TechnoPark Corp., Florida, USA
 * All rights reserved. THIS IS PRIVATE SOFTWARE.
 *
 * Redistribution and use in source and binary forms, with or without modification, are PROHIBITED
 * without prior written permission from the author. This product may NOT be used anywhere
 * and on any computer except the server platform of TechnoPark Corp. located at
 * www.technoparkcorp.com. If you received this code occacionally and without intent to use
 * it, please report this incident to the author by email: privacy@technoparkcorp.com or
 * by mail: 568 Ninth Street South 202 Naples, Florida 34102, the United States of America,
 * tel. +1 (239) 243 0206, fax +1 (239) 236-0738.
 *
 * @author Yegor Bugaenko <egor@technoparkcorp.com>
 * @copyright Copyright (c) TechnoPark Corp., 2001-2009
 * @version $Id$
 *
 */

/**
 * Project objective
 *
 * @package Artifacts
 */
class theObjective
{
        
    /**
     * Value of the objective
     *
     * @var string
     **/
    protected $_value;
    
    /**
     * Construct the class
     *
     * @param integer Value of the objective
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
        
    /**
     * Getter dispatcher
     *
     * @param string Name of property to get
     * @return mixed
     **/
    public function __get($name) 
    {
        $method = '_get' . ucfirst($name);
        if (method_exists($this, $method))
            return $this->$method();
            
        $var = '_' . $name;
        if (property_exists($this, $var))
            return $this->$var;
            
        FaZend_Exception::raise(
            'InvalidPropertyOfMethod',
            "Can't find what is $name in " . get_class($this)
            );
    }

    /**
     * Get value of the objectiv
     *
     * @return integer
     **/
    protected function _getValue() 
    {
        return $this->_value;
    }

}