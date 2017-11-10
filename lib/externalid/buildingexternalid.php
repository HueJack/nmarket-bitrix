<?php
/**
 * XML_ID оторый можно использовать как есть, без обработки
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 10:35
 */

namespace Fgsoft\Nmarket\ExternalId;


class BuildingExternalId extends AbstractExternalId
{
    protected function generate()
    {
        $this->externalId = $this->node->get('nmarket-complex-id') . '_' . $this->node->get('nmarket-building-id');
    }
}