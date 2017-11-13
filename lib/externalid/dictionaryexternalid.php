<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:32
 */

namespace Fgsoft\Nmarket\ExternalId;


class DictionaryExternalId extends AbstractExternalId
{
    protected function generate()
    {
        $this->externalId = md5(mb_strtoupper(trim($this->node->get($this->key)) . $this->sugar));
    }

}