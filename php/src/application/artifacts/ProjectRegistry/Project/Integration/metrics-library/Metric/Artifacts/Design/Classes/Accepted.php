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
 * Total number of classes accepted by the architect
 * 
 * @package Artifacts
 */
class Metric_Artifacts_Design_Classes_Accepted extends Metric_Abstract
{

    /**
     * Load this metric
     *
     * @return void
     */
    public function reload()
    {
        // we can't calculate metrics here if deliverables are not loaded
        if (!$this->_project->deliverables->isLoaded()) {
            $this->_project->deliverables->reload();
        }
        
        // total amount of classes in the project
        $this->value = 0;
        foreach ($this->_project->deliverables->classes as $class) {
            if ($class->isAccepted()) {
                $this->value++;
            }
        }
        $this->default = round(
            $this->_project->metrics['artifacts/requirements/functional/accepted']->objective *
            $this->_project->metrics['history/ratios/classes/per/functional']->value
        );
    }
    
    /**
     * Get work package
     *
     * @param string[] Names of metrics, to consider after this one
     * @return theWorkPackage
     */
    protected function _derive(array &$metrics = array())
    {
        // to avoid division by zero
        if (!$this->objective) {
            return null;
        }
        
        // how full our requirements are covered by design? [0..1]
        $coverage = $this->_project->metrics['aspects/coverage/requirements/design']->value;
        
        // we have more classes accepted than needed
        if ($this->value >= $this->objective) {
            return null;
        }

        // price of entire design
        $price = FaZend_Bo_Money::factory(
            $this->_project->metrics['history/cost/design/class']->value
        )
        ->mul($this->objective);

        return $this->_makeWp(
            $price->mul(1 - $coverage * $this->value/$this->objective), 
            sprintf(
                'to design and accept +%d classes, add %d%% of coverage',
                $this->objective - $this->value,
                100 * (1 - $coverage)
            )
        );
    }
        
}
