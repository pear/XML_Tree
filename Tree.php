<?php
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2001 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Sebastian Bergmann <sb@sebastian-bergmann.de>               |
// |          Bernd Römer <berndr@bonn.edu>                               |
// |          Christian Kühn <ck@chkuehn.de>                              |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'XML/Tree/Node.php';

/**
* PEAR::XML_Tree
*
* Purpose
*
*    Allows for the building of XML data structures
*    using a tree representation, without the need
*    for an extension like DOMXML.
*
* Example
*
*    $tree  = new XML_Tree;
*    $root =& $tree->add_root('root');
*    $foo  =& $root->add_child('foo');
*
*    header('Content-Type: text/xml');
*    $tree->dump();
*
* @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
* @package XML_Tree
* @version 1.0  16-Aug-2001
*/
class XML_Tree {
    /**
    * Namespace
    *
    * @var  array
    */
    var $namespace;

    /**
    * Root
    *
    * @var  object
    */
    var $root;

    /**
    * XML Version
    *
    * @var  string
    */
    var $version;

    /**
    * Constructor
    *
    * @param  string  XML Version
    */
    function XML_Tree($version = '1.0') {
        $this->version = $version;
    }

    /**
    * Add root node.
    *
    * @param  string  name of root element
    * @return object  reference to root node
    */
    function &add_root($name) {        
        $this->root = new XML_Tree_Node($name);
        return $this->root;
    }

    /**
    * Get a copy of this tree.
    *
    * @return  object XML_Tree
    */
    function copy() {
        return $this;
    }

    /**
    * Print text representation of XML tree.
    */
    function dump() {
        echo $this->get();
    }

    /**
    * Get text representation of XML tree.
    *
    * @return  string  XML
    */
    function &get() {
        $out = '<?xml version="' . $this->version . "\"?>\n";
        $out .= $this->root->get();

        return $out;
    }

    /**
    * Get current namespace.
    *
    * @param  string  namespace
    * @return string
    */
    function &get_name($name) {
        return $this->root->get_element($this->namespace[$name]);
    }

    /**
    * Register a namespace.
    *
    * @param  string  namespace
    * @param  string  path
    */
    function register_name($name, $path) {
        $this->namespace[$name] = $path;
    }
}
?>
