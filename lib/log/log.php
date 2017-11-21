<?php
/**
 * User: Nikolay Mesherinov
 * Date: 21.11.2017
 * Time: 14:54
 */

namespace Fgsoft\Nmarket\Log;

class Log
{
    private $key;

    private $value;

    public function __construct($key, $value)
    {
        if (empty($key) || empty($value)) {
            throw new \InvalidArgumentException('Key or Value is empty');
        }

        $this->key = $key;
        $this->value = $value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getAsArray()
    {
        return [$this->key => $this->value];
    }
}

