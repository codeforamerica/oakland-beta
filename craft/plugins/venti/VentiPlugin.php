<?php

namespace Craft;

class VentiPlugin extends BasePlugin
{
    public function init()
    {
        require_once(CRAFT_PLUGINS_PATH.'venti/vendor/tm/CalendarVariable.php');
        require_once(CRAFT_PLUGINS_PATH.'venti/vendor/tm/Venti_Calendar.php');
    }
    public function getName()
    {
        return Craft::t('Venti');
    }

    public function getVersion()
    {
        return '1.2.15';
    }

    public function getSchemaVersion()
    {
        return '1.2.3';
    }

    public function getDescription()
    {
        return 'A powerful event plugin for Craft CMS.';
    }

    public function getDocumentationUrl()
    {
        return 'http://tippingmedia.com/craft/documentation.html';
    }

    public function getDeveloper()
    {
        return 'Tipping Media';
    }

    public function getDeveloperUrl()
    {
        return 'http://tippingmedia.com';
    }

    public function getIconPath()
    {
        return craft()->path->getPluginsPath().'/resources/img/venti.svg';
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.venti.twigextensions.VentiTwigExtension');
        Craft::import('plugins.venti.twigextensions.Calendar_TokenParser');
        Craft::import('plugins.venti.twigextensions.Calendar_Node');

        return new VentiTwigExtension();
    }

    public function getSettingsHtml() {
        return craft()->templates->render('venti/settings');
    }

    public function defineAdditionalEntryTableAttributes()
    {
        return array(
            'ventiStartDate' => "Start Date",
            'ventiRepeat' => "Repeat",
            'ventiAllDay' => "All Day",
            'ventiEndDate' => "End Date",
            'ventiRepeatSummary' => "Repeat Summary"
        );
    }

    public function getEntryTableAttributeHtml(EntryModel $entry, $attribute)
    {

        // If custom field, get field handle
        if (strncmp($attribute, 'field:', 6) === 0)
        {
            $fieldId = substr($attribute, 6);
            $field = craft()->fields->getFieldById($fieldId);
            $attribute = $field->handle;
        }

        $attributes = $entry->getAttributes();
        if($fieldData = craft()->venti_eventManage->getEventFieldData($attributes['id'], $attributes['locale']))
        {

            switch ($attribute) {
                case 'ventiStartDate':
                    return $fieldData['startDate']->format('M d, Y');
                    break;
                case 'ventiEndDate':
                    return $fieldData['endDate']->format('M d, Y');
                    break;
                case 'ventiRepeat':
                    return $fieldData['repeat'];
                    break;
                case 'ventiAllDay':
                    return $fieldData['allDay'];
                    break;
                case 'ventiRepeatSummary':
                    return $fieldData['summary'];
                    break;
            }
        }
        else
        {
            // throw new Exception(Craft::t('Event with id “{id}” in locale “{locale}”. Try resaving events.', array('id' => $attributes['id'], 'locale' => $attributes['locale'])));
            VentiPlugin::log("Entry table attributes can't be found for Venti attributes.", LogLevel::Error, true);
        }
    }


}
