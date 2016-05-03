<?php
namespace Craft;

/**
 * Resave Venti Events task
 */
class Venti_ResaveEventsTask extends BaseTask
{

    /**
	 * @var
	 */
	private $_elementIds;


    private $_elements;


	/**
	 * Defines the settings.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
        return array(
			'elementIds' => AttributeType::Mixed,
		);
	}

	/**
	 * Returns the default description for this task.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return Craft::t('Resaving Venti Recurring Events');
	}

	/**
	 * Gets the total number of steps for this task.
	 *
	 * @return int
	 */
	public function getTotalSteps()
	{
        $settings = $this->getSettings();

 	    $this->_elementIds = $settings->elementIds;
        $criteria = array(
            "id" => $this->_elementIds,
            'limit' => null,
            'status' => null,
            'localeEnabled' => null
        );

        $this->_elements = craft()->elements->findElements($criteria);
        return count($this->_elementIds);
	}

	/**
	 * Runs a task step.
	 *
	 * @param int $step
	 * @return bool
	 */
	public function runStep($step)
	{
        try {

            $element = $this->_elements[$step];

            $query = craft()->db->createCommand()
                     ->select('venti.eventid,venti.startDate,venti.endDate,venti.allDay,venti.repeat,venti.rRule,venti.summary,venti.isrepeat')
                     ->from('venti_events venti');

            $query->andWhere(array("venti.eventid"=> $element->id));
            $query->andWhere("venti.isrepeat is NULL");

            $attributes = $query->queryAll();

            $attributes[0]['startDate'] = DateTime::createFromString($attributes[0]['startDate'],null,true);
            $attributes[0]['endDate'] = DateTime::createFromString($attributes[0]['endDate'],null,true);

            if (craft()->venti_eventManage->saveEventData($element, $attributes[0])) {
                return true;
            }else{
                VentiPlugin::log('Recurring event with ID ' . $element->id ." was not saved.", LogLevel::Error);
                return 'Recurring event with ID ' . $element->id ." was not saved.";
            }

        } catch (Exception $e) {
            VentiPlugin::log('An exception was thrown while trying to save the recurring event with the ID “'.$this->_elementIds[$step].'”: '.$e->getMessage(), LogLevel::Error);
            return 'An exception was thrown while trying to save the recurring event with the ID “'.$this->_elementIds[$step].'”: '.$e->getMessage();

        }
	}
}
