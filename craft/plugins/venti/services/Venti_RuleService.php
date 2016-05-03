<?php

/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2015, TippingMedia
 */


namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'venti/vendor/autoload.php');

class Venti_RuleService extends BaseApplicationComponent
{


    /**
    * Human Readable ICalendar Rule String
    * @param  array
    * @return string
    *
    * Every month on Monday, Wednesday, Friday for 30 times
    */
    public function getRuleString( Array $params )
    {

        /**
        * VARIABLES
        */
        $ruleString = [];
        $frequencyLabels = [
            'Daily',
            'Every weekday (Monday to Friday)',
            'Every Monday, Wednesday, and Friday',
            'Every Tuesday, and Thrusday',
            'Weekly',
            'Monthly',
            'Yearly'
        ];


        /**
        * POST VALUES
        */

        $frequency      = array_key_exists('frequency', $params) ? $params['frequency'] : null;
        $repeatEvery    = array_key_exists('every', $params) ? $params['every'] : null;
        $repeatOnDays   = array_key_exists('on', $params) ? $params['on'] : null;                // array
        $repeatBy       = array_key_exists('by', $params) ? $params['by'] : null;                // array
        $starts         = array_key_exists('starts', $params) ? $params['starts'] : null;
        $endsOn         = array_key_exists('endsOn', $params) ? $params['endsOn'] : null;        // array
        $endDate        = array_key_exists('enddate', $params) ? $params['enddate']['date'] : null;
        $occur          = array_key_exists('occur', $params) ? $params['occur'] : null;
        $exclude        = array_key_exists('exclude', $params) ? $params['exclude'] : null;      // array
        $include        = array_key_exists('include', $params) ? $params['include'] : null;      // array



        /**
        * Expected Values
        * 0: Daily
        * 1: Every weekday (Monday to Friday)
        * 2: Every Monday, Wednesday, and Friday
        * 3: Every Tuesday, and Thursday
        * 4: Weekly
        * 5: Monthly
        * 6: Yearly
        */

        if ( $frequency != null)
        {
            switch ( intval($frequency) )
            {

                #-- Daily
                case 0:
                    if ( $repeatEvery != null )
                    {
                        if ( $repeatEvery == 1 )
                        {
                            array_push( $ruleString, $frequencyLabels[$frequency] );
                        }
                        else
                        {
                            $everyBlankDays = "Every " . $repeatEvery . " days";
                            array_push( $ruleString, $everyBlankDays);
                        }
                    }
                    break;


                #-- Weekday
                case 1:
                    array_push( $ruleString, $frequencyLabels[$frequency] );
                    break;


                #-- Every Monday, Wednesday, Friday
                case 2:
                    array_push( $ruleString, $frequencyLabels[$frequency] );
                    break;


                #-- Every week on Tuesday, and Thursday
                case 3:
                    array_push( $ruleString, $frequencyLabels[$frequency] );
                    break;


                #-- Weekly + days of the week if selected
                case 4:

                    if ( $repeatOnDays != null )
                    {
                        if( $repeatEvery != null )
                        {
                            if ( intval($repeatEvery) > 1 )
                            {
                                #--  Every 5 weeks on Monday, Wednesday, Saturday
                                $daysString = join( ", ", $repeatOnDays);
                                $everyBlankWeeks = "Every " . $repeatsEvery . " weeks on " . strtoupper($daysString);
                                array_push( $ruleString, $everyBlankWeeks );
                            }
                            else
                            {
                                #-- Every week on Monday, Wednesday, Saturday
                                $daysString = join( ", ", $repeatOnDays);
                                $weeklyString = "Every week on " . strtoupper($daysString);
                                array_push( $ruleString, $weeklyString );
                            }
                        }
                        else
                        {
                            array_push( $ruleString, "Every week" );
                        }
                    }
                    break;


                #-- Every month
                case 5:
                    if( $repeatBy != null )
                    {
                        //$stime = strtotime($starts);
                        $startDate = new \DateTime($starts, new \DateTimeZone(craft()->getTimeZone()));
                        $everyMonthPre = "Monthly on the ";

                        #--  Every month on the 17th
                        if ( intval($repeatBy) == 0 )
                        {
                            $monthDay = $startDate->format('jS');
                            array_push( $ruleString, $everyMonthPre . $monthDay );
                        }

                        #-- Every month on the fifth Friday
                        if ( intval($repeatBy) == 1 )
                        {
                            $wkDay = new \DateTime($starts, new \DateTimeZone(craft()->getTimeZone()));
                            $nthDay = $this->getNthDay($startDate->format('w'));
                            array_push( $ruleString, $everyMonthPre . $nthDay . " " . $wkDay  );
                        }
                    }
                    break;


                #-- Every year
                case 6:
                    if ( $repeatEvery != null )
                    {
                        if ( $repeatEvery == 1 )
                        {
                            array_push( $ruleString, "Every year"  );
                        }
                        else
                        {
                            #-- Every 3rd year
                            $everyBlankYear = "Every " . $repeatsEvery . $this->getNumberSuffix($repeatsEvery) . " year";
                            array_push( $ruleString, $everyBlankYear );
                        }
                    }

                    break;

            }


            #-- Ends On by occurrence or date
            if ( $endsOn != null )
            {
                if (intval($endsOn[0]) == 1 && $occur != null)
                {
                    $endAfter = "for " . $occur . " times";
                    array_push( $ruleString, $endOnDate );
                }

                if (intval($endsOn[0]) == 2 && $endDate != null)
                {
                    $endOnDate = "until " . $endDate;
                    array_push( $ruleString, $endOnDate );
                }
            }


            return join(' ',$ruleString);
        }


    }


