<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160204_055929_venti_VentiEventFieldColumnAdditions extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {


        // Step 1 :: Add Venti field(s) to content table
        $rows = craft()->db->createCommand()
                ->select('fields.id, fields.handle, fields.context, fields.type')
                ->from('fields fields')
                ->where(['fields.type' => 'Venti_Event'])
                ->queryAll();

        for ($i=0; $i < count($rows); $i++) 
        { 
            if ($rows[$i]['context'] == 'global') 
            {
                $fieldHandle = 'field_' . $rows[$i]['handle'];
                if (!craft()->db->columnExists('content', $fieldHandle)) 
                {
                    $this->addColumn('content', $fieldHandle, array(ColumnType::Text));
                    VentiPlugin::log($fieldHandle . " column in content table created", LogLevel::Info, true);
                }
                else
                {
                    VentiPlugin::log($fieldHandle . " column already exists in the Content table", LogLevel::Info, true);
                }
            }
        }


        // Step 2 :: Add field date to newly created Venti field(s)
        craft()->templateCache->deleteCachesByElementType('Entry');
        
        //Get unique list of element ids from venti events
        $ids = craft()->db->createCommand()
                ->select('venti.eventid')
                ->from('venti_events venti')
                ->queryAll();

        $flatIds = [];

        foreach ($ids as $key => $value) {
            array_push($flatIds, $value['eventid']);
        }
        
        $entryIds = array_unique($flatIds);
        /*craft()->sections->getAllSections()
        
        {% for section in sections %}
            {% if section.getEntryTypes()[0].getFieldLayout().getFields()[0].getField().handle == 'specificfield' %}
                do something
            {% endif %}
        {% endfor %}*/

        foreach ($entryIds as $entryId)
        {

            $entry = craft()->entries->getEntryById($entryId);

            if (!$entry)
            {
                continue;
            }

            $event = craft()->db->createCommand()
                ->select('venti.eid, venti.eventid, venti.startDate, venti.endDate, venti.summary, venti.rRule, venti.repeat, venti.allDay, venti.isrepeat ')
                ->from('venti_events venti')
                ->where('venti.eventid = :eventid', array(':eventid' => $entryId))
                ->andWhere('venti.isrepeat IS NULL')
                ->queryAll();
            //VentiPlugin::log("Entry ID: " . $entryId . " | Event ID: " . $event[0]['eventid']." | Event Count: " . count($event), LogLevel::Info);    
            $fieldData  = array(
                'startDate'   => $event[0]['startDate'],
                'endDate'     => $event[0]['endDate'],
                'allDay'      => $event[0]['allDay'],
                'repeat'      => $event[0]['repeat'],
                'summary'     => $event[0]['summary'],
                'rRule'       => $event[0]['rRule']
            );

            $fields = $entry->fieldLayout->fields;

            foreach ($fields as $field) 
            { 
                if($field->getField()->type == 'Venti_Event')
                {
                    $fieldHandle = $field->getField()->handle;
                    //Update new field with data
                    VentiPlugin::log($fieldHandle, LogLevel::Info);
                    $entry->setContentFromPost(array(
                        $fieldHandle => $fieldData
                    ));
                }
            }



            // Save it
            $success = craft()->entries->saveEntry($entry);

            if (!$success)
            {
                // Log the error in this plugin's log file (in craft/storage/runtime/logs/fooplugin.log)
                $error = 'Encountered the following validation errors when trying to save entry # "'.$entry->id.'":';

                foreach ($entry->getAllErrors() as $attributeError)
                {
                    $error .= "\n - {$attributeError}";
                }

                VentiPlugin::log($error, LogLevel::Error);
            }
        }
        
        

        return true;

    }
}