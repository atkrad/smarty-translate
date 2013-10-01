<?php
/**
 * Class Extractor
 */
class Extractor
{
    private $startTag = '{';
    private $endTag = '}';
    private $smartyCommand = 'trans';
    private $smartyExtension = 'tpl';

    /**
     * @var PotGenerator
     */
    private $pgObject = null;

    public function __construct()
    {
        $this->pgObject = new PotGenerator();
    }

    public function extractFile($path)
    {
        if (is_dir($path)) {
            $this->searchInDirectory($path);
        } else {
            $this->searchInFile($path);
        }
    }

    /**
     * Set smarty start tag
     *
     * @param string $startTag
     */
    public function setStartTag($startTag)
    {
        $this->startTag = $startTag;
    }

    /**
     * Get smarty start tag
     *
     * @return string
     */
    public function getStartTag()
    {
        return $this->startTag;
    }

    /**
     * Set smarty end tag
     *
     * @param string $endTag
     */
    public function setEndTag($endTag)
    {
        $this->endTag = $endTag;
    }

    /**
     * Get smarty end tag
     *
     * @return string
     */
    public function getEndTag()
    {
        return $this->endTag;
    }

    /**
     * Set smarty command
     *
     * @param string $smartyCommand
     */
    public function setSmartyCommand($smartyCommand)
    {
        $this->smartyCommand = $smartyCommand;
    }

    /**
     * Get smarty command
     *
     * @return string
     */
    public function getSmartyCommand()
    {
        return $this->smartyCommand;
    }

    /**
     * Set smarty file extension
     *
     * @param string $smartyExtension
     */
    public function setSmartyExtension($smartyExtension)
    {
        $this->smartyExtension = $smartyExtension;
    }

    /**
     * Get smarty file extension
     *
     * @return string
     */
    public function getSmartyExtension()
    {
        return $this->smartyExtension;
    }

    private function blockMatcher($string)
    {
        $st = preg_quote($this->getStartTag());
        $et = preg_quote($this->getEndTag());
        $sc = preg_quote($this->getSmartyCommand());

        $output = preg_match_all(
            "/{$st}\s*({$sc})\s*([^{$et}]*){$et}([^{$st}]*){$st}\/\\1{$et}/",
            $string,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($output) {
            return $matches;
        } else {
            return $output;
        }
    }

    private function modifierMatcher($string)
    {
        $st = preg_quote($this->startTag);
        $et = preg_quote($this->endTag);
        $sc = preg_quote($this->smartyCommand);

        $output = preg_match_all(
            "/{$st}[^\"{$et}]+((\"|')[^\\2{$et}]+\\2[^\"'{$et}]+)*(\"|')([^\\3{$et}]+)\\3(\|[^\|{$et}]+)*\|{$sc}(\|[^\|{$et}]+)*{$et}/",
            $string,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($output) {
            return $matches;
        } else {
            return $output;
        }
    }

    private function searchInDirectory($directory)
    {
        $iterator = new DirectoryIterator($directory);
        /** @var $fileInfo DirectoryIterator */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->searchInDirectory($fileInfo->getFileInfo());
            } else {
                if ($fileInfo->getExtension() == $this->getSmartyExtension()) {
                    $this->searchInFile($fileInfo->getFileInfo());
                }
            }
        }
    }

    private function searchInFile($filePath)
    {
        $file = new SplFileObject($filePath);
        $fileContent = file_get_contents($file->getPathname());

        if ($blockMatches = $this->blockMatcher($fileContent)) {
            for ($i = 0; $i < count($blockMatches[0]); $i++) {

                $offset = $blockMatches[3][$i][1];
                $lineNumber = substr_count(substr($fileContent, 0, $offset), "\n") + 1;

                if (preg_match('/plural\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $blockMatches[2][$i][0], $match)) {
                    $this->pgObject->setPlural($blockMatches[3][$i][0], $match[1], $filePath, $lineNumber);
                } else {
                    $this->pgObject->setSingular($blockMatches[3][$i][0], $filePath, $lineNumber);
                }
            }
        }

        if ($modifierMatches = $this->modifierMatcher($fileContent)) {
            for ($i = 0; $i < count($modifierMatches[0]); $i++) {
                $offset = $modifierMatches[4][$i][1];
                $lineNumber = substr_count(substr($fileContent, 0, $offset), "\n") + 1;

                $this->pgObject->setSingular($modifierMatches[4][$i][0], $filePath, $lineNumber);
            }
        }
    }

}