<?php
namespace Craft;

class Venti_CriteriaModel extends ElementCriteriaModel implements \Countable
{


    protected $_cachedIds;
    protected $_cachedTotal;
    protected $_matchedElements;
    protected $_matchedElementsAtOffsets;

    /**
     * @var BaseElementType
     */
    private $_elementType;





    /**
     * Constructor
     *
     * @param mixed           $attributes
     * @param BaseElementType $elementType
     * @return Events_CriteriaModel
     */
    public function __construct($attributes, BaseElementType $elementType)
    {

        $this->_elementType = $elementType;

        parent::__construct($attributes, $this->_elementType);

    }




    /**
     * Returns an iterator for traversing over the elements.
     *
     * Required by the IteratorAggregate interface.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->find());
    }


    /**
     * Returns the element's full URL.
     *
     * @return string
     */
    public function getEurl()
    {
        if ($this->uri !== null)
        {
            $path = ($this->uri == '__home__') ? '' : $this->uri . "/" . $this->eid;
            $url = UrlHelper::getSiteUrl($path, null, null, $this->locale);

            return $url;
        }
    }




    /**
     * Returns whether an element exists at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_numeric($offset))
        {
            return ($this->nth($offset) !== null);
        }
        else
        {
            return parent::offsetExists($offset);
        }
    }




    /**
     * Returns the element at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (is_numeric($offset))
        {
            return $this->nth($offset);
        }
        else
        {
            return parent::offsetGet($offset);
        }
    }





    /**
     * Sets the element at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     * @param mixed $item
     *
     * @return null
     */
    public function offsetSet($offset, $item)
    {
        if (is_numeric($offset) && $item instanceof BaseElementModel)
        {
            $this->_matchedElementsAtOffsets[$offset] = $item;
        }
        else
        {
            return parent::offsetSet($offset, $item);
        }
    }





    /**
     * Returns the total number of elements matched by this criteria. Required by the Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->find());
    }





    /**
     * Returns all elements that match the criteria.
     *
     * @param array $attributes Any last-minute parameters that should be added.
     *
     * @return array The matched elements.
     */
    public function find($attributes = null)
    {   
        $this->setAttributes($attributes);

        //$this->_includeInTemplateCaches();

        if (!isset($this->_matchedElements))
        {
            $elements = craft()->venti_eventManage->findElements($this);
            $this->setmatchedElements($elements);
        }
        return $this->_matchedElements;
    }





    /**
     * Returns an element at a specific offset.
     *
     * @param int $offset The offset.
     *
     * @return Events_OutputModel|null The element, if there is one.
     */
    public function nth($offset)
    {
        if (!isset($this->_matchedElementsAtOffsets) || !array_key_exists($offset, $this->_matchedElementsAtOffsets))
        {
            $criteria = new Venti_CriteriaModel($this->getAttributes(), $this->_elementType);
            $criteria->offset = $offset;
            $criteria->limit = 1;

            $elements = $criteria->find();

            if ($elements)
            {
                $this->_matchedElementsAtOffsets[$offset] = $elements[0];
            }
            else
            {
                $this->_matchedElementsAtOffsets[$offset] = null;
            }
        }
        return $this->_matchedElementsAtOffsets[$offset];
    }





    /**
     * Returns all element IDs that match the criteria.
     *
     * @param array|null $attributes
     *
     * @return array
     */
    public function ids($attributes = null)
    {
        $this->setAttributes($attributes);

        //$this->_includeInTemplateCaches();

        if (!isset($this->_cachedIds))
        {
            foreach($this->find() as $row)
            {
                $this->_cachedIds[] = $row->id;
            }
        }
        return $this->_cachedIds;
    }





    /**
     * Returns the total elements that match the criteria.
     *
     * @param array|null $attributes
     *
     * @return int
     */
    public function total($attributes = null)
    {
        $this->setAttributes($attributes);

        //$this->_includeInTemplateCaches();

        if (!isset($this->_cachedTotal))
        {
            $this->_cachedTotal = count($this->find());
        }

        return $this->_cachedTotal;
    }





    /**
     * Returns the first element that matches the criteria.
     *
     * @param array|null $attributes
     *
     * @return BaseElementModel|null
     */

    public function first($attributes = null)
    {
        $this->setAttributes($attributes);
        return $this->nth(0);
    }





    /**
     * Returns the last element that matches the criteria.
     *
     * @param array|null $attributes
     *
     * @return BaseElementModel|null
     */
    public function last($attributes = null)
    {
        $this->setAttributes($attributes);
        $total = $this->total();
        if ($total)
        {
            return $this->nth($total-1);
        }
    }





    /**
     * Returns a copy of this model.
     *
     * @return BaseModel
     */
    public function copy()
    {
        $class = get_class($this);
        $copy = new $class($this->getAttributes(), $this->_elementType);

        if ($this->_matchedElements !== null)
        {
            $copy->setMatchedElements($this->_matchedElements);
        }

        return $copy;
    }




    /**
     * @inheritDoc BaseElementModel::getElementType()
     *
     * @return BaseElementType
     */
    public function getElementType()
    {
        return $this->_elementType;
    }




    /**
     * Sets an attribute's value.
     *
     * In addition, will clears the cached values when a new attribute is set.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return bool
     */
    public function setAttribute($name, $value)
    {
        if (in_array($name, $this->attributeNames()) && $this->getAttribute($name) === $value)
        {
            return true;
        }

        if (parent::setAttribute($name, $value))
        {
            $this->_matchedElements = null;
            $this->_matchedElementsAtOffsets = null;
            $this->_cachedIds = null;
            $this->_cachedTotal = null;

            return true;
        }
        else
        {
            return false;
        }
    }





    /**
     * Stores the matched elements to avoid redundant DB queries.
     *
     * @param array $elements The matched elements.
     *
     * @return null
     */
    public function setmatchedElements($elements)
    {
        $this->_matchedElements = $elements;
        // Store them by offset, too
        $offset = $this->offset;
        foreach ($this->_matchedElements as $element)
        {
            $this->_matchedElementsAtOffsets[$offset] = $element;
            $offset++;
        }
    }





    /**
     * language => locale
     *
     * @param $locale
     *
     * @return ElementCriteriaModel
     */
    public function setLanguage($locale)
    {
        $this->setAttribute('locale', $locale);

        return $this;
    }





    /**
     * @inheritDoc BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'            => AttributeType::Number,
            'eid'           => AttributeType::Number,
            'eventid'       => AttributeType::Number,
            'startDate'     => AttributeType::Mixed,
            'endDate'       => AttributeType::Mixed,
            'allDay'        => AttributeType::Number,
            'repeat'        => AttributeType::Number,
            'rRule'         => AttributeType::String,
            'summary'       => AttributeType::String,
            'isrepeat'      => AttributeType::Number,
            'limit'         => array('default' => 1000, AttributeType::Number),
            'order'         => array(AttributeType::String, 'default' => 'venti.startDate asc')

        ));
    }





    /**
     * @return null
     */
    private function _includeInTemplateCaches()
    {
        $cacheService = craft()->getComponent('templateCache', false);

        if ($cacheService)
        {
            $cacheService->includeCriteriaInTemplateCaches($this);
        }
    }

}