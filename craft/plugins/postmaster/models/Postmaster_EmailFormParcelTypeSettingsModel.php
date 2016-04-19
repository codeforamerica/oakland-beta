<?php
namespace Craft;

class Postmaster_EmailFormParcelTypeSettingsModel extends Postmaster_EmailModel
{
	protected function defineAttributes()
    {
    	$attributes = parent::defineAttributes();
        $attributes['extraConditionals'] = array(AttributeType::String);

        return $attributes;
    }
}