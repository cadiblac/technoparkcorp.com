<?php
/**
 *
 * Copyright (c) FaZend.com
 * All rights reserved.
 *
 * You can use this product "as is" without any warranties from authors.
 * You can change the product only through Google Code repository
 * at http://code.google.com/p/fazend
 * If you have any questions about privacy, please email privacy@fazend.com
 *
 * @copyright Copyright (c) FaZend.com
 * @version $Id$
 * @category FaZend
 */

/**
 * Publish document on a page
 *
 * @package helpers
 */
class Helper_Publish extends FaZend_View_Helper {

    /**
     * Document to be published
     *
     * @var Model_Artifact
     */
    protected $_doc = null;
    
    /**
     * Local ACL for publishing pages
     *
     * @var Zend_Acl
     */
    protected $_acl = null;
    
    /**
     * List of publishing pages
     *
     * @var FaZend_StdObject[]
     */
    protected $_pages = array();

    /**
     * Publishes an artifact
     *
     * @param Model_Artifact The artifact to publish
     * @return Helper_Publish
     */
    public function publish(Model_Artifact $doc) {
        $this->_doc = $doc;
        $this->_loadAcl();
        return $this;
    }

    /**
     * Show in string
     *
     * @return string HTML
     */
    public function __toString() {
        try {
            return $this->_render();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
        
    /**
     * Show in string
     *
     * @return string HTML
     */
    protected function _render() {
        
        // include CSS specific for this helper
        $this->getView()->includeCSS('helper/publish.css');
        
        // current document
        $current = $this->getView()->doc;
        
        // define privileges of current user on current page
        $privileges = null;
        if (Model_Pages::getInstance()->isAllowed($current, null, 'w'))
            $privileges = 'rw';

        // build menu
        $links = array();        
        foreach ($this->_pages as $page) {            
            if ($this->_acl->isAllowed(Model_User::me()->email, $page->tag, $privileges))
                $links[$page->tag] = '<a href="' . $this->getView()->panelUrl() . '?' . $page->tag .  '">' . $page->tag . '</a>';
        }
        
        $request = Zend_Controller_Front::getInstance()->getRequest();
        foreach ($this->_pages as $page) {
            if (isset($request->{$page->tag})) { 
                if (isset($links[$page->tag])) {
                    $pageHtml = $this->_executePage($page);
                    $links[$page->tag] = '<b>' . $links[$page->tag] . '</b>';
                } else {
                    $pageHtml = '<p class="error">You don\' have enough access permissions to access this page (' . $page->tag . ')</p>';
                }
            }
        }

        return '<div class="publish">' .
            '<tt>' . get_class($this->_doc) . '</tt>: ' . 
            implode('&#32;&middot;&#32;', $links) . '</div>' . 
            (isset($pageHtml) ? "<div class='publisher'>" . $pageHtml . '</div>' : false);
        
    }
    
    /**
     * Execute one page and return HTML result
     *
     * @param FaZend_StdObject Page resource
     * @return string HTML
     **/
    protected function _executePage(FaZend_StdObject $page) {
        return $this->getView()->render($page->path);
    }
    
    /**
     * Loads local ACL for all possible pages for this document and user
     *
     * @return void
     **/
    protected function _loadAcl() {
        $this->_acl = new Zend_Acl();
        $this->_acl->deny();

        // add current user by default
        $this->_acl->addRole(Model_User::me()->email);
        
        $prefix = 'panel/publish';
        $dir = APPLICATION_PATH . '/views/scripts/' . $prefix;
        
        foreach (glob($dir . '/*.phtml') as $file) {
            $script = pathinfo($file, PATHINFO_FILENAME);
            
            // skip system files
            if ($script[0] == '_')
                continue;
                
            $this->_pages[$script] = FaZend_StdObject::create()
                ->set('path', $prefix . '/' . $script . '.phtml')
                ->set('tag', $script);
                
            $this->_acl->addResource($script);
        }
        
        foreach (explode("\n", ($this->getView()->render($prefix . '/_access.phtml'))) as $id=>$line) {
            // ignore comments and empty lines
            if (preg_match('/^(?:\s?#.*|\s?)$/', $line))
                continue;

            $matches = array();
            if (!preg_match('/^(\w+)\s?=\s?(.*)$/', trim($line, "\t\r\n "), $matches))
                FaZend_Exception::raise('Helper_Publish_InvalidSyntax', "Error in access.pthml file, line #$id: $line");
            
            if (!$this->_acl->has($matches[1]))
                $this->_acl->addResource($matches[1]);
            
            // what rights are assigned?
            switch (trim($matches[2])) {
                // no access
                case '':
                    $this->_acl->deny(null, $matches[1]);
                    break;
                
                // for reading
                case 'r':
                    $this->_acl->allow(null, $matches[1]);
                    break;
                    
                // for writing
                case 'w':
                case 'rw':
                    $this->_acl->allow(null, $matches[1], 'rw');
                    break;
                
                // email address
                default:
                    if (!$this->_acl->hasRole($matches[2]))
                        $this->_acl->addRole($matches[2]);
                    $this->_acl->allow($matches[2], $matches[1]);
                    break;
            }
        }
        
    }

}