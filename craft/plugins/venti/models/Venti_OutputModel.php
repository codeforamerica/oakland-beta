<?php
namespace Craft;

class Venti_OutputModel extends BaseElementModel
{

    public function __toString()
    {
        return $this->name;
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



    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'eid'           => AttributeType::Number,
            'eventid'       => AttributeType::Number,
            'startDate'     => AttributeType::DateTime,
            'order'         => array(AttributeType::String, 'default' => 'venti.startDate asc'),
            'endDate'       => AttributeType::DateTime,
            'allDay'        => AttributeType::Number,
            'repeat'        => AttributeType::Number,
            'rRule'         => AttributeType::String,
            'summary'       => AttributeType::String,
            'isrepeat'      => AttributeType::Number,
            'locale'        => AttributeType::String,
        ));
    }

}