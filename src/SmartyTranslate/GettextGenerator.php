<?php
namespace SmartyTranslate;

/**
 * Class GettextGenerator
 *
 * @package SmartyTranslate
 */
class GettextGenerator
{

    /**
     * All pot strings
     *
     * @var array
     */
    private static $potString = array();
    /**
     * Package name
     *
     * @var string
     */
    private static $packageName = '';
    /**
     * Package version
     *
     * @var string
     */
    private static $packageVersion = '';
    /**
     * Default copyright header
     *
     * @var string
     */
    private static $copyrightHolder = <<<MSG
# LANGUAGE (LOCALE) translation for PACKAGE.
# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER
# This file is distributed under the same license as the PACKAGE package.
# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.
#
MSG;
    /**
     * Default gettext headers file
     *
     * @var array
     */
    private $header = array(
        'Project-Id-Version' => 'PACKAGE VERSION',
        'Report-Msgid-Bugs-To' => '',
        'POT-Creation-Date' => '',
        'PO-Revision-Date' => '2013-MO-DA HO:MI+ZONE',
        'Last-Translator' => 'FULL NAME <EMAIL@ADDRESS>',
        'Language-Team' => 'LANGUAGE <LL@li.org>',
        'Language' => '',
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
        'Plural-Forms' => 'nplurals=2; plural=n != 1;',
    );

    public function __construct()
    {
        $this->setHeader();
    }

    /**
     * @param mixed $packageName
     */
    public static function setPackageName($packageName)
    {
        self::$packageName = $packageName;
    }

    /**
     * @return string
     */
    public static function getPackageName()
    {
        return self::$packageName;
    }

    /**
     * @param mixed $packageVersion
     */
    public static function setPackageVersion($packageVersion)
    {
        self::$packageVersion = $packageVersion;
    }

    /**
     * @return string
     */
    public static function getPackageVersion()
    {
        return self::$packageVersion;
    }

    /**
     * @param string $copyrightHolder
     */
    public static function setCopyrightHolder($copyrightHolder)
    {
        $cphArr = explode('\n', $copyrightHolder);
        $output = [];

        foreach ($cphArr as $cph) {
            $output[] = "# {$cph}";
        }

        $output[] = "#";

        self::$copyrightHolder = implode("\n", $output);
    }

    /**
     * @return string
     */
    public static function getCopyrightHolder()
    {
        return self::$copyrightHolder;
    }

    /**
     * Set pot header
     *
     * @param array $headers
     */
    public function setHeader($headers = array())
    {
        $defaultHeaders = [];
        if ($this->getPackageName() || $this->getPackageVersion()) {
            $defaultHeaders['Project-Id-Version'] = $this->getPackageName() . ' ' . $this->getPackageVersion();
        }
        $defaultHeaders['POT-Creation-Date'] = date('Y-m-d H:iO');

        $this->header = array_merge($this->header, $defaultHeaders, $headers);
    }

    /**
     * Get pot header
     *
     * @return string
     */
    public function getHeader()
    {
        $ph = array();
        foreach ($this->header as $headKey => $headValue) {
            $ph[] = "\"{$headKey}: {$headValue}\\n\"";
        }

        return implode("\n", $ph);
    }

    private function getMessageId($string)
    {
        $sString = array();
        $filterString = $this->filterString($string);
        $nArr = explode('\n', $filterString);
        if (count($nArr) > 1) {
            $sString[] = "msgid \"\"";

            foreach ($nArr as $stringArr) {
                if (end($nArr) != $stringArr) {
                    $sString[] = "\"{$stringArr}\\n\"";
                } elseif (end($nArr) == $stringArr && strrpos($this->filterString($string), '\n')) {
                    $sString[] = "\"{$stringArr}\"";
                } else {
                    $sString[] = "\"{$stringArr}\"";
                }
            }
        } else {
            $sString[] = "msgid \"{$filterString}\"";
        }
        return implode("\n", $sString);
    }

    /**
     * Set singular gettext
     *
     * @param string $string
     * @param string $filePath
     * @param string $lineNum
     */
    public function setSingular($string, $filePath, $lineNum)
    {
        $sString = [];
        $sString[] = $this->getMessageId($string);
        $sString[] = "msgstr \"\"";

        $ss = implode("\n", $sString);

        if (!array_key_exists($string, self::$potString)) {
            self::$potString[$string] = array(
                'string' => $ss,
                'reference' => array($this->setReference($filePath, $lineNum))
            );
        } else {
            self::$potString[$string]['reference'][] = $this->setReference($filePath, $lineNum);
        }
    }

    /**
     * Set plural gettext
     *
     * @param string $string
     * @param string $pluralString
     * @param string $filePath
     * @param string $lineNum
     */
    public function setPlural($string, $pluralString, $filePath, $lineNum)
    {
        $pString = array();
        $pString[] = "msgid \"{$this->filterString($string)}\"";
        $pString[] = "msgid_plural \"{$this->filterString($pluralString)}\"";
        $pString[] = "msgstr[0] \"\"";
        $pString[] = "msgstr[1] \"\"";

        $ps = implode("\n", $pString);

        if (!array_key_exists($string, self::$potString)) {
            self::$potString[$string] = array(
                'string' => $ps,
                'reference' => array($this->setReference($filePath, $lineNum))
            );
        } else {
            self::$potString[$string]['reference'][] = $this->setReference($filePath, $lineNum);
        }
    }

    /**
     * Set reference
     *
     * @param string $filePath
     * @param string $lineNum
     *
     * @return string
     */
    private function setReference($filePath, $lineNum)
    {
        return "#: {$filePath}:{$lineNum}";
    }

    /**
     * Get reference
     *
     * @param string $reference
     *
     * @return string
     */
    private function getReference($reference)
    {
        return implode("\n", $reference);
    }

    /**
     * Get flag
     *
     * @param $flag
     *
     * @return string
     */
    protected function getFlag($flag)
    {
        return "#, {$flag}";
    }

    protected function getZeroTranslated()
    {
        $output = [];
        $output[] = "msgid \"\"";
        $output[] = "msgstr \"\"";
        return implode("\n", $output);
    }

    /**
     * Generate pot file
     */
    public function generate()
    {
        $output = array();
        $output[] = $this->getCopyrightHolder();
        $output[] = $this->getFlag('fuzzy');
        $output[] = $this->getZeroTranslated();
        $output[] = $this->getHeader();
        $output[] = "";
        foreach (self::$potString as $ps) {
            $output[] = $this->getReference($ps['reference']);
            $output[] = $ps['string'];
            $output[] = "";
        }
        return implode("\n", $output);
    }

    /**
     * Filter msg string
     *
     * @param string $string
     *
     * @return mixed
     */
    protected function filterString($string)
    {
        $string = str_replace('"', '\"', $string);
        $string = str_replace("\r\n", '\n', $string);
        return $string;
    }

}