    /**
    * ICALENDAR RULE
    * @param  array
    * @return string
    *
    * FREQ=MONTHLY;BYDAY=-4FR;COUNT=5 : every month on the 4th last Friday for 5 times
    */

    public function getRRule( Array $params )
    {

        /**
        * POST VALUES
        */
        $frequency      = array_key_exists('frequency', $params) ? $params['frequency'] : null;
        $repeatEvery    = array_key_exists('every', $params) ? $params['every'] : null;
        $repeatOnDays   = array_key_exists('on', $params) ? $params['on'] : null;                // array
        $repeatBy       = array_key_exists('by', $params) ? $params['by'] : null;                // array
        $starts         = array_key_exists('starts', $params) ? $params['starts'] : null;
        $endsOn         = array_key_exists('endsOn', $params) ? $params['endsOn'] : null;        // array
        $endDate        = array_key_exists('enddate', $params) ? $params['enddate']['date'] : null;
        $occur          = array_key_exists('occur', $params) ? $params['occur'] : null;
        $exclude        = array_key_exists('exclude', $params) ? $params['exclude'] : null;      // array
        $include        = array_key_exists('include', $params) ? $params['include'] : null;      // array

        $ruleString = [];


        $rrule = [
            'FREQ'          => null,
            'DSTART'        => null,
            'COUNT'         => null,
            'BYDAY'         => null,
            'UNTIL'         => null,
            'INTERVAL'      => null,
            'BYMONTH'       => null,
            'BYMONTHDAY'    => null,
            'WKST'          => "MO",
            'EXDATE'        => null,
            'RDATE'         => null,
        ];

        $needles = [":","-","+0000"];
        $fill = ["","","Z"];


        if ( $frequency != null)
        {
            switch ( intval($frequency) )
            {
                #-- Daily
                case 0:
                    if ( $repeatEvery != null )
                    {
                        if ( $repeatEvery == 1 )
                        {
                            $rrule['FREQ'] = "DAILY";
                        }
                        else
                        {
                            $rrule['FREQ'] = "DAILY";
                            $rrule['INTERVAL'] = $repeatEvery;
                        }
                    }
                    break;

                #-- Weekday
                case 1:
                    $rrule['FREQ'] = "WEEKLY";
                    $rrule['BYDAY'] = "MO,TU,WE,TH,FR";
                    break;


                #-- Every week on MO, TU, WE
                case 2:
                    $rrule['FREQ'] = "WEEKLY";
                    $rrule['BYDAY'] = "MO,WE,FR";
                    break;


                #-- Every week on TU, TH
                case 3:
                    $rrule['FREQ'] = "WEEKLY";
                    $rrule['BYDAY'] = "TU,TH";
                    break;

                #-- Every week on TU, TH
                case 4:
                    if ( $repeatOnDays != null )
                    {
                        if( $repeatEvery != null )
                        {
                            if ( intval($repeatEvery) > 1 )
                            {
                                #--  Every 5 weeks on Monday, Wednesday, Saturday
                                $rrule['FREQ'] = "WEEKLY";
                                $rrule['INTERVAL'] = $repeatEvery;
                                $rrule['BYDAY'] = join(',', array_map(array($this,'getDOW'),$repeatOnDays));
                            }
                            else
                            {
                                #-- Every week on Monday, Wednesday, Saturday
                                $rrule['FREQ'] = "WEEKLY";
                                $rrule['BYDAY'] = join(',', array_map(array($this,'getDOW'),$repeatOnDays));
                            }
                        }
                        else
                        {
                            #-- Every week on day of start date
                            $day = new \DateTime($starts, new \DateTimeZone(craft()->getTimeZone()));
                            $rrule['FREQ'] = "WEEKLY";
                            $rrule['BYDAY'] = $this->getDOW($day->format('l'));
                        }
                    }
                    break;

                    #-- Every month
                    case 5:

                        if( $repeatBy != null )
                        {

                            //$stime = strtotime($starts);
                            $startDate = new \DateTime($starts, new \DateTimeZone(craft()->getTimeZone()));
                            $weekDay = $this->getDOW( $startDate->format('l') );


                            #--  Every month on the 17th
                            if ( intval($repeatBy[0]) == 0 )
                            {
                                $monthDay = $startDate->format('j');
                                $rrule['FREQ'] = "MONTHLY"; 

                                $rrule['BYMONTHDAY'] = $monthDay;
                                
                                if ($repeatEvery != null && intval($repeatEvery) > 1 ) {
                                    $rrule['INTERVAL'] = $repeatEvery;
                                }
                            }

                            #-- Every month on the fifth Friday
                            if ( intval($repeatBy[0]) == 1 )
                            {
                                $wkDay = new \DateTime($starts, new \DateTimeZone(craft()->getTimeZone()));
                                $nthDay = $this->getNthDay($startDate);
                                $rrule['FREQ'] = "MONTHLY";
                                if ($this->isFirst($startDate)) 
                                {
                                    #-- +1DOW
                                    $rrule['BYDAY'] = "+1" . $weekDay;
                                }
                                elseif ($this->isLast($startDate)) 
                                {
                                    #-- -1DOW
                                    $rrule['BYDAY'] = "-1" . $weekDay;
                                }
                                else
                                {
                                    #-- 28th
                                    $rrule['BYDAY'] = "+" . $nthDay . $this->getDOW( $wkDay->format('l') );
                                }
                                
                                if ($repeatEvery != null && intval($repeatEvery) > 1 ) {
                                    $rrule['INTERVAL'] = $repeatEvery;
                                }
                            }
                        }


                        break;


                    #-- Every year
                    case 6:
                        if ( $repeatEvery != null )
                        {
                            if ( $repeatEvery == 1 )
                            {
                                $rrule['FREQ'] = "YEARLY";
                            }
                            else
                            {
                                #-- Every 3rd year
                                $rrule['FREQ'] = "YEARLY";
                                $rrule['INTERVAL'] = $repeatEvery;
                            }
                        }
                        break;
            }


            #-- Starts
            if ($starts != null)
            {
                $stime = strtotime($starts);
                //$sdate = new \DateTime($starts,new \DateTimeZone(craft()->getTimeZone()));
                $rrule['DSTART'] = str_replace($needles, $fill, gmdate('c',$stime));
            }


            #-- Ends On by occurrence or date
            if ( $endsOn != null )
            {
                if (intval($endsOn[0]) == 0 && $occur == null)
                {
                    $rrule['COUNT'] = null;
                }

                if (intval($endsOn[0]) == 1 && $occur != null)
                {
                    $rrule['COUNT'] = $occur;
                }

                if (intval($endsOn[0]) == 2 && $endDate != null)
                {
                    #-- Add time so end date is included in recurrence schedule
                    $etime = strtotime("+23 hours",strtotime($endDate));
                    $rrule['UNTIL'] = str_replace($needles, $fill, gmdate('c',$etime));
                }
            }


            #-- Excluded Dates
            if ( $exclude != null )
            {
                $ex = [];

                for ($i=0; $i < count($exclude) ; $i++)
                {
                    $exdate = new \DateTime($exclude[$i], new \DateTimeZone(craft()->getTimeZone()));
                    array_push($ex, $exdate->format('Ymd'));
                }

                $rrule['EXDATE'] = join(',',$ex);
            }

            #-- Included Dates
            if ( $include != null )
            {
                $in = [];

                for ($i=0; $i < count($include) ; $i++)
                {
                    $indate = new \DateTime($include[$i], new \DateTimeZone(craft()->getTimeZone()));
                    array_push($in, $indate->format('Ymd'));
                }

                $rrule['RDATE'] = join(',',$in);
            }

        }


        foreach ($rrule as $key => $value) {
            if($value != null){
                array_push($ruleString, $key . "=" . $value);
            }
        }


        return join(';',$ruleString);

    }



