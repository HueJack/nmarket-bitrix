<?php
/**
 * Генерация внешнего кода из nodeOffer
 * User: Nikolay Mesherinov
 * Date: 08.11.2017
 * Time: 11:39
 */

namespace Fgsoft\Nmarket\ExternalId;

use Fgsoft\Nmarket\Node\Node;

interface ExternalId
{
    public function __construct(Node $node, $key, $sugar = '');

    /**
     * Возвращает EXTERNAL_ID для элемента
     * @return string
     */
    public function get();

}