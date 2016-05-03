<?php
namespace Craft;

class Venti_RuleModel extends BaseModel
{
    protected function defineAttributes()
    {
        return array(
            'id'              => AttributeType::Number,
            'rrule'           => AttributeType::String,
            'startDate'       => AttributeType::DateTime,
            'dates'           => AttributeType::Mixed
        );
    }
}