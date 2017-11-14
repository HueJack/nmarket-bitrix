<?php
/**
 * User: Nikolay Mesherinov
 * Date: 14.11.2017
 * Time: 13:09
 */

namespace Fgsoft\Nmarket\Cache;


class Memcache implements Cache
{
    protected $memcache;

    protected $expire;

    public function __construct($host, $port, $expire = 1800)
    {
        $this->memcache = new \Memcache();
        $this->expire = $expire;

        if (!$this->memcache->connect($host, $port)) {
            throw new \Exception('Механизм кэширования Memcache не работает. Параметры подключения ' . print_r(['host' => $host, 'port' => $port], true));
        }
    }

    public function set($key, $value)
    {
        if ($this->memcache->set($key, $value, $this->expire)) {
            return true;
        }

        return false;
    }

    public function get($key)
    {
        return $this->memcache->get($key);
    }

}