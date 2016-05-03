<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160203_055928_venti_VentiEventLocalColumnAddition extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {

        // Step 1 :: Add locale column to venti_event table
        if (!craft()->db->columnExists('venti_events','locale')) 
        {
            $this->addColumnAfter('venti_events','locale', array(ColumnType::Char, 'default' => craft()->language), 'summary');
            VentiPlugin::log("Locale column in Venti_Events table created", LogLevel::Info, true);
        }
        else
        {
            VentiPlugin::log("Locale column already exists in Venti_Events table", LogLevel::Info, true);
        }        

        return true;

    }
}