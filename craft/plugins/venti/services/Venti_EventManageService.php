<?php

/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2015, TippingMedia
 */


namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'venti/vendor/autoload.php');

class Venti_EventManageService extends BaseApplicationComponent
{

    /**
     * Variables
     *
     */

    protected $eventRecord;
    protected $ruleHolder;
    protected $_placeholderElements;




    /**
     * Constructor
     *
     */

    public function __construct($eventRecord = null)
    {
        $this->eventRecord = $eventRecord;
        if (is_null($this->eventRecord))
        {
                $this->eventRecord = Venti_EventRecord::model();
        }
    }




    /**
     * Save rule options to ruleHolder model
     *
     * @return  bool
     */

    public function saveRuleOptions(Venti_RuleModel $model)
    {
        if($model->dates = $this->getRecurDates($model->getAttribute('startDate'), $model->getAttribute('rrule')))
        {
            $this->ruleHolder = $model;
            return true;
        }
        else
        {
            throw new Exception(Craft::t('Recurr array not created."'));
        }
    }




    public function getCriteria($attributes = null)
    {

        //Sets ElementType for plugin
        $elementType = $this->getElementType('Venti_Event');

        if (!$elementType)
        {
            throw new Exception(Craft::t('No element type exists by the type “{type}”.', array('type' => $type)));
        }

        return new ElementCriteriaModel($attributes, $elementType);
    }




    /**
     * Get multiple events
     *
     * @return  array
     */
    public function allEvents($attributes = null)
    {
        $criteria = $this->getCriteria($attributes);
        return $criteria;
    }




    /**
     * Get specific event by its eventid
     *
     * @return  array
     */
    public function getEventById($eventId, $localeId = null)
    {
        if (!$eventId)
        {
            return null;
        }
        else
        {
            $criteria = $this->getCriteria(array("id" => $eventId));
            $criteria->locale = $localeId;
        }

        return $criteria->first();
    }




    /**
     * Get specific event by its eid
     *
     * @return  array
     */
    public function getEvent($eid, $localeId = null)
    {
        if (!$eid)
        {
            return null;
        }
        else
        {
            $criteria = $this->getCriteria(array("eid" => $eid));
            //$criteria->locale = $localeId;
        }

        return $criteria->first();
    }




    /**
     * Get field type data based on eventid
     *
     * @return  array
     */
    public function getEventFieldData($id, $localeId = null)
    {
        
        $event = array();
        $eventRecord = Venti_EventRecord::model();
        if ($record = $this->eventRecord->findByAttributes(array("eventid" => $id, "locale" => $localeId))) 
        {

             $event  = array(
                'startDate'   => $record->startDate,
                'endDate'     => $record->endDate,
                'allDay'      => (bool) $record->allDay,
                'repeat'      => (bool) $record->repeat,
                'summary'     => $record->summary,
                'rRule'       => $record->rRule,
                'locale'      => $record->locale,
                );

             return $event;
        }
        else
        {
            //craft()->userSession->setError(Craft::t("Event with id (". $id .") can't be found for event field data."));
            VentiPlugin::log("Venti_EventManageService::getEventFieldData – can't find event by id ". $id . ".", LogLevel::Error, true);
        }
    }




    /**
     * Get next event.
     * if recurring returns next possible date
     *
     * @return  array
     */
    public function nextEvent($eventId = null, $localeId = null)
    {
        $now = new DateTime('now', new \DateTimeZone(craft()->getTimeZone()));
        if ($eventId == null)
        {
            $criteria = $this->getCriteria(
                array(
                    "startDate" => array('>'.$now),
                    "locale" => $localeId
                )
            );
        }
        else
        {
            $criteria = $this->getCriteria(
                array(
                    "startDate" => array('and','>'.$now),
                    "id" => $eventId,
                    "locale" => $localeId
                )
            );
        }
        return $criteria->first();
    }


    /**
     * Grabs subset of events array based on offset and number of items.
     * - Experimental
     * @return  array
     */

    protected function pagination($events,$filterAttributes)
    {

        $offset = intval($filterAttributes['page']['offset']);
        $howmany = intval($filterAttributes['page']['howmany']);
        $total = $offset + $howmany;

        $output = array();
        $i = 0;
        foreach ($events as $event)
        {
            if( $i >= $offset && $i < $total )
            {
                array_push($output,$event);
            }
            $i++;
        }

        return $output;
    }