    /**
    * Day of the week short format
    * @return String
    */
    public function getDOW ( $day )
    {
        $dayOfTheWeek = [
            "monday"    => "MO",
            "tuesday"   => "TU",
            "wednesday" => "WE",
            "thursday"  => "TH",
            "friday"    => "FR",
            "saturday"  => "SA",
            "sunday"    => "SU"
        ];
        return $dayOfTheWeek[strtolower($day)];
    }



    /**
    *  Suffix of number nd, th, rd
    *  @return String
    */
    public function getNumberSuffix( $number )
    {
        $last_number = substr($number,-1); //fetch the last number

        // if last number is 0 than it assign value 4
        if($last_number == "0" || $last_number == 0){
            $last_number = 4;
        }
        return date("S",mktime(0,0,0,1,$last_number,2009));
    }



    /**
    *  Map Array for populating modal
    *  @return Array
    */
    public function modalValuesArray($rule)
    {
        $ruleAry = explode(";",$rule);
        $keyValueAry = [];

        $outputAry = [];

        for ($i=0; $i < count($ruleAry); $i++)
        {
            $keyAry = explode("=",$ruleAry[$i]);
            $keyValueAry[$keyAry[0]] = $keyAry[1];
        }

        // If there is no count or until it never ends
        if (!array_key_exists('COUNT',$keyValueAry) && !array_key_exists('UNTIL',$keyValueAry)) 
        {
            $outputAry['endsOn'] = ['0'];
        }

        // Loop through assign values to output array
        foreach ($keyValueAry as $key => $value)
        {
            switch ($key)
            {
                case 'FREQ':
                    if (strtolower($value) == 'daily')
                    {
                        $outputAry['frequency'] = 0;
                    }
                    else if(strtolower($value) == 'weekly')
                    {
                        if(array_key_exists('BYDAY',$keyValueAry))
                        {
                            if($keyValueAry['BYDAY'] == "MO,TU,WE,TH,FR")
                            {
                                $outputAry['frequency'] = 1;
                            }
                            else if($keyValueAry['BYDAY'] == "MO,WE,FR")
                            {
                                $outputAry['frequency'] = 2;
                            }
                            else if($keyValueAry['BYDAY'] == "TU,TH")
                            {
                                $outputAry['frequency'] = 3;
                            }
                            else
                            {
                                $outputAry['frequency'] = 4;
                            }
                        }
                    }
                    else if(strtolower($value) == 'monthly')
                    {
                        $outputAry['frequency'] = 5;
                        if(array_key_exists('BYMONTHDAY',$keyValueAry))
                        {
                            $outputAry['by'] = 0;
                        }
                        else
                        {
                            $outputAry['by'] = 1;
                        }
                    }
                    else if(strtolower($value) == 'yearly')
                    {
                        $outputAry['frequency'] = 6;
                    }
                    break;

                case 'BYDAY':
                    $reg = "/\\b(MO|TU|WE|TH|FR|SA|SU)/"; 
                    if ( preg_match_all($reg, $value, $matches) ) 
                    {
                        $outputAry['on'] = array_map( array($this, "dayOfTheWeek"), $matches[0] );
                    }
                    break;

                case 'COUNT':
                    $outputAry['occur'] = $value;
                    $outputAry['endsOn'] = ['1'];
                    break;

                case 'DSTART':
                    $date = new \DateTime($value, new \DateTimeZone(craft()->getTimeZone()));
                    $outputAry['starts'] = $date->format('n/j/Y');
                    break;

                case 'INTERVAL':
                    $outputAry['every'] = $value;
                    break;

                case 'UNTIL':
                    #-- remove Z from date for date output to be correct.
                    $dte = str_replace("Z", "", $value);
                    $date = new DateTime($dte, new \DateTimeZone(craft()->getTimeZone()));
                    $outputAry['enddate'] = $date;
                    $outputAry['endsOn'] = ['2'];
                    break;

                case 'EXDATE':
                    $datesAry = explode(',',$value);
                    $exdates = [];
                    for ($i=0; $i < count($datesAry); $i++) {
                        $exdate = new \DateTime($datesAry[$i],new \DateTimeZone(craft()->getTimeZone()));
                        $exdates[$i] = $exdate->format('n/j/Y');
                    }
                    $outputAry['exclude'] = $exdates;
                    break;
                    
                case 'RDATE':
                    $datesAry = explode(',',$value);
                    $indates = [];
                    for ($i=0; $i < count($datesAry); $i++) {
                        $indate = new \DateTime($datesAry[$i],new \DateTimeZone(craft()->getTimeZone()));
                        $indates[$i] = $indate->format('n/j/Y');
                    }
                    $outputAry['include'] = $indates;
                    break;
            }

        }

        return $outputAry;
    }

