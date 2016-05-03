<?php
namespace Craft;

use \Twig_Extension;
use \Twig_Filter_Method;

class VentiTwigExtension extends \Twig_Extension
{
  protected $env;
  
  public function getName()
  {
    return 'VentiTwig';
  }

  public function initRuntime(\Twig_Environment $env)
  {
      $this->env = $env;
  }

  /**
   * Returns the token parser instances to add to the existing list.
   *
   * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
   */
  public function getTokenParsers()
  {
    return array(
      new Calendar_TokenParser(),
    );
  }

  public function getFilters()
  {
    return array(
      'groupByDate' => new \Twig_Filter_Method($this, 'events'),
      'strToDate'   => new \Twig_Filter_Method($this, 'strtodate')
    );
  }

  public function getFunctions()
  {
    return array(
        'groupByDate' => new \Twig_Function_Method($this, 'events'),
        'strToDate'      => new \Twig_Function_Method($this, 'strtodate')
    );
  }

  public function events(array $source=array(), $params)
  {
    return craft()->venti_eventManage->groupByDate($source, $params);
  }

  function strtodate($str, $format)
  {
      return DateTime::createFromFormat($format, $str);
  }
}