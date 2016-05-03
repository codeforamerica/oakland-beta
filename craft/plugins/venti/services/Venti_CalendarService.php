<?php

/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2015, TippingMedia
 */


namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'venti/vendor/tm/calendar.class.php');

class Venti_CalendarService extends BaseApplicationComponent
{

    public function getCalendar($events,$month,$year)
    {
        $cal = new \Calendar($month,$year);
        return $cal->createCalendar($events,craft()->getTimeZone());
    }

}