    public function dayOfTheWeek($str)
    {
         $dow = [
            "MO" => "monday",
            "TU" => "tuesday",
            "WE" => "wednesday",
            "TH" => "thursday",
            "FR" => "friday",
            "SA" => "saturday",
            "SU" => "sunday"
        ];
        return $dow[$str];
    }


    public function byDayToOn($str)
    {
        $dow = [
            "MO" => "monday",
            "TU" => "tuesday",
            "WE" => "wednesday",
            "TH" => "thursday",
            "FR" => "friday",
            "SA" => "saturday",
            "SU" => "sunday"
        ];

        $strAry = explode(',',$str);
        $output = [];
        foreach ($strAry as $key => $value)
        {
            array_push($output,$dow[$value]);
        }
        return $output;
    }


    /**
    *  Nth day of the month.
    *  @return String
    */
    public function getNthDay( $date )
    {
        return ceil($date->format('j') / 7);
    }


    /**
    *  If First DOW in month.
    *  @return Bool
    */
    public function isFirst( $date )
    {
        $dateStr = $date->format('m/d/Y');
        $current = new \DateTime($dateStr);
        $proxy = new \DateTime($dateStr);
        $str = 'first ' . strtolower($date->format('l')) . ' of this month';
        $firstDay = $proxy->modify($str);
       
        return $firstDay == $current ? true : false;
    }


