<?php
/**
 * Данные XML ветки Offer
 * User: Nikolay Mesherinov
 * Date: 08.11.2017
 * Time: 11:54
 */

namespace Fgsoft\Nmarket\Node;


class OfferNode implements Node
{
    protected $fields;

    protected $internalId;

    public function __construct(array $fields)
    {
        $this->internalId = key($fields);
        $this->fields = $fields[$this->internalId]['fields'];
    }

    /**
     * @param $fieldKey string
     * @return null|string
     */
    public function get($fieldKey)
    {
        if (empty($fieldKey) || !array_key_exists($fieldKey, $this->fields)) {
            return null;
        }

        return $this->fields[$fieldKey];
    }

    public function getInternalID()
    {
        return $this->internalId;
    }

    public function getCategory()
    {
        return $this->get('category');
    }

    /**
     * @return null|string
     */
    public function getLastUpdateDate()
    {
        return $this->get('last-update-date');
    }

    /**
     * Ипотека
     * @return string
     */
    public function getMortgage()
    {
        return $this->get('mortgage');
    }

    /**
     * Ремонт
     * Возможные значения:

        «дизайнерский»
        «евро»
        «с отделкой»
        «требует ремонта»
        «хороший»
        «частичный ремонт»
        «черновая отделка».
     * и т.п.
     *
     * @return string
     */
    public function getRenovation()
    {
        return $this->get('renovation');
    }

    /**
     * Описание
     * @return null|string
     */
    public function getDescription()
    {
        return $this->get('description');
    }

    /**
     * Признак новостройки
     * @return null|string
     */
    public function getIsNewFlat()
    {
        return $this->get('new-flat');
    }

    /**
     * Общее количество комнат
     * @return null|string
     */
    public function getRoomsAmount()
    {
        return $this->get('rooms');
    }

    /**
     * Этаж
     * @return null|string
     */
    public function getFloorNumber()
    {
        return $this->get('floor');
    }

    /**
     * Общее количество этажей в доме.
     * @return null|string
     */
    public function getFloorsTotal()
    {
        return $this->get('floors-total');
    }

    /**
     * Название жилого комплекса.
     * @return null|string
     */
    public function getBuildingName()
    {
        return $this->get('building-name');
    }

    /**
     * @return string
     * Тип дома.
     *
        Возможные значения:

        «блочный»
        «деревянный»
        «кирпичный»
        «кирпично-монолитный»
        «монолит»
        «панельный».
     */
    public function getBuildingType()
    {
        return $this->get('building-type');
    }

    /**
     * Очередь строительства.
     * @return null|string
     */
    public function getBuildingPhase()
    {
        return $this->get('building-phase');
    }

    /**
     * Секция/Блок
     * @return string
     */
    public function getBuildingSection()
    {
        return $this->get('building-section');
    }

    /**
     * Год сдачи дома
     * @return string
     */
    public function getBuildYear()
    {
        return $this->get('built-year');
    }

    /**
     * Квартал сдачи дома.
     * @return string
     */
    public function getReadyQuarter()
    {
        return $this->get('ready-quarter');
    }

    /**
     * Наличие лифта
     * @return bool
     *
     * Строго ограниченные значения:
        «да»/«нет»
        «true»/«false»
        «1»/«0»
        «+»/«-».
     */
    public function getLift()
    {
        return $this->get('lift');
    }

    /**
     * Наличие охраняемой парковки.
     * @return string
     *
     * Строго ограниченные значения:
        «да»/«нет»
        «true»/«false»
        «1»/«0»
        «+»/«-».
     */
    public function getParking()
    {
        return $this->get('parking');
    }

    /**
     * Высота потолков в метрах.
     * @return string
     */
    public function getCeilingHeight()
    {
        return $this->get('ceiling-height');
    }

    /**
     * Идентификатор комплекса в системе nmarket
     * @return integer
     */
    public function getNmarketComplexId()
    {
        return $this->get('nmarket-complex-id');
    }

    /**
     * Идентификатор строения в системе nmarket
     * Отдельный корпус
     *
     * @return int
     */
    public function getNmarketBuildingId()
    {
        return $this->get('nmarket-building-id');
    }

    /**
     * Страна
     * @return string
     */
    public function getCountry()
    {
        return $this->get('location->country');
    }

    /**
     * Название населенного пункта.
     * @return string
     */
    public function getLocalityName()
    {
        return $this->get('locality-name');
    }

    /**
     * Район населенного пункта.
     * @return string
     */
    public function getSubLocalityName()
    {
        return $this->get('sub-locality-name');
    }

    /**
     * Адрес объекта (улица и номер здания).
     * @return string
     */
    public function getAddress()
    {
        return $this->get('address');
    }

    /**
     * Изображение
     * @return string
     */
    public function getImage()
    {
        return $this->get('image');
    }

    /**
     * Стоимость
     * @return string
     */
    public function getPrice()
    {
        return $this->get('price');
    }

    /**
     * Площадь
     * @return string
     */
    public function getArea()
    {
        return $this->get('area');
    }

    /**
     * Жилая площадь
     * @return string
     */
    public function getLivingSpace()
    {
        return $this->get('living-space');
    }

    /**
     * Площадь кухни
     * @return string
     */
    public function getKitchenSpace()
    {
        return $this->get('kitchen-space');
    }

    /**
     * Тип санузла.
        Возможные значения:

        «совмещенный»
        «раздельный»
        числовое значение (например «2»).
     *
     * @return string
     */
    public function getBathroomUnit()
    {
        return $this->get('bathroom-unit');
    }

    /**
     * Тип балкона.
        Возможные значения:

        «балкон»
        «лоджия»
        «2 балкона»
        «2 лоджии»
        И т. п.
     * @return string
     */
    public function getBalcony()
    {
        return $this->get('balcony');
    }
}