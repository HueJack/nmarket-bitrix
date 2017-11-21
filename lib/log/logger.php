<?php
/**
 * User: Nikolay Mesherinov
 * Date: 21.11.2017
 * Time: 14:54
 */

namespace Fgsoft\Nmarket\Log;


class Logger
{
    private static $logs;

    private static $instance;

    private function __construct(){}

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add(Log $log)
    {
       self::$logs[] = $log;
    }

    public function getLogs()
    {
        return self::$logs;
    }
    public function getAsArray()
    {
        $result = [];

        if (!empty(self::$logs)) {
            foreach (self::$logs as $log) {
                $result[] = $log->getAsArray();
            }

            return $result;
        }

        return null;
    }

    public function count()
    {
        if (is_array(self::$logs)) {
            return sizeof(self::$logs);
        }

        return 0;
    }
}

