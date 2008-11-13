<?php
require_once 'PEAR/PackageFileManager.php';

$version = '2.0.0RC2';
$notes = <<<EOT
Fixed XML_Tree::isValidName() when adding a CDATA childNode.
Dependencies like XML_DTD should now work again.
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