<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151104_040245_venti_ResaveVentiElements extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

        $elementIds = array_unique(craft()->venti_eventManage->allEvents()->ids());

		// Queue up a new ResaveElements task
		$task = craft()->tasks->createTask("Venti_ResaveEvents", null, array(
			"elementIds" => $elementIds
		));

		//craft()->tasks->runPendingTasks();
		craft()->tasks->runTask($task);

		return true;
	}
}
