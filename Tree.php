<?php
//
// +----------------------------------------------------------------------+
// | PEAR :: XML_Tree                                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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
// |          Michele Manzato <michele.manzato@verona.miz.it>             |
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
*    $root =& $tree->addRoot('root');
*    $foo  =& $root->addChild('foo');
*
*    header('Content-Type: text/xml');
*    $tree->dump();
*
* @author  Bernd Römer <berndr@bonn.edu>
* @package XML
* @version $Version$ - 1.0
*/
class XML_Tree extends XML_Parser
{
    /**
    * File Handle
    *
    * @var  resource
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
    * @var  object XML_Tree_Node
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
    * @param  string  Filename
    * @param  string  XML Version
    */
    function XML_Tree($filename = '', $version = '1.0')
    {
        $this->filename = $filename;
        $this->version  = $version;
    }

    /**
    * Return the root XML_Tree_Node.
    *
    * @access public
    */
    function &getRoot()
    {
        if (!is_null($this->root)) {
            return $this->root;
        } else {
            return $this->raiseError("No root");
        }
    }

    /**
    * Add root node.
    *
    * @param  string  $name          name of root element
    * @return object XML_Tree_Node   reference to root node
    *
    * @access public
    */
    function &addRoot($name, $content = '', $attributes = array())
    {
        $this->root = &new XML_Tree_Node($name, $content, $attributes);
        return $this->root;
    }

    /**
    * @deprecated
    */
    function &add_root($name, $content = '', $attributes = array()) {
        return $this->addRoot($name, $content, $attributes);
    }

    /**
    * inserts a child/tree (child) into tree ($path,$pos) and
    * maintains namespace integrity
    *
    * @param array      $path           path to parent of child to remove
    * @param integer    $pos            position of child to be inserted in its parents children-list
    * @param mixed      $child          child-node (by XML_Tree,XML_Node or Name)
    * @param string     $content        content (text) for new node
    * @param array      $attributes     attribute-hash for new node
    *
    * @return object XML_Tree_Node inserted child (node)
    * @access public
    */
    function &insertChild($path,$pos,$child, $content = '', $attributes = array())
    {
        // update namespace to maintain namespace integrity
        $count=count($path);
        foreach ($this->namespace as $key => $val) {
            if ((array_slice($val,0,$count)==$path) && ($val[$count]>=$pos)) {
                $this->namespace[$key][$count]++;
            }
        }

        $parent = &$this->get_node_by_path($path);
        return $parent->insertChild($pos,$child,$content,$attributes);
    }

    /**
    * @deprecated
    */
    function &insert_child($path,$pos,$child, $content = '', $attributes = array()) {
        return $this->insertChild($path, $child, $content, $attributes);
    }

    /*
    * removes a child ($path,$pos) from tree ($path,$pos) and
    * maintains namespace integrity
    *
    * @param array      $path   path to parent of child to remove
    * @param integer    $pos    position of child in parents children-list
    *
    * @return object XML_Tree_Node parent whichs child was removed
    * @access public
    */
    function &removeChild($path,$pos)
    {
        // update namespace to maintain namespace integrity
        $count=count($path);
        foreach($this->namespace as $key => $val) {
            if (array_slice($val,0,$count)==$path) {
                if ($val[$count]==$pos) {
                    unset($this->namespace[$key]);
                    break;
                }
                if ($val[$count]>$pos) {
                    $this->namespace[$key][$count]--;
                }
            }
        }

        $parent=&$this->get_node_by_path($path);
        return($parent->remove_child($pos));
    }

    /**
    * @deprecated
    */
    function &remove_child($path, $pos) {
        return $this->removeChild($path, $pos);
    }

    /*
    * Maps a xml file to a objects tree
    *
    * @return mixed The objects tree (XML_tree or an Pear error)
    * @access public
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
        $err = $this->parse();
        if (PEAR::isError($err)) {
            return $err;
        }
        return $this->root;
    }

    function getTreeFromString($str)
    {
        $this->folding = false;
        $this->XML_Parser(null, 'event');
        $this->cdata = null;
        $err = $this->parseString($str);
        if (PEAR::isError($err)) {
            return $err;
        }
        return $this->root;
    }

    /**
    * Handler for the xml-data
    *
    * @param mixed  $xp         ignored
    * @param string $elem       name of the element
    * @param array  $attribs    attributes for the generated node
    *
    * @access private
    */
    function startHandler($xp, $elem, &$attribs)
    {
        // root elem
        if (!isset($this->i)) {
            $this->obj1 =& $this->addRoot($elem, null, $attribs);
            $this->i = 2;
        } else {
            // mixed contents
            if (!empty($this->cdata)) {
                $parent_id = 'obj' . ($this->i - 1);
                $parent    =& $this->$parent_id;
                $parent->children[] = &new XML_Tree_Node(null, $this->cdata);
            }
            $obj_id = 'obj' . $this->i++;
            $this->$obj_id = &new XML_Tree_Node($elem, null, $attribs);
        }
        $this->cdata = null;
        return null;
    }

