<?php
namespace Craft;

class Venti_OutputModel extends BaseElementModel
{

    const LIVE     = 'live';
    const PENDING  = 'pending';
    const EXPIRED  = 'expired';

    /**
     * @var string
     */
    protected $elementType = 'Venti_Event';



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
     * @inheritDoc BaseElementModel::getStatus()
     *
     * @return string|null
     */
    public function getStatus()
    {
        $status = parent::getStatus();

        if ($status == static::ENABLED && $this->postDate)
        {
            $currentTime = DateTimeHelper::currentTimeStamp();
            $postDate    = $this->postDate->getTimestamp();
            $expiryDate  = ($this->expiryDate ? $this->expiryDate->getTimestamp() : null);

            if ($postDate <= $currentTime && (!$expiryDate || $expiryDate > $currentTime))
            {
                return static::LIVE;
            }
            else if ($postDate > $currentTime)
            {
                return static::PENDING;
            }
            else
            {
                return static::EXPIRED;
            }
        }

        return $status;
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
            'postDate'      => AttributeType::DateTime,
            'expiryDate'    => AttributeType::DateTime,
        ));
    }

}