<?php
/**
 * Autoloader configuration for test classes in this module
 */
$testNamespace          = 'VivoTest';
$autoloadConfig         = array(
    'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(
            $testNamespace => __DIR__ . '/' . $testNamespace,
        ),
    ),
);
return $autoloadConfig;