    /**
    * Handler for the xml-data
    *
    * @param mixed  $xp         ignored
    * @param string $elem       name of the element
    *
    * @access private
    */
    function endHandler($xp, $elem)
    {
        $this->i--;
        if ($this->i > 1) {
            $obj_id = 'obj' . $this->i;
            // recover the node created in StartHandler
            $node   =& $this->$obj_id;
            // mixed contents
            if (count($node->children) > 0) {
                if (trim($this->cdata)) {
                    $node->children[] = &new XML_Tree_Node(null, $this->cdata);
                }
            } else {
                $node->setContent($this->cdata);
            }
            $parent_id = 'obj' . ($this->i - 1);
            $parent    =& $this->$parent_id;
            // attach the node to its parent node children array
            $parent->children[] = $node;
        }
        $this->cdata = null;
        return null;
    }

    /*
    * The xml character data handler
    *
    * @param mixed  $xp         ignored
    * @param string $data       PCDATA between tags
    *
    * @access private
    */
    function cdataHandler($xp, $data)
    {
        if (trim($data)) {
            $this->cdata .= $data;
        }
    }

    /**
    * Get a copy of this tree.
    *
    * @return object XML_Tree
    * @access public
    */
    function clone()
    {
        $clone = &new XML_Tree($this->filename,$this->version);
        $clone->root = $this->root->clone();

        // clone all other vars
        $temp=get_object_vars($this);
        foreach($temp as $varname => $value) {
            if (!in_array($varname, array('filename','version','root'))) {
                $clone->$varname = $value;
            }
        }
        return $clone;
    }

    /**
    * Print text representation of XML tree. If $xmlHeader is true then
    * generate also the addtional XML header directive (nice for debugging).
    *
    * @access public
    */
    function dump($xmlHeader = false)
    {
        if ($xmlHeader) {
            header('Content-type: text/xml');
        }
        echo $this->get();
    }

    /**
    * Get text representation of XML tree.
    *
    * @return  string  XML
    * @access public
    */
    function &get()
    {
        $out = '<?xml version="' . $this->version . "\"?>\n";
        if (!is_null($this->root)) {
            if (!is_object($this->root) ||
                (get_class($this->root) != 'xml_tree_node'))
            {
                return $this->raiseError("Bad XML root node");
            }
            $out .= $this->root->get();
        }
        return $out;
    }

    /**
    * Get current namespace.
    *
    * @param  string  $name namespace
    * @return string
    *
    * @access public
    */
    function &getName($name)
    {
        return $this->root->getElement($this->namespace[$name]);
    }

    /**
    * @deprecated
    */
    function &get_name($name) {
        return $this->getName($name);
    }

    /**
    * Register a namespace.
    *
    * @param  string  $name namespace
    * @param  string  $path path
    *
    * @access public
    */
    function registerName($name, $path)
    {
        $this->namespace[$name] = $path;
    }

    /**
    * @deprecated
    */
    function register_name($name, $path) {
        return $this->registerName($name, $path);
    }

    /**
    * Get a reference to a node. Node is searched by its 'path'.
    *
    * @param  mixed   Path to node. Can be either a string (slash-separated
    *   children names) or an array (sequence of children names) both
    *   of them starting from node. Note that the first name in sequence
    *   must be the name of the document root.
    * @return object  reference to the XML_Tree_Node found, or PEAR_Error if
    *   the path does not exist. If more than one element matches then only
    *   the first match is returned.
    * @access public
    */
    function &getNodeAt($path)
    {
        if (is_null($this->root)) {
            return $this->raiseError("XML_Tree hasn't a root node");
        }

        if (is_string($path)) {
            $path = explode("/", $path);
        }

        if (sizeof($path) == 0) {
            return $this->raiseError("Path to node is empty");
        }

        $path1 = $path;
        $rootName = array_shift($path1);

        if ($this->root->name != $rootName) {
            return $this->raiseError("Path does not match the document root");
        }

        $x =& $this->root->getNodeAt($path1);

        if (!PEAR::isError($x)) {
            return $x;
        }
        // No node with that name found
        return $this->raiseError("Bad path to node: [".implode('/', $path)."]");
    }
}
?>
