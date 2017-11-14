<?php
/**
 * User: Nikolay Mesherinov
 * Date: 14.11.2017
 * Time: 13:08
 */

namespace Fgsoft\Nmarket\Cache;

interface Cache
{
    public function set($key, $value);

    public function get($key);
}