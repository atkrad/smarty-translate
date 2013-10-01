<?php
/**
 * Class PotGenerator
 */
class PotGenerator
{
    /**
     * Gettext format strings
     *
     * @var string
     */
    private $formatStrings = 'php-format';
    /**
     * All pot strings
     *
     * @var array
     */
    private static $potString = array();
    /**
     * Default pot headers file
     *
     * @var array
     */
    private $potHeader = array(
        'Project-Id-Version' => 'This is the name and version of the package.',
        'Report-Msgid-Bugs-To' => 'It contains an email address or URL where you can report bugs in the untranslated strings',
        'POT-Creation-Date' => '',
        'PO-Revision-Date' => '',
        'Last-Translator' => '',
        'Language-Team' => '',
        'Language' => '',
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
        'Plural-Forms' => 'nplurals=2; plural=n != 1;',
    );


    /**
     * Set Format Strings
     *
     * @param string $formatStrings
     */
    public function setFormatStrings($formatStrings)
    {
        $this->formatStrings = $formatStrings;
    }

    /**
     * Get Format Strings
     *
     * @return string
     */
    public function getFormatStrings()
    {
        return $this->formatStrings;
    }

    /**
     * Set pot header
     *
     * @param array $headers
     */
    public function setPotHeader($headers = array())
    {
        $defaultHeaders = array(
            'POT-Creation-Date' => date('Y-m-d H:iO'),
        );

        $this->potHeader = array_merge($this->potHeader, $defaultHeaders, $headers);
    }

    /**
     * Get pot header
     *
     * @return string
     */
    public function getPotHeader()
    {
        $ph = array();
        foreach ($this->potHeader as $headKey => $headValue) {
            $ph[] = "\"{$headKey}: {$headValue}\"";
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
     * @return string
     */
    private function getFlag()
    {
        return "#, {$this->getFormatStrings()}";
    }

    /**
     * Generate pot file
     */
    public function generate()
    {
        $output = array();
        $output[] = $this->getPotHeader();
        $output[] = "";
        foreach (self::$potString as $ps) {
            $output[] = $this->getReference($ps['reference']);
            //$output[] = $this->getFlag();
            $output[] = $ps['string'];
            $output[] = "";
        }
        echo implode("\n", $output);
    }

    /**
     * Filter msg string
     *
     * @param string $string
     *
     * @return mixed
     */
    private function filterString($string)
    {
        //$string = stripslashes($string);
        $string = str_replace('"', '\"', $string);
        $string = str_replace("\r\n", '\n', $string);
        //$string = str_replace("\n", '\n', $string);
        return $string;
    }

}