    /**
     * Generates End Date from difference in time of original Start & End Dates
     * Repeat dates need endDate but same time as startDate
     *
     * @return  DateTime
     */

     private function sameDateNewTime(\DateTime $date1, \DateTime  $date2, \DateInterval $difr)
     {

        $newDate = new \DateTime($date2->format('c'));
        $newDate->setTimezone(new \DateTimeZone("UTC"));
        $newDate1 = $newDate->add($difr);

        return $newDate1;
     }



    /**
     * Get dates array based on recur template.
     *
     * @return  array
     */

    public function getRecurDates($start, $rrule)
    {

        $timezone = craft()->getTimeZone(); //'UTC','America/New_York','America/Denver' craft()->getTimeZone()
        $startDateString = $start->format(DateTime::MYSQL_DATETIME);

        #-- returns null or datetime
        $endOn = craft()->venti_rule->getEndOn($rrule);
        $rule = new \Recurr\Rule($rrule, $startDateString, $endOn, $timezone);
        $transformer = new \Recurr\Transformer\ArrayTransformer();
        $transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        // if ($endOn !== null) 
        // {
        //     $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($start, $endOn, true);
        // }
        // else
        // {
        //     $constraint = new \Recurr\Transformer\Constraint\AfterConstraint(new \DateTime(), true);
        // }
        

        return $transformer->transform($rule);
    }



    /**
     * Saving event data to eventRecord
     *
     * @return  bool
     */

    public function saveEventData(BaseElementModel $element, $attributes)
    {

        $model = new Venti_EventModel();
        $model->eventid = $element->id;
        $model->locale  = $element->locale;
        $model->setAttributes($attributes);
        $model->startDate = DateTime::createFromString($attributes['startDate']['date'], craft()->getTimeZone());
        $model->endDate = DateTime::createFromString($attributes['endDate']['date'], craft()->getTimeZone());
        $timezone = craft()->getTimeZone();

        #   
        # Validate's the model
        if ($model->validate())
        {
            #
            # If there is no current event record at specific id create new record
            if (null === ($record = $this->eventRecord->findByAttributes(array("eventid" => $element->id, "locale" => $element->locale))))
            {
                $record = $this->eventRecord->create();
            }

            #
            # Map model attributes to event record attributes
            $record->setAttributes($model->getAttributes(),false);

            if($record->save())
            {

                $model->setAttribute('eventid', $record->getAttribute('eventid'));

                #
                # If repeat checkbox is selected (exists in attributes array) save recurring dates
                # if not selected see if there are dates in dateRecord associated with event id
                # if there are delete them.

                if(array_key_exists('repeat',$attributes))
                {

                    if($attributes['repeat'] == 1)
                    {

                        $this->saveRecurringDates($model);

                    }

                }
                else
                {

                    if($dates = $this->eventRecord->findByAttributes(array("eventid" => $element->id, "isrepeat" => 1, "locale" => $element->locale)))
                    {
                        $this->eventRecord->deleteAllByAttributes(array("eventid" => $element->id, "isrepeat" => 1));
                    }

                }

                #
                # Delete templates caches with this eventid/element->id
                craft()->templateCache->deleteCachesByElementId($element->id);

                return true;

            }
            else
            {

                $model->addErrors($record->getErrors());
                craft()->userSession->setError(Craft::t('Event not saved.'));
                return false;
            }

        }
        else
        {
            craft()->userSession->setError(Craft::t("Event dates can't be empty."));
            return false;
        }

    }





    /**
     * Save recurring dates to dateRecord
     *
     */

