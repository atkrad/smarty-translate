<?php
namespace SmartyTranslate;

use ArrayObject;

/**
 * Class Cli
 *
 * @package SmartyTranslate
 */
class Cli
{

    private static $exec = '';
    private static $options = array();
    private static $flags = array();
    private static $arguments = array();
    private $argsServer = array();

    public function __construct($args = null)
    {
        if (!is_null($args)) {
            $this->setArgsServer($args);
            $this->parseArguments();
        }
    }

    /**
     * @param array $arguments
     */
    protected static function setArguments($arguments)
    {
        self::$arguments[] = $arguments;
    }

    /**
     * @return array
     */
    protected static function getArguments()
    {
        return self::$arguments;
    }

    /**
     * @param $key
     * @param $value
     *
     * @internal param array $options
     */
    protected static function setOptions($key, $value)
    {
        self::$options[$key] = $value;
    }

    /**
     * @return array
     */
    protected static function getOptions()
    {
        return self::$options;
    }

    /**
     * @param string $exec
     */
    protected static function setExec($exec)
    {
        self::$exec = $exec;
    }

    /**
     * @return string
     */
    protected static function getExec()
    {
        return self::$exec;
    }

    /**
     * @param array $flags
     */
    protected static function setFlags($flags)
    {
        self::$flags[] = $flags;
    }

    /**
     * @return array
     */
    protected static function getFlags()
    {
        return self::$flags;
    }

    /**
     * Set array of arguments passed to the script.
     *
     * @param array $argsServer
     */
    protected function setArgsServer($argsServer)
    {
        $this->argsServer = $argsServer;
    }

    /**
     * Get array of arguments passed to the script.
     *
     * @return array
     */
    protected function getArgsServer()
    {
        return $this->argsServer;
    }

    public function parseArguments()
    {
        $obj = new ArrayObject($this->getArgsServer());
        $it = $obj->getIterator();

        while ($it->valid()) {
            if ($it->key() == 0) {
                $this->setExec($it->current());
            }

            if ($this->isOption($it->current())) {
                $option = substr($it->current(), 2);

                if ($this->isValidOption($option) !== false) {
                    $this->setOptions(explode('=', $option, 2)[0], explode('=', $option, 2)[1]);
                } else {
                    die(sprintf("This \"--%s\" option is not valid", $option));
                }
            }

            if ($this->isFlag($it->current())) {
                for ($i = 1; isset($it->current()[$i]); $i++) {
                    $this->setFlags($it->current()[$i]);
                }
            }

            if ($this->isValidArguments($it->current())) {
                $this->setArguments($it->current());
            }

            $it->next();
        }
    }

    public function getOption($option)
    {
        return $this->getOptions()[$option];
    }

    public function optionExists($option)
    {
        return array_key_exists($option, $this->getOptions());
    }

    public function flagExists($flag)
    {
        return in_array($flag, $this->getFlags());
    }

    public function argumentExists($arguments)
    {
        return in_array($arguments, $this->getArguments());
    }

    private function isValidArguments($arguments)
    {
        return (!$this->isFlag($arguments) && !$this->isOption($arguments) && $this->getExec() != $arguments);
    }

    private function isValidOption($option)
    {
        return strpos($option, '=');
    }

    private function isOption($arg)
    {
        return (substr($arg, 0, 2) === '--');
    }

    private function isFlag($arg)
    {
        $output = preg_match('/^[-]\w+/', $arg, $matches);

        if ($output) {
            return $matches;
        } else {
            return $output;
        }
    }
}