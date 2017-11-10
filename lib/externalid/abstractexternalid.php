<?php
/**
 * User: Nikolay Mesherinov
 * Date: 08.11.2017
 * Time: 11:43
 */

namespace Fgsoft\Nmarket\ExternalId;

use Fgsoft\Nmarket\Node\Node;

abstract class AbstractExternalId implements ExternalId
{
    /**
     * @var Node
     */
    protected $node;

    protected $sugar;

    protected $key;

    protected $externalId;

    public function __construct(Node $node, $key, $sugar = '')
    {
        $this->node = $node;
        $this->key = $key;
        $this->sugar = $sugar;

        $this->generate();
    }

    /**
     * Процесс генерации externalId из параметров offerNode
     * @return string
     */
    abstract protected function generate();

    public function get()
    {
        return $this->externalId;
    }
}