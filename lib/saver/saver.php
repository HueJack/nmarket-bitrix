<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 10:24
 */

namespace Fgsoft\Nmarket\Saver;

use Fgsoft\Nmarket\Node\Node;
use Fgsoft\Nmarket\ExternalId\ExternalId;

interface Saver
{
    public function save();
}