<?php
namespace Craft;

class Venti_EventModel extends BaseModel
{

    public function __toString()
    {
        return $this->name;
    }

    protected function defineAttributes()
    {
        return array(
            'eventid'       => AttributeType::Number,
            'startDate'     => [
                                'type' => AttributeType::DateTime,
                                'required'=> true
                                ],
            'endDate'       => [
                                'type' => AttributeType::DateTime,
                                'required'=> true
                                ],
            'allDay'        => AttributeType::Number,
            'repeat'        => AttributeType::Number,
            'rRule'         => AttributeType::String,
            'summary'       => AttributeType::String,
            'isrepeat'      => AttributeType::Number,
            'locale'        => AttributeType::String
        );
    }
}