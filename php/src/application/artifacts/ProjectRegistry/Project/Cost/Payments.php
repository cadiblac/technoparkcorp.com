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
 * Project payments
 *
 * @package Artifacts
 */
class thePayments implements Model_Artifact_Stateless {
       
    /**
     * Holder of the class
     *
     * @var theProject
     */
    public $project;
    
    /**
     * Create new payment specific for some user
     *
     * @param string Email of the user
     * @param string Original amount of payment, like '125 EUR'
     * @param string Context, for example name of project
     * @param string Details of the payment
     * @return FaZend_Db_Table_ActiveRow_payment
     **/
    public function createSpecific($user, $original, $context, $details) {
        $payment = $this->createGeneric($original, $context, $details);
        $payment->user = $user;
        $payment->save();
        return $payment;
    }
        
    /**
     * Create new payment WITHOUT info about user, just context
     *
     * @param string Original amount of payment, like '125 EUR'
     * @param string Context, for example name of project
     * @param string Details of the payment
     * @return FaZend_Db_Table_ActiveRow_payment
     **/
    public function createGeneric($original, $context, $details) {
        $payment = new thePayment();
        $payment->user = null;
        $payment->context = $context;
        $payment->details = $details;
        $payment->rate = null;
        $payment->original = $original;
        
        $payment->amount = (integer)Model_Cost::factory($original)->cents;
        
        $payment->save();
        return $payment;
    }
        
    /**
     * Get statement
     *
     * @return array
     **/
    public function retrieveStatement() {
        return thePayment::retrieve()
            ->where('context = ?', $this->project->name)
            ->order('created')
            ->setRowClass('thePayment')
            ->fetchAll();
    }
        
}
