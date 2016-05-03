<?php

/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2014, Tipping Media
 */

namespace Craft;

require(CRAFT_PLUGINS_PATH.'venti/vendor/autoload.php');

class Venti_EventFieldType extends BaseFieldType
{

    /**
     * Block type name
     */
    public function getName()
    {
        return Craft::t('Venti Events');
    }


    /**
     * Save it
     */
    public function defineContentAttribute()
    {
        //return AttributeType::String;
        return array(AttributeType::Mixed);
        
    }
        

    /**
     * Returns the field's input HTML.
     *
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    public function getInputHtml($name,$value)
    {
        $locale = $this->element->locale;
        if(is_array($value)){

            $startDate = (!empty($value['startDate']['date'])) ? DateTime::createFromString($value['startDate']['date'], craft()->getTimeZone()) : '';
            $endDate = (!empty($value['endDate']['date'])) ? DateTime::createFromString($value['endDate']['date'], craft()->getTimeZone()) : '';
            $fieldData = [
                'startDate' => $startDate,
                'endDate'   => $endDate,
                'allDay'    => isset($value['allDay'])  ? $value['allDay']  : "",
                'recur'     => isset($value['recur'])   ? $value['recur']   : "",
                'repeat'    => isset($value['repeat'])  ? $value['repeat']  : "",
                'summary'   => isset($value['summary']) ? $value['summary'] : "",
                'rRule'     => isset($value['rRule'])   ? $value['rRule']   : "",
                'locale'    => isset($value['locale'])  ? $value['locale']  : craft()->language
            ];
        }else{
            if (isset($this->element->id)){
                $fieldData = craft()->venti_eventManage->getEventFieldData($this->element->id, craft()->language);
            }else{
                $fieldData['startDate'] = new DateTime();
                $fieldData['endDate'] = new DateTime();
                $fieldData['allDay'] = "";
                $fieldData['recur'] = "";
                $fieldData['repeat'] = "";
                $fieldData['summary'] = "";
                $fieldData['rRule'] = "";
                $fieldData['locale'] = "";
            }
        }

        
        return craft()->templates->render('venti/ui', array(
            "name" => $name,
            "fieldData" => $fieldData,
            "locale" => $locale
        ));
        
    }


 /**
     * Returns the input value as it should be saved to the database.
     *
     * @param mixed $value
     * @return mixed
     */
    public function prepValueFromPost($value)
    {
        $this->_convertTimes($value);
        return json_encode($value);
    }


    public function prepValue($value)
    { 
        if ($value != null && is_array($value))
        {
            
            return $value;
        }
        else
        {
            return $value;
        }
    }

    

/**
 * onAfterElementSave
 * Sends data to saveEventDate service method to save event Times
 */
    public function onAfterElementSave(){
        $element = $this->element;
        $attributes = $this->element->getContent()->getAttributes();
        
        // If attributes don't have data don't try and save event data.
        if($attributes[$this->model->handle] !== null){
            craft()->venti_eventManage->saveEventData($element, $attributes[$this->model->handle]);
        }
    }



/**
     * Loops through the data and converts the times to DateTime objects.
     *
     * @access private
     * @param array &$value
     */
    private function _convertTimes(&$value)
    {
        if (is_array($value))
        {
            if ((is_string($value['startDate']) && $value['startDate']) || (is_array($value['startDate']) && $value['startDate']['time']))
            {
                $value['startDate'] = DateTime::createFromString($value['startDate'], craft()->getTimeZone());
            }
            else
            {
                $value['startDate'] = '';
            }

            if ((is_string($value['endDate']) && $value['endDate']) || (is_array($value['endDate']) && $value['endDate']['time']))
            {
                $value['endDate'] = DateTime::createFromString($value['endDate'], craft()->getTimeZone());
            }
            else
            {
                $value['endDate'] = '';
            }
        }
    }




    /**
     * Validates the value.
     *
     * Returns 'true' or any custom validation errors.
     *
     * @param array $value
     * @return true|string|array
     */
    public function validate($value)
    {
        $startDate = $value['startDate']; 
        $endDate = $value['endDate'];
        $repeat = isset($value['repeat']) ? $value['repeat'] : null;
        $is_error = false;

        if($startDate == ""){
            $error['startDate'] = Craft::t('Start Date and time must be set.');
            $is_error = true;
        }

        if ($repeat == 1 ) 
        {
            if($endDate != $startDate)
            {
                $error['endDate'] = Craft::t('The End Date must equal the Start Date if this is a repeat event.');
            }
        }
        elseif( $repeat == 0 )
        {
            if($endDate == ""){
                $error['endDate'] = Craft::t('End Date and time must be set.');
                $is_error = true;
            }else{
                if($endDate < $startDate){
                    $error['endDate'] = Craft::t('The End Date must be greater than or equal to the Start Date.');
                    $is_error = true;
                }
            }
        }


        if($is_error == true){
            return $error;
        }else{
            return true;
        }

    }


}
