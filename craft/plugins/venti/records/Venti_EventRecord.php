<?php
namespace Craft;

class Venti_EventRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'venti_events';
    }

    public function primaryKey()
    {
       return 'eid';
    }

    public function defineAttributes()
    {
        return array(
            'eid'           => array(AttributeType::Number, 'unique' => true, 'column' => ColumnType::PK),
            'eventid'       => array(AttributeType::Number, 'unique' => false),
            'startDate'     => AttributeType::DateTime,
            'endDate'       => AttributeType::DateTime,
            'allDay'        => AttributeType::Number,
            'repeat'        => AttributeType::Number,
            'rRule'         => AttributeType::String,
            'summary'       => AttributeType::String,
            'isrepeat'      => AttributeType::Number,
            'locale'        => AttributeType::String
        );
    }


    // /**
    //  * @inheritDoc BaseRecord::defineIndexes()
    //  *
    //  * @return array
    //  */
    // public function defineIndexes()
    // {
    //     return array(
    //         array('columns' => array('eid')),
    //         array('columns' => array('eventid')),
    //     );
    // }


    public function defineRelations()
    {
        return array(
            'element'  => array(static::BELONGS_TO, 'ElementRecord', 'eventid', 'required' => true, 'onDelete' => static::CASCADE),
        );
    }


    public function create()
    {
        $class = get_class($this);
        $record = new $class();
        return $record;
    }
}