<?php
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
// |          Christian Kühn <ck@chkuehn.de> (escape xml entities)        |
// |          Michele Manzato <michele.manzato@verona.miz.it>             |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
* PEAR::XML_Tree_Node
*
* @author  Bernd Römer <berndr@bonn.edu>
* @package XML_Tree
* @version 1.0  16-Aug-2001
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
        $this->set_content($content);
        $this->name       = $name;
    }

    /**
    * Adds a child node to this node.
    *
    * @param  mixed   child
    * @param  string  content
    * @param  array   attributes
    * @return object  reference to new child node
    */
    function &addChild($child, $content = '', $attributes = array()) {
        $index = sizeof($this->children);

        if (is_object($child)) {
            if (strtolower(get_class($child)) == 'xml_tree_node') {
                $this->children[$index] = $child;
            }

            if (strtolower(get_class($child)) == 'xml_tree' && isset($child->root)) {
                $this->children[$index] = $child->root->getElement();
            }
        } else {
            $this->children[$index] = new XML_Tree_Node($child, $content, $attributes);
        }

        return $this->children[$index];
    }

    /**
    * @deprecated
    */
    function &add_child($child, $content = '', $attributes = array()) {
        return $this->addChild($child, $content, $attributes);
    }

    /**
    * clone node and all its children (recursive)
    *
    * @return object reference to the clone-node
    */
    function &clone() {
        $clone=new XML_Tree_Node($this->name,$this->content,$this->attributes);

        $max_child=count($this->children);
        for($i=0;$i<$max_child;$i++) {
            $clone->children[]=$this->children[$i]->clone();
        }

        /* for future use....
            // clone all other vars
            $temp=get_object_vars($this);
            foreach($temp as $varname => $value)
                if (!in_array($varname,array('name','content','attributes','children')))
                    $clone->$varname=$value;
        */

        return $clone;
    }

    /**
    * inserts child ($child) to a specified child-position ($pos)
    *
    * @return  inserted node
    */
    function &insertChild($path,$pos,&$child, $content = '', $attributes = array()) {
        // direct insert of objects useing array_splice() faild :(
        array_splice($this->children,$pos,0,'dummy');
        if (is_object($child)) { // child offered is not instanziated
            // insert a single node
            if (strtolower(get_class($child)) == 'xml_tree_node') {
                $this->children[$pos]=&$child;
            }
            // insert a tree i.e insert root-element
            if (strtolower(get_class($child)) == 'xml_tree' && isset($child->root)) {
                $this->children[$pos]=$child->root->get_element();
            }
        } else { // child offered is not instanziated
            $this->children[$pos]=new XML_Tree_Node($child, $content, $attributes);
        }
        return($this);
    }

    /**
    * @deprecated
    */
    function &insert_child($path,$pos,&$child, $content = '', $attributes = array()) {
        return $this->insertChild($path,$pos,$child, $content, $attributes);
    }

    /**
    * removes child ($pos)
    *
    * @param integer pos position of child in children-list
    *
    * @return  removed node
    */
    function &removeChild($pos) {
        // array_splice() instead of a simple unset() to maintain index-integrity
        return(array_splice($this->children,$pos,1));
    }

    /**
    * @deprecated
    */
    function &remove_child($pos) {
        return $this->removeChild($pos);
    }

    /**
    * Returns text representation of this node.
    *
    * @return  string  xml
    */
    function &get()
    {
        static $deep = -1;
        static $do_ident = true;
        $deep++;
        if ($this->name !== null) {
            $ident = str_repeat('  ', $deep);
            if ($do_ident) {
                $out = $ident . '<' . $this->name;
            } else {
                $out = '<' . $this->name;
            }
            foreach ($this->attributes as $name => $value) {
                $out .= ' ' . $name . '="' . $value . '"';
            }

            $out .= '>' . $this->content;

            if (sizeof($this->children) > 0) {
                $out .= "\n";
                foreach ($this->children as $child) {
                    $out .= $child->get();
                }
            } else {
                $ident = '';
            }
            if ($do_ident) {
                $out .= $ident . '</' . $this->name . ">\n";
            } else {
                $out .= '</' . $this->name . '>';
            }
            $do_ident = true;
        } else {
            $out = $this->content;
            $do_ident = false;
        }
        $deep--;
        return $out;
    }

    /**
    * Gets an attribute by its name.
    *
    * @param  string  name
    * @return string  attribute
    */
    function getAttribute($name) {
        return $this->attributes[strtolower($name)];
    }

    /**
    * @deprecated
    */
    function get_attribute($name) {
        return $this->getAttribute($name);
    }

    /**
    * Gets an element by its 'path'.
    *
    * @param  array  path to element, specified as a sequence of indexes
    *   to the children. E.g. array(1, 2, 3) means "third child of second child
    *   of first child" of the node.
    * @return object  reference to element found, or PEAR_Error
    */
    function &getElement($path) {
        if (!is_array($path))
            $path = array($path);

        if (sizeof($path) == 0) {
            return $this;
        }

        $path1 = $path;
        $next = array_shift($path1);
        if (isset($this->children[$next]))
        {
            $x =& $this->children[$next]->getElement($path1);
            if (!PEAR::isError($x))
                return $x;
        }

        return $this->raiseError("Bad path to node: [".implode('-', $path)."]");
    }

    /**
    * Get a reference to a node. The node is searched by its 'path'.
    *
    * @param  mixed   Path to node. Can be either a string (slash-separated
    *   children names) or an array (sequence of children names) both
    *   starting from this node. Note that the first name in sequence
    *   is a child name, not the name of this node.
    * @return object  reference to the XML_Tree_Node found, or PEAR_Error if
    *   the path does not exist. If more than one element matches then only
    *   the first match is returned.
    * @access public
    */
    function &getNodeAt($path)
    {
        if (is_string($path))
            $path = explode("/", $path);

        if (sizeof($path) == 0) {
            return $this;
        }

        $path1 = $path;
        $next = array_shift($path1);

        // Get the first children of this node whose name is '$next'
        $child = null;
        for ($i = 0; $i < count($this->children); $i++)
            if ($this->children[$i]->name == $next) {
                $child =& $this->children[$i];
                break;
            }

        if (!is_null($child))
        {
            $x =& $child->getNodeAt($path1);
            if (!PEAR::isError($x))
                return $x;
        }

        // No node with that name found
        return $this->raiseError("Bad path to node: [".implode('/', $path)."]");
    }

    /**
    * @deprecated
    */
    function &get_element($path)
    {
        return $this->getElement($path);
    }

    /**
    * Sets an attribute.
    *
    * @param  string  name
    * @param  string  value
    */
    function setAttribute($name, $value = '')
    {
        $this->attributes[strtolower($name)] = $value;
    }

    /**
    * @deprecated
    */
    function set_attribute($name, $value = '')
    {
        return $this->setAttribute($name, $value);
    }

    /**
    * Unsets an attribute.
    *
    * @param  string  name
    */
    function unsetAttribute($name)
    {
        unset($this->attributes[strtolower($name)]);
    }

    /**
    * @deprecated
    */
    function unset_attribute($name)
    {
        return $this->unsetAttribute($name);
    }

    /**
    *
    *
    */
    function setContent(&$content)
    {
        $this->content = $this->_xml_entities($content);
    }

    function set_content(&$content)
    {
        return $this->setContent($content);
    }

    /**
    * Escape XML entities.
    *
    * @param   string  xml
    * @return  string  xml
    * @access  private
    */
    function _xml_entities($xml) {
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

    /**
    * Decode XML entities.
    *
    * @param   string  xml to decode
    * @return  string  xml decoded
    * @access  private
    */
    function decodeXmlEntities($xml)
    {
        static $trans_tbl = null;
        if (!$trans_tbl) {
            $trans_tbl = get_html_translation_table(HTML_ENTITIES);
            $trans_tbl = array_flip ($trans_tbl);
        }
        for ($i = 1; $i <= 255; $i++)
        {
            $ent = sprintf("&#%03d;", $i);
            $ch = chr($i);
            $xml = str_replace($ent, $ch, $xml);
        }

        return strtr($xml, $trans_tbl);
    }


    /**
    * Print text representation of XML tree.
    */
    function dump() {
        echo $this->get();
    }
}
?>