    public function saveRecurringDates($model)
    {

        $id         = $model->getAttribute('eventid');
        $locale     = $model->getAttribute('locale');
        $startdate  = $model->getAttribute('startDate');
        $enddate    = $model->getAttribute('endDate');
        $diff       = $startdate->diff($enddate);
        $rule       = $model->getAttribute('rRule');
        $dates      = $this->getRecurDates($startdate, $rule);
        $dates      = $dates->toArray();
        
        if(null === ($record = $this->eventRecord->findByAttributes(array("eventid" => $id, "isrepeat" => 1, "locale" => $locale))))
        {

            foreach ($dates as $key => $value)
            {
                
                # gets startdate from Recur\Recurrece object
                $date = $value->getStart(); //DateTime

                #
                # If original start date is equal to first start recurring start date
                # don't save the recurring date.
                if ($key == 0 && $startdate == $date) 
                {
                    continue;
                } 

                $record = $this->eventRecord->create();
                $record->setAttributes($model->getAttributes());
                $record->setAttribute('startDate', $this->formatStartDate($date));
                $record->setAttribute('endDate', $this->sameDateNewTime($model->endDate, $date, $diff));
                $record->setAttribute('isrepeat', 1);
                $record->setAttribute('summary', $model->summary);
                $record->setAttribute('rRule', $model->rRule);
                $record->setAttribute('allDay', $model->allDay);
                $record->setAttribute('locale', $model->locale);

                if(!$record->save())
                {
                    craft()->userSession->setError(Craft::t('Repeat events not saved.'));
                    $model->addErrors($record->getErrors());
                    return false;
                }
                
            }
            
        }
        else
        {
            $this->eventRecord->deleteAllByAttributes(array("eventid" => $id, "isrepeat" => 1, "locale" => $locale));
            foreach ($dates as $key => $value)
            {

                # gets startdate from Recur\Recurrece object
                $date = $value->getStart(); //DateTime

                #
                # If original start date is equal to first start recurring start date
                # don't save the recurring date.
                if ($key == 0 && $startdate == $date) 
                {
                    continue;
                } 
                $record = $this->eventRecord->create();
                $record->setAttributes($model->getAttributes());
                $record->setAttribute('eventid', $id);
                $record->setAttribute('startDate', $this->formatStartDate($date));
                $record->setAttribute('endDate', $this->sameDateNewTime($model->endDate, $date, $diff));
                $record->setAttribute('isrepeat',1);
                $record->setAttribute('rRule', $model->rRule);
                $record->setAttribute('summary', $model->summary);
                $record->setAttribute('allDay', $model->allDay);
                $record->setAttribute('locale', $model->locale);


                if(!$record->save())
                {
                    craft()->userSession->setError(Craft::t('Repeat events not saved.'));
                    $model->addErrors($record->getErrors());
                    return false;
                }
                
            }
            
        }
    }



    /**
     * Convert date to MYSQL_DATETIME in UTC
     *
     * @return  Craft\DateTime
     */
    public function formatStartDate(\DateTime $date)
    {
        $temp = DateTimeHelper::formatTimeForDb( $date->getTimestamp() );
        return  DateTime::createFromFormat( DateTime::MYSQL_DATETIME, $temp );
    }



    /**
     * EventsTwigExtension method for grouping by date
     *
     * @return  array
     */

    public function groupByDate($arr, $item)
    {
        $groups = array();

        foreach ($arr as $key => $object)
        {
            $value = $this->getDateFormat($item, $object);
            $groups[$value][] = $object;
        }

        return $groups;
    }




    /**
     * Get date format for groupByDate grouping
     *
     * @return  string
     */

    protected function getDateFormat($item, $object)
    {
        $sdate = $object['startDate'];

        #
        # Group by day of month and year
        if($item === 'day')
        {
            return $sdate->format('M d Y');
        }

        #
        # Group by Month of year
        if($item === 'month')
        {
            return $sdate->format('F Y');
        }

        #
        # Group by year
        if($item === 'year')
        {
            return $sdate->format('Y');
        }

        #
        # Month Day Year Hour
        if($item === 'time')
        {
            return $sdate->format('M d Y g');
        }

        #
        # Group by weekday
        if($item=== 'weekday')
        {
            return $sdate->format('l');
        }

        #
        # Group by weekday name and hour
        if($item === 'weekdayhour')
        {
            return $sdate->format('l g');
        }
    }


    /**
     * Returns an element type by its class handle.
     *
     * @param string $class The element type class handle.
     *
     * @return IElementType|null The element type, or `null`.
     */
    public function getElementType($class)
    {
        return craft()->components->getComponentByTypeAndClass(ComponentType::Element, $class);
    }



    /**
       *
       * @param $recurRule recurrence rule - FREQ=YEARLY;INTERVAL=2;COUNT=3;
       * @return string recurrence string - every year for 3 times
       */
    public function recurTextTransform($recurRule, $lang = null)
    {
        //- Recurr's supported locales
        $locales = ['de','en','eu','fr','it','sv','es'];

        $locale = in_array(craft()->language, $locales) ? craft()->language : "en";
        if ($lang != null && in_array($lang, $locales))
        {
            $locale = $lang;
        }

        $rule = new \Recurr\Rule($recurRule, new \DateTime());

        $textTransformer = new \Recurr\Transformer\TextTransformer(
            new \Recurr\Transformer\Translator($locale)
        );

        return $textTransformer->transform($rule);
    }
}
