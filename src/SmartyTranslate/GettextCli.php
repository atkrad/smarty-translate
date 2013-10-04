<?php

namespace SmartyTranslate;

/**
 * Class GettextCli
 *
 * @package SmartyTranslate
 */
class GettextCli extends Cli
{
    private $gtGenObject;

    public function __construct($args)
    {
        parent::__construct($args);
        $this->gtGenObject = new GettextGenerator();
        $this->init();
    }

    private function init()
    {
        $this->setCopyrightHolder();
        $this->setPackageName();
        $this->setPackageVersion();
    }

    public function getFiles()
    {
        return $this->getArguments();
    }

    private function setCopyrightHolder()
    {
        if ($this->optionExists('copyright-holder')) {
            $this->gtGenObject->setCopyrightHolder($this->getOption('copyright-holder'));
        }
    }

    private function setPackageName()
    {
        if ($this->optionExists('package-name')) {
            $this->gtGenObject->setPackageName($this->getOption('package-name'));
        }
    }

    private function setPackageVersion()
    {
        if ($this->optionExists('package-version')) {
            $this->gtGenObject->setPackageVersion($this->getOption('package-version'));
        }
    }

}