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

/**
* PEAR::XML_Tree
*
* @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
* @package XML_Tree
* @version 1.0  15-Aug-2001
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
    * Get a copy of this tree.
    *
    * @return  object XML_Tree
    */
    function copy() {
        return $this;
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
    * Print text representation of XML tree.
    */
    function dump() {
        echo $this->get();
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

    /**
    * Get current namespace.
    *
    * @param  string  namespace
    * @return string
    */
    function &get_name($name) {
        return $this->root->get_element($this->namespace[$name]);
    }
}

/**
* PEAR::XML_Tree_Node
*
* @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
* @package XML_Tree
* @version 1.0  15-Aug-2001
*/
class XML_Tree_Node {
    /**
    * Attributes of this node
    *
    * @var  array
    */
    var $attributes;

    /**
    * Children of this node
    *
    * @var  array
    */
    var $children;

    /**
    * Content
    *
    * @var  string
    */
    var $content;

    /**
    * Name
    *
    * @var  string
    */
    var $name;

    /**
    * Constructor
    *
    * @param  string  name
    * @param  string  content
    * @param  array   attributes
    */
    function XML_Tree_Node($name, $content = '', $attributes = array()) {
        $this->attributes = $attributes;
        $this->children   = array();
        $this->content    = xml_entities($content);
        $this->name       = strtolower($name);
    }

    /**
    * Adds a child node to this node.
    *
    * @param  mixed   child
    * @param  string  content
    * @param  array   attributes
    * @return object  reference to new child node
    */
    function &add_child($child, $content = '', $attributes = array()) {
        $index = sizeof($this->children);

        if (is_object($child)) {
            if (strtolower(get_class($child)) == 'xml_tree_node') {
                $this->children[$index] = $child;
            }

            if (strtolower(get_class($child)) == 'xml_tree' && isset($child->root)) {
                $this->children[$index] = $child->root->get_element();
            }
        } else {
            $this->children[$index] = new XML_Tree_Node($child, $content, $attributes);
        }

        return $this->children[$index];
    }

    /**
    * Gets an attribute by its name.
    *
    * @param  string  name
    * @return string  attribute
    */
    function get_attribute($name) {
        return $this->attributes[strtolower($name)];
    }

    /**
    * Gets an element by its 'path'.
    *
    * @param  string  path
    * @return object  element
    */
    function &get_element($path) {
        if (sizeof($path) == 0) {
            return $this;
        }

        $next = array_shift($path);

        return $this->children[$next]->get_element($path);
    }

    /**
    * Sets an attribute.
    *
    * @param  string  name
    * @param  string  value
    */
    function set_attribute($name, $value = '') {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
    * Unsets an attribute.
    *
    * @param  string  name
    */
    function unset_attribute($name) {
        unset($this->attributes[strtolower($name)]);
    }

    /**
    * Returns text representation of this node.
    *
    * @return  string  xml
    */
    function &get() {
        $out = '<' . $this->name;

        foreach ($this->attributes as $name => $value) {
            $out .= ' ' . $name . '="' . $value . '"';
        }

        $out .= '>' . $this->content;

        if (sizeof($this->children) > 0) {
            $out .= "\n";

            foreach ($this->children as $child) {
                $out .= $child->get();
            }
        }

        $out .= '</' . $this->name . ">\n";

        return $out;
    }
}

function &xml_entities(&$xml) {
    $xml = str_replace(array('ü', 'Ü', 'ö',
                             'Ö', 'ä', 'Ä',
                             'ß'
                            ),
                       array('&#252;', '&#220;', '&#246;',
                             '&#214;', '&#228;', '&#196;',
                             '&#223;'
                            ),
                       $xml
                      );

    $xml = preg_replace(array("/\&([a-z\d\#]+)\;/i",
                              "/\&/",
                              "/\#\|\|([a-z\d\#]+)\|\|\#/i",
                              "/([^a-zA-Z\d\s\<\>\&\;\.\:\=\"\-\/\%\?\!\'\(\)\[\]\{\}\$\#\+\,\@_])/e"
                             ),
                        array("#||\\1||#",
                              "&amp;",
                              "&\\1;",
                              "'&#'.ord('\\1').';'"
                             ),
                        $xml
                       );

    return $xml;
}
?>
