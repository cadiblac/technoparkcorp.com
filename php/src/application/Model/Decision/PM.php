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
 * Decision made by PM wobot
 *
 * @package Model
 */
abstract class Model_Decision_PM extends Model_Decision
{

    /**
     * Project, the owner of the decision
     *
     * @var theProject
     */
    protected $_project;

    /**
     * Set project, the initiator of this decision
     *
     * @param theProject Project, the holder of the decision
     * @return void
     */
    public function setProject(theProject $project)
    {
        $this->_project = $project;
    }

}
