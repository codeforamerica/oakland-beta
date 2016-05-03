<?PHP
/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2014, TippingMedia
 */

namespace Craft;


class Venti_AjaxController extends BaseController
{


    /*
     * Render repeat date modal from ajax call.
     */
    public function actionModal()
    {
        $this->requireAjaxRequest();

        $defaultValues = [
            "frequency" => 0,
            'by' => ['0'],
            'endsOn' => ['0'],
            'on'  => [],
            'starts' => '',
            'enddate' => '',
            'occur' => '',
            'every' => '',
            'exclude' => []
        ];

        $rule = craft()->request->getPost("rrule");
        $locale = craft()->request->getPost("locale");
        $values = $rule != "" ?  craft()->venti_rule->modalValuesArray($rule) : $defaultValues;
        $this->renderTemplate('venti/_modal',array('values' => $values, 'locale' => $locale),false);
    }

    /*
     *
     *
     */
    public function actionRecurTextTransform()
    {
        $this->requireAjaxRequest();
        $rule = craft()->request->getPost('rule');
        $lang = craft()->request->getPost('lang') ? craft()->request->getPost('lang') : null;
        $text = craft()->venti_eventManage->recurTextTransform($rule, $lang);
        $this->returnJson($text);
    }


    public function actionGetRuleString()
    {
        $this->requireAjaxRequest();
        $post = craft()->request->getPost();
        $repeat = reset($post)['repeat'];
        $locale = array_key_exists('locale', $repeat) ? $repeat['locale'] : craft()->locale;
        $localeData = craft()->i18n->getLocaleData();
        $lang = $localeData->getLanguageId($locale);
        $ruleString = craft()->venti_rule->getRRule($repeat);
        $ruleHumanString = craft()->venti_rule->recurTextTransform($ruleString, $lang);
        $output = [
            "rrule" => $ruleString,
            "readable" => $ruleHumanString
        ];
        $this->returnJson($output);
    }

}
?>
