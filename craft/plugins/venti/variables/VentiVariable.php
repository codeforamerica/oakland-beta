<?php

namespace Craft;

/**
 * Venti Variable provides access to database objects from templates
 */
class VentiVariable
{

    /**
     * Return all events based on criteria
     *
     * @param  array $criteria
     * @return Venti_CriteriaModel
     */
    public function allEvents($criteria = null)
    {
        //return craft()->venti_eventManage->getCriteria($criteria);
        return craft()->elements->getCriteria('Venti_Event', $criteria);
    }


    function events($criteria = null)
    {
        return craft()->elements->getCriteria('Venti_Event', $criteria);
    }


    /**
     * Get a specific event. If no event is found, returns null
     *
     * @param  int $id
     * @return mixed
     */
    public function getEvent($criteria = null)
    {
        return craft()->venti_eventManage->getEvent($criteria);
    }

    /**
     * Get a specific event. If no event is found, returns null
     *
     * @param  int $id
     * @return mixed
     */
    public function getEventById($eventId, $localeId = null)
    {
        return craft()->venti_eventManage->getEventById($eventId, $localeId);
    }

    /**
     * Get next event. 
     *
     * @param  int $id
     * @return mixed
     */
    public function nextEvent($criteria = null)
    {
        return craft()->venti_eventManage->nextEvent($criteria);
    }

    /**
     * Get calendar output. 
     *
     * @param  array events, number month, number year
     * @return html
     */
    public function getCal($events, $month, $year)
    {
        if ($events) {
            return craft()->venti_calendar->getCalendar($events, $month, $year);
        }
    }

}