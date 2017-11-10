<?php
/**
 * User: Nikolay Mesherinov
 * Date: 09.11.2017
 * Time: 13:48
 */

namespace Fgsoft\Nmarket\Saver;


class DictionarySaver extends AbstractSaver
{
    function fillFields()
    {
        $this->addField('NAME', $this->node->get($this->nodeKey));
        $this->addField('XML_ID', $this->externalId->get());
        $this->addField('IBLOCK_ID', $this->iblockId);

        $code = '';
        if (is_numeric($this->node->get($this->nodeKey))) {
            $code = uniqid('element_');
        } else {
            $code = \CUtil::translit($this->node->get($this->nodeKey), 'ru');
        }

        $this->addField('CODE', $code);
    }

}