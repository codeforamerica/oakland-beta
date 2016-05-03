<?php
namespace Craft;

/**
 * Venti - Event element type
 */
class Venti_EventElementType extends BaseElementType
{
    /**
     * Returns the element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Events');
    }

    /**
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public function hasContent()
    {
        return false;
    }

    /**
     * Returns whether this element type has titles.
     *
     * @return bool
     */
    public function hasTitles()
    {
        return false;
    }



    public function isLocalized()
    {
         return true;
    }


    /**
    * @inheritDoc IElementType::hasStatuses()
    *
    * @return bool
    */
    public function hasStatuses()
    {
        return true;
    }

    /**
     * @inheritDoc IElementType::getStatuses()
     *
     * @return array|null
     */
    public function getStatuses()
    {
        return array(
            EntryModel::LIVE => Craft::t('Live'),
            EntryModel::PENDING => Craft::t('Pending'),
            EntryModel::EXPIRED => Craft::t('Expired'),
            BaseElementModel::DISABLED => Craft::t('Disabled')
        );
    }


    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'eid'           => AttributeType::Mixed,
            'eventid'       => AttributeType::Mixed,
            'startDate'     => AttributeType::Mixed,
            'endDate'       => AttributeType::Mixed,
            'rRule'         => AttributeType::Mixed,
            'repeat'        => AttributeType::Mixed,
            'allDay'        => AttributeType::Mixed,
            'summary'       => AttributeType::Mixed,
            'isrepeat'      => AttributeType::Mixed,
            'locale'		=> AttributeType::Mixed,
            'order'         => array(AttributeType::String, 'default' => 'venti.startDate asc'),
            'between'       => AttributeType::Mixed,
            'expiryDate'      => AttributeType::Mixed,
            'postDate'        => AttributeType::Mixed,
            'status'          => array(AttributeType::String, 'default' => EntryModel::LIVE),

        );
    }

    /**
     * Modifies an element query targeting elements of this type.
     *
     * @param DbCommand $query
     * @param ElementCriteriaModel $criteria
     * @return mixed
     */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
        $query
            ->addSelect('venti.startDate, venti.endDate, venti.allDay, venti.isrepeat, venti.eid, venti.eventid, venti.repeat, venti.rRule, venti.summary, venti.locale, entries.postDate, entries.expiryDate')
            ->leftJoin('venti_events venti', 'venti.eventid = elements.id')
            ->leftJoin('entries entries', 'entries.id = eventid')
            ->group('');


        if ($criteria->locale) 
        {
            $query->andWhere(DbHelper::parseParam('venti.locale', $criteria->locale, $query->params));
        }

        if ($criteria->startDate)
        {
            $query->andWhere(DbHelper::parseDateParam('venti.startDate', $criteria->startDate, $query->params));
        }

        if ($criteria->id)
        {
            $query->andWhere(DbHelper::parseDateParam('venti.eventid', $criteria->eventid, $query->params));
        }

        if ($criteria->endDate)
        {
            $query->andWhere(DbHelper::parseDateParam('venti.endDate', $criteria->endDate, $query->params));
        }

        if($criteria->summary)
        {
            $query->andWhere(DbHelper::parseParam('venti.summary', $criteria->summary, $query->params));
        }

        if($criteria->isrepeat)
        {
            $query->andWhere(DbHelper::parseParam('venti.isrepeat', $criteria->isrepeat, $query->params));
        }

        if($criteria->rRule)
        {
            $query->andWhere(DbHelper::parseParam('venti.rRule', $criteria->rRule, $query->params));
        }

        if($criteria->eid)
        {
            $query->andWhere(DbHelper::parseParam('venti.eid', $criteria->eid, $query->params));
        }

        if($criteria->repeat)
        {
            $query->andWhere(DbHelper::parseParam('venti.repeat', $criteria->repeat, $query->params));
        }

        if($criteria->allDay)
        {
            $query->andWhere(DbHelper::parseDateParam('venti.allDay', $criteria->allDay, $query->params));
        }

        if($criteria->between)
        {
        	$dates = array();
        	$interval = array();

        	if(!is_array($criteria->between))
        	{
        		$criteria->between = ArrayHelper::stringToArray($criteria->between);
        	}

        	if (count($criteria->between) == 2) 
        	{
        		foreach ($criteria->between as $ref) 
        		{
        			if (!$ref instanceof \DateTime)
					{
						$dates[] = DateTime::createFromString($ref, craft()->getTimeZone());
					}
					else
					{
						$dates[] = $ref;
					}
        		}

        		if ($dates[0] > $dates[1]) 
        		{
        			$interval[0] = $dates[1];
        			$interval[1] = $dates[0];
        		}
        		else
        		{
        			$interval = $dates;
        		}

        		$query->andWhere('(venti.startDate BETWEEN :betweenStartDate AND :betweenEndDate) OR (:betweenStartDate BETWEEN venti.startDate AND venti.endDate)',
        			array(
        				':betweenStartDate'   => DateTimeHelper::formatTimeForDb($interval[0]->getTimestamp()), 
        				':betweenEndDate'     => DateTimeHelper::formatTimeForDb($interval[1]->getTimestamp())
        				)
        		);
        	}        	
        }

    }


    /**
     * Populates an element model based on a query result.
     *
     * @param array $row
     * @return BaseElementModel|BaseModel|void
     */
    public function populateElementModel($row)
    {
        $row['type'] = "Venti_Event";
        //\CVarDumper::dump($row, 5, true);exit;
        
        return Venti_OutputModel::populateModel($row);
    }


    /**
     * @inheritDoc IElementType::getElementQueryStatusCondition()
     *
     * @param DbCommand $query
     * @param string    $status
     *
     * @return array|false|string|void
     */
    public function getElementQueryStatusCondition(DbCommand $query, $status)
    {
        $currentTimeDb = DateTimeHelper::currentTimeForDb();

        switch ($status)
        {
            case EntryModel::LIVE:
            {
                return array('and',
                    'elements.enabled = 1',
                    'elements_i18n.enabled = 1',
                    "entries.postDate <= '{$currentTimeDb}'",
                    array('or', 'entries.expiryDate is null', "entries.expiryDate > '{$currentTimeDb}'")
                );
            }

            case EntryModel::PENDING:
            {
                return array('and',
                    'elements.enabled = 1',
                    'elements_i18n.enabled = 1',
                    "entries.postDate > '{$currentTimeDb}'"
                );
            }

            case EntryModel::EXPIRED:
            {
                return array('and',
                    'elements.enabled = 1',
                    'elements_i18n.enabled = 1',
                    'entries.expiryDate is not null',
                    "entries.expiryDate <= '{$currentTimeDb}'"
                );
            }
        }
    }

}
