<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160204_055929_venti_add_locale_field extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    
    public function safeUp()
    {

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