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
// | Authors: Bernd Römer <berndr@bonn.edu>                               |
// |          Sebastian Bergmann <sb@sebastian-bergmann.de>               |
// |          Tomas V.V.Cox <cox@idecnet.com> (tree mapping from xml file)|
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'XML/Parser.php';
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
* @author  Bernd Römer <berndr@bonn.edu>
* @package XML_Tree
* @version 1.0  16-Aug-2001
*/
class XML_Tree extends XML_Parser
{
    /**
    * File Handle
    *
    * @var  ressource
    */
    var $file = NULL;

    /**
    * Filename
    *
    * @var  string
    */
    var $filename = '';

    /**
    * Namespace
    *
    * @var  array
    */
    var $namespace = array();

    /**
    * Root
    *
    * @var  object
    */
    var $root = NULL;

    /**
    * XML Version
    *
    * @var  string
    */
    var $version = '1.0';

    /**
    * Constructor
    *
    * @param  string  XML Version
    */
    function XML_Tree($filename = '', $version = '1.0') {
        $this->filename = $filename;
        $this->version  = $version;
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

    /*
    * Maps a xml file to a objects tree
    *
    * @return object The objects tree or an Pear error
    */
    function &getTreeFromFile ()
    {
        $this->folding = false;
        $this->XML_Parser(null, 'event');
        $err = $this->setInputFile($this->filename);
        if (PEAR::isError($err)) {
            return $err;
        }
        $this->cdata = null;
        $this->store_cdata = false;
        $err = $this->parse();
        if (PEAR::isError($err)) {
            return $err;
        }
        return $this->root;
    }

    function StartHandler($xp, $elem, &$attribs)
    {
        // root elem
        if (!isset($this->i)) {
            $this->obj1 =& $this->add_root($elem);
            $this->i = 2;
        } else {
            $obj_id = 'obj' . $this->i++;
            $this->$obj_id =& new XML_Tree_Node($elem, null, $attribs);
        }
        $this->store_cdata = true;
        return NULL;
    }

    function EndHandler($xp, $elem)
    {
        $this->i--;
        if ($this->i > 1) {
            $obj_id = 'obj' . $this->i;
            // recover the node created in StartHandler
            $node   =& $this->$obj_id;
            $node->set_content($this->cdata);
            $parent_id = 'obj' . ($this->i - 1);
            $parent    =& $this->$parent_id;
            // attach the node to its parent node children array
            $parent->children[] = $node;
        }
        $this->cdata = null;
        $this->store_cdata = false;
        return NULL;
    }

    /*
    * The xml character data handler
    */
    function cdataHandler($xp, $data)
    {
        // only store data inside tags
        if ($this->store_cdata) {
            $this->cdata .= $data;
        }
    }

    /**
    * Get a copy of this tree.
    *
    * @return  object XML_Tree
    */
    function clone() {
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