    /**
    *  If Last DOW in month.
    *  @return Bool
    */
    public function isLast ( $date )
    {
        
        $dateStr = $date->format('m/d/Y');
        $current = new \DateTime($dateStr);
        $proxy = new \DateTime($dateStr);
        $str = 'last ' . strtolower($date->format('l')) . ' of this month'; 
        $lastDay = $proxy->modify($str);
      
        return $lastDay == $current ? true : false;
    }


    /**
    *  Get EndOn Date for Recurr Rule
    *  @return Datetime
    */
    public function getEndOn ( $rule )
    {
        $ruleAry = explode(";",$rule);
        $keyValueAry = [];
        $endOn;

        for ($i=0; $i < count($ruleAry); $i++)
        {
            $keyAry = explode("=",$ruleAry[$i]);
            $keyValueAry[$keyAry[0]] = $keyAry[1];
        }

        if ( array_key_exists('UNTIL',$keyValueAry) ) 
        {
            $endOn = new DateTime($keyValueAry['UNTIL'], new \DateTimeZone(craft()->getTimeZone()));
        }
        else
        {
            $endOn = null;
        }

        return $endOn;
    }


    /**
       *
       * @param $recurRule recurrence rule - FREQ=YEARLY;INTERVAL=2;COUNT=3;
       * @return string recurrence string - every year for 3 times
       */
    public function recurTextTransform($recurRule, $lang = null)
    {
        //- Recurr's supported locales
        $locales = ['de','en','eu','fr','it','sv','es'];

        $locale = in_array(craft()->language, $locales) ? craft()->language : "en";
        if ($lang != null && in_array($lang, $locales))
        {
            $locale = $lang;
        }

        $rule = new \Recurr\Rule($recurRule, new \DateTime());

        $textTransformer = new \Recurr\Transformer\TextTransformer(
            new \Recurr\Transformer\Translator($locale)
        );

        return $textTransformer->transform($rule);
    }

}
