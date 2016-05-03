<?php

    namespace Craft;
    /* ------------------------------------------------------------ *\
            Author: Adam Randlett
            @adamrandlett
            randlett.net

            -Generate a custom calendar view
            -Multi day event output courtesy of Caleb Durham

    \* ------------------------------------------------------------ */

    class Venti_Calendar{

            protected $weekdays = array(
                    'sun' => 0,
                    'mon' => 1,
                    'tue' => 2,
                    'wed' => 3,
                    'thu' => 4,
                    'fri' => 5,
                    'sat' => 6
                );
            protected $month;
            protected $year;
            protected $events;
            protected $firstDayOfMonth;
            protected $lastDayOfMonth;
            protected $firstDayNum;
            protected $lastDayNum;
            protected $days_in_month;
            protected $eventsGroupedByDay;
            protected $timezone;

        /**
         * __construct 
         * @param object $criteria (events,month,day)
         */
        public function __construct($criteria = null)
        {
            $this->month = $criteria['month'];
            $this->year = $criteria['year'];
            $this->firstDayOfMonth = strtolower($this->firstOfMonth($this->month,$this->year));
            $this->lastDayOfMonth = strtolower($this->lastOfMonth($this->month,$this->year));
            $this->firstDayNum = $this->weekdays[$this->firstDayOfMonth];
            $this->lastDayNum = $this->weekdays[$this->lastDayOfMonth];
            $this->days_in_month = cal_days_in_month(0, $this->month, $this->year);
            $this->events = $criteria['events'];
        }


        /* GET EVENTS INTO ARRAYS BY DAY AND OUTPUT CALENDAR */

        public function createCalendar()
        {

            for( $d = 1; $d < $this->days_in_month + 1 ; $d++ )
            {
                //day of month year
                $idxDay = $d;
                //year.day.month
                //$arrayId = $this->year.$this->month.$d;
                if(($this->month < 10) && ($d < 10)){
                    $arrayId = $this->year. 0 .$this->month. 0 .$d;
                }elseif(($this->month < 10)){
                    $arrayId = $this->year. 0 .$this->month.$d;
                }elseif(($d < 10)){
                    $arrayId = $this->year.$this->month. 0 .$d;
                }else{
                    $arrayId = $this->year.$this->month.$d;
                }
                //set array with day as key
                $this->eventsGroupedByDay[$arrayId] = array(
                    'date' => array(
                        'day' => $d,
                        'month' => $this->month,
                        'year' => $this->year
                    ),
                    'events' => array()
                );

                foreach ($this->events as $event)
                {

                    //$event = $key;
                    $eventElm2 = $event->title;
                    $startDate = $event->startDate;
                    $endDate = $event->endDate;
                    //create event element array
                    $eventElm = array(
                        'event' => $event,
                        'title' => $event->title,
                        'url' => $event->url,
                        'slug' => $event->slug,
                        'eid' => $event->eid,
                        'id' => $event->id,
                        'dateString' => $startDate,
                        'dateParts' => array(
                            'day' => $startDate->format('d'),
                            'month' => $startDate->format('m'),
                            'year' => $startDate->format('Y')
                        )
                    );
                    
                    if($startDate->format('Ymd') == $arrayId)
                    {
                        $count = (count($this->eventsGroupedByDay[$arrayId]['events']) + 1) - 1 ;
                        $this->eventsGroupedByDay[$arrayId]['events'][$count] = $eventElm;
                    }elseif($endDate->format('Ymd') == $arrayId)
                    {
                        $count = (count($this->eventsGroupedByDay[$arrayId]['events']) + 1) - 1 ;
                        $this->eventsGroupedByDay[$arrayId]['events'][$count] = $eventElm;
                    }
                    if($startDate->format('Ymd') < $arrayId && $endDate->format('Ymd') > $arrayId)
                    {
                        $count = (count($this->eventsGroupedByDay[$arrayId]['events']) + 1) - 1 ;
                        $this->eventsGroupedByDay[$arrayId]['events'][$count] = $eventElm;
                    }
                }
            }

            return $this->outputCalendar();
        }

        function array_msort($array, $cols)
        {
            $colarr = array();
            foreach ($cols as $col => $order) {
                $colarr[$col] = array();
                foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
            }
            $eval = 'array_multisort(';
            foreach ($cols as $col => $order) {
                $eval .= '$colarr[\''.$col.'\'],'.$order.',';
            }
            $eval = substr($eval,0,-1).');';
            eval($eval);
            $ret = array();
            foreach ($colarr as $col => $arr) {
                foreach ($arr as $k => $v) {
                    $k = substr($k,1);
                    if (!isset($ret[$k])) $ret[$k] = $array[$k];
                    $ret[$k][$col] = $array[$k][$col];
                }
            }
            return $ret;
        }



        /* GET PARTS OF CALENDAR JOIN AND OUTPUT */

        private function outputCalendar()
        {
            $localeData = craft()->i18n->getLocaleData("en_us");
            $calVariable = new \Craft\CalendarVariable();
            $calVariable->head = $this->groupWeekDayNames($localeData);
            $calVariable->month = $this->month;
            $calVariable->year = $this->year;
            $dayEvents = [];
            $output = array_merge(
                $dayEvents, 
                $this->getBlankDays($this->firstDayNum,"begin"), 
                $this->getDays(), 
                $this->getBlankDays($this->lastDayNum,"end")
            );
            return array($calVariable, $output);
        }



        /* GET BLANK NON MONTH DAYS FOR FILLERS */

        private function getBlankDays($blanks,$pos)
        {
            $blank_days = $blanks;
            $blank_array = [];
            $day_count = $pos == 'begin' ? 1 : 6;
            $lastMonthLastDay = $this->getPreviousMonthDay($this->month,$this->year) - $blanks;
            $nextMonthFirstDay = $this->getNextMonthDay($this->month,$this->year);
            $nextMonth =  $this->getNextMonth($this->month,$this->year);
            $prevMonth = $this->getPreviousMonth($this->month, $this->year);

            if($pos == 'begin')
            {

                while($blank_days > 0)
                {
                    $lastMonthLastDay = $lastMonthLastDay + 1;
                    $year = $prevMonth == 12 ? $this->year - 1 : $this->year;
                    $date = $this->getDate($prevMonth .'/'.$lastMonthLastDay.'/'. $year);
                    array_push($blank_array,["day"=>$lastMonthLastDay,"today"=>"","date"=>$date,"events"=>[]]);
                    $blank_days = $blank_days - 1;
                }
                $day_count ++;

            }
            else
            {

                while($blank_days < 6)
                {
                    $year = $nextMonth == 1 ? $this->year + 1 : $this->year;
                    $date = $this->getDate($nextMonth .'/'. $nextMonthFirstDay .'/'. $year);
                    array_push($blank_array,["day"=>$nextMonthFirstDay,"today"=>"","date"=>$date,"events"=>[]]);
                    $nextMonthFirstDay = $nextMonthFirstDay + 1;
                    $blank_days = $blank_days + 1;
                }
                $day_count --;

            }

            return $blank_array;
        }



        /* GET DAYS OF THE MONTH WITH EVENTS INPUT */

        private function getDays()
        {
            $day_num = 1;
            $day_count = $this->firstDayNum + 1;
            $day_events = [];
            $dt = new \DateTime();
            $dt->setTimeZone(new \DateTimeZone(craft()->getTimeZone()));
            $today = $dt->format('Ymd');
            while($day_num <= $this->days_in_month)
            {
                $day_obj = [];

                if(($this->month < 10) && ($day_num < 10)){
                    $day_id = $this->year. 0 .$this->month. 0 .$day_num;
                }elseif(($this->month < 10)){
                    $day_id = $this->year. 0 .$this->month.$day_num;
                }elseif(($day_num < 10)){
                    $day_id = $this->year.$this->month. 0 .$day_num;
                }else{
                    $day_id = $this->year.$this->month.$day_num;
                }

                $day_obj["day"] = $day_num;
                $day_obj["today"] = $today == $day_id ? true : false;
                $time = strtotime($this->month."/".$day_num."/".$this->year);
                $day_obj["date"] = new DateTime(date("Y-m-d",$time),new \DateTimeZone(craft()->getTimeZone()));
                $day_obj["events"] = $this->array_msort($this->eventsGroupedByDay[$day_id]['events'], array('dateString'=>SORT_ASC));
                array_push($day_events, $day_obj);

                $day_num++;
                $day_count++;

                if($day_count > 7)
                {
                    $day_count = 1;
                }
            }
            return $day_events;
        }

        /**
         * GET THE NAME OF EACH DAY OF THE WEEK IN DIFFERNT FORMATS
         * @param  Object $localeData
         * @return Array 
         */
        private function groupWeekDayNames($localeData)
        {
            $names = [];
            $narrow = $localeData->getWeekdayNames('narrow');
            $short = $localeData->getWeekdayNames('short');
            $abbr = $localeData->getWeekdayNames('abbreviated');
            $wide = $localeData->getWeekdayNames('wide');
            $length = count($wide);

            for ($i=0; $i < $length ; $i++) { 
                $names[$i] = array(
                    "narrow" => $narrow[$i],
                    "short" => $short[$i],
                    "abbr" => $abbr[$i],
                    "wide" => $wide[$i]
                );
            }

            return $names;

        }


        /**
         * GET FIRST WEEKDAY OF THE MONTH - SHORT FORM (Sun,Mon,Tue...)
         * @param  int $month 
         * @param  int $year  
         * @return string     day number
         */

        private function firstOfMonth($month,$year)
        {
            return date("D", strtotime($month.'/01/'.$year.' 00:00:00'));
        }

        /**
         * GET LAST WEEKDAY OF THE MONTH - SHORT FORM (Sun,Mon,Tue...)
         * @param  int $month 
         * @param  int $year  
         * @return string     day number
         */

        private function lastOfMonth($month,$year)
        {
            return date("D", strtotime('-1 second',strtotime('+1 month',strtotime($month.'/01/'.$year.' 00:00:00'))));
        }

        /**
         * GET THE PREVIOUS MONTH
         * @param  int $month 
         * @param  int $year  
         * @return string     day number
         */
        private function getPreviousMonthDay($month,$year)
        {
            return date("j", strtotime('-1 second',strtotime($month.'/01/'.$year.' 00:00:00')));
        }

        /**
         * GET THE NEXT MONTH FIRST DAY
         * @param  int $month 
         * @param  int $year  
         * @return string   day number
         */
        private function getNextMonthDay($month,$year)
        {
            return date("j", strtotime('+1 second',strtotime($month.'/01/'.$year.' 24:59:99')));
        }

        /**
         * GET THE PREVIOUS MONTH
         * @param  int $month 
         * @param  int $year 
         * @return string    month number
         */
        private function getPreviousMonth($month,$year)
        {
            return date("n", strtotime('-1 day',strtotime($month.'/01/'.$year)));
        }

        /**
         * GET THE NEXT MONTH
         * @param  int $month 
         * @param  int $year 
         * @return string    month number
         */
        private function getNextMonth($month,$year)
        {
            return date("n", strtotime('+1 month',strtotime($month.'/01/'.$year)));
        }

        /**
         * GET A DATE IN DATETIME FORMAT
         * @param  int $month 
         * @param  int $year 
         * @return DateTime  
         */
        private function getDate($date)
        {
            return new \DateTime($date, new \DateTimeZone(craft()->getTimeZone()));
        }

    }
?>