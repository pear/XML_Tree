<?php
require_once 'PEAR/PackageFileManager.php';

$version = '2.0RC1';
$notes = <<<EOT
*** Minor BC Breakages Have Occured ***

* Fixes all known bugs:
#89, #766, #905, #1125, #1253, #1414, #492, #555, #1238

* Returns PEAR_Error objects if an invalid element/attribute name is given (BC Break)

* \$object->error contains PEAR_Error if an error occurs, should be checked when creating the root element

* Added the ability to encapsulate all CDATA in <![CDATA[]]> Sections (see XML_Tree::useCdataSections()) or a specific
node's CDATA using the new argument to XML_Tree_Node::XML_Tree_Node() and XML_Tree_Node::addChild())
EOT;

$description =<<<EOT
Allows for the building of XML data structures using a tree
representation, without the need for an extension like DOMXML.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'XML_Tree',
    'summary'           => 'Represent XML data in a tree structure',
    'description'       => $description,
    'version'           => $version,
    'state'             => 'beta',
    'license'           => 'PHP License',
    'ignore'            => array('package.php', 'package.xml', '*.bak', '*src*', '*.tgz', '*pear_media*', '*tests*'),
	'filelistgenerator' => 'cvs', // other option is 'file'
    'notes'             => $notes,
    'changelogoldtonew' => false,
    'baseinstalldir'    => 'XML',
    'packagedirectory'  => '',
    'simpleoutput'      => true
    ));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('davey','lead','Davey Shafik','davey@php.net');

$package->addDependency('auto');
$package->addDependency('XML_Parser', '1.1.0', 'ge', 'pkg', false);


if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
?>