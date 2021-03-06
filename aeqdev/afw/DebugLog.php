<?php

namespace aeqdev\afw;

class DebugLog implements \aeqdev\APDO\ILog, \ArrayAccess
{

    private $timeStart;
    private $timeLast;
    private $memoryLast;
    private $log = [];

    public $enabled = false;
    public $jsConsole = false;
    public $phpLog = false;
    public $textOutput = false;
    public $echo = false;

    function __construct($timeStart = null)
    {
        $this->timeLast = $this->timeStart = $timeStart ?: microtime(true);
    }

    function __destruct()
    {
        $this->flush();
    }

    function flush()
    {
        if (!$this->enabled) return;
        if ($this->jsConsole) {
            $this->printJsConsole();
        }
        if ($this->phpLog) {
            $this->printPhpLog();
        }
        if ($this->textOutput) {
            $this->printTextOutput();
        }
        $this->log = [];
    }

    function debug($message)
    {
        if (!$this->enabled) return;
        $this->log [] = $message;

        if ($this->echo) {
            echo $message, "\n";
        }
    }

    function printf($format, $args = null)
    {
        if (!$this->enabled) return;
        $args = func_get_args();
        unset($args[0]);
        $this->debug(vsprintf($format, $args));
    }

    function print_r($expression)
    {
        if (!$this->enabled) return;
        $this->debug(print_r($expression, true));
    }

    function resources($prefix = null)
    {
        if (!$this->enabled) return;
        $time = microtime(true);
        $memory = memory_get_usage() / pow(2, 20);
        $this->printf(
            '%30s | point: %8.3f s %8.3f Mb | interval: %8.3f s %+8.3f Mb | peak: %8.3f Mb',
            $prefix,
            $time - $this->timeStart,
            $memory,
            $time - $this->timeLast,
            $memory - $this->memoryLast,
            memory_get_peak_usage() / pow(2, 20)
        );
        $this->timeLast = $time;
        $this->memoryLast = $memory;
    }

    private function printJsConsole()
    {
        if (!$this->enabled) return;
        echo "<script>\n";
        foreach ($this->log as $message) {
            $message = str_replace(['"', "\n"], ['\\"', "\\n\\\n"], $message);
            echo "console.log(\"$message\");\n";
        }
        echo "</script>\n";
    }

    private function printPhpLog()
    {
        if (!$this->enabled) return;
        foreach ($this->log as $message) {
            error_log($message);
        }
    }

    private function printTextOutput()
    {
        if (!$this->enabled) return;
        echo "\n";
        foreach ($this->log as $message) {
            echo $message, "\n";
        }
    }

    function offsetExists($offset)
    {
        return isset($this->log[$offset]);
    }

    function offsetGet($offset)
    {
        return isset($this->log[$offset]) ? $this->log[$offset] : null;
    }

    function offsetSet($offset, $value)
    {
        if (!isset($offset)) {
            $this->debug($value);
        } else {
            $this->log[$offset] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->log[$offset]);
    }

}
