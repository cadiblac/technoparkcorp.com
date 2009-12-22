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
 * One simple artifact
 *
 * @package Model
 */
class Model_Artifact extends FaZend_Pos_Abstract implements Model_Artifact_Interface 
{

    /**
     * Attach sub-artifact if it's not here already
     *
     * @param string Name of the property to be accessed later
     * @param Model_Artifact_Interface The artifact to attach
     * @param string Property to set with $this
     * @return $this
     * @param Model_Artifact_PropertyAlreadyExists
     */
    protected function _attach($name, Model_Artifact_Interface $artifact, $property = null) 
    {
        // don't attach again, if it's already here
        if (isset($this->$name))
            return $this;
            
        $this->$name = $artifact;
        
        // initialize the artifact, if necessary
        self::initialize($this, $artifact, $property);
        return $this;
    }
    
    /**
     * Attach sub-artifact if it's not here already, as an item in array
     *
     * @param string|false Key of the array
     * @param Model_Artifact_Interface The artifact to attach
     * @param string Property to set with $this
     * @return $this
     * @throws Model_Artifact_KeyAlreadyExists
     */
    protected function _attachItem($key, Model_Artifact_Interface $artifact, $property = null) 
    {
        if (isset($this[$key]))
            return $this;
            
        // attach as "new" element to the array or as associative value
        if ($key === false)
            $this[] = $artifact;
        else
            $this[$key] = $artifact;
            
        // initialize the artifact, if necessary
        self::initialize($this, $artifact, $property);
        return $this;
    }
    
    /**
     * Initialize child artifact
     *
     * @param mixed Root object
     * @param Model_Artifact_Interface The artifact to attach
     * @param string Property to set with $this
     * @return void
     * @throws Model_Artifact_InvalidChildArtifact
     */
    public static function initialize($root, Model_Artifact_Interface $artifact, $property) 
    {
        if (is_null($property) && !($artifact instanceof Model_Artifact_Stateless)) {
            // do nothing
        } elseif (!is_null($property) && ($artifact instanceof Model_Artifact_Stateless)) {
            if (method_exists($artifact, $property))
                $artifact->$property($root);
            else
                $artifact->$property = $root;
        } elseif (!is_null($property)) {
            FaZend_Exception::raise('Model_Artifact_InvalidChildArtifact', 
                'Artifact ' . get_class($artifact) . ' is not stateless');
        }
            
        // reload it if it's empty now and requires loading
        if (($artifact instanceof Model_Artifact_Passive) && !$artifact->isLoaded())
            $artifact->reload();
    }
    
}
