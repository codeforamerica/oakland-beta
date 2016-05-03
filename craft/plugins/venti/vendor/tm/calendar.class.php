<?php
    /* ------------------------------------------------------------ *\
            Author: Adam Randlett
            @adamrandlett
            randlett.net

            -Generate a custom calendar view
            -Multi day event output courtesy of Caleb Durham

    \* ------------------------------------------------------------ */

    class Calendar{

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
            protected $firstDayOfMonth;
            protected $lastDayOfMonth;
            protected $firstDayNum;
            protected $lastDayNum;
            protected $days_in_month;
            protected $eventsGroupedByDay;
            protected $timezone;


        public function __construct($month, $year)
        {
            $this->month = $month;
            $this->year = $year;
            $this->firstDayOfMonth = strtolower($this->firstOfMonth($this->month,$this->year));
            $this->lastDayOfMonth = strtolower($this->lastOfMonth($this->month,$this->year));
            $this->firstDayNum = $this->weekdays[$this->firstDayOfMonth];
            $this->lastDayNum = $this->weekdays[$this->lastDayOfMonth];
            $this->days_in_month = cal_days_in_month(0, $this->month, $this->year);
        }


        /* GET EVENTS INTO ARRAYS BY DAY AND OUTPUT CALENDAR */

        public function createCalendar($events,$timezone)
        {

            $this->timezone = $timezone;

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

                foreach ($events as $event)
                {

                    //$event = $key;
                    $eventElm2 = $event->title;
                    $startDate = $event->startDate;
                    $endDate = $event->endDate;
                    //create event element array
                    $eventElm = array(
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

                    //\CVarDumper::dump($startDate->format('Ynj'), 5, true);

                    // if($startDate->format('Ynj') == $arrayId)
                    // {
                    //   $count = (count($this->eventsGroupedByDay[$arrayId]['events']) + 1) - 1 ;
                    //   $this->eventsGroupedByDay[$arrayId]['events'][$count] = $eventElm;
                    // }
                    
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



        /* GENERATE EVENTS FROM CRAFT EVENTS INTO UL */

        private function generateEvents($day_id)
        {
            // \CVarDumper::dump($this->eventsGroupedByDay, 5, true);
            // exit;
            $listItems = "";
            $evtArry  = $this->array_msort($this->eventsGroupedByDay[$day_id]['events'], array('dateString'=>SORT_ASC));
            //\CVarDumper::dump($evtArry, 5, true);exit;
            if($evtArry)
            {
                foreach($evtArry as $key)
                {
                    $listItems .= "<li>";
                    $listItems .= "<a href='".$key['url']."/".$key['eid']."'>".$key['title']."</a>";
                    $listItems .= "</li>";
                }
                return "<ul>" . $listItems . "</ul>";
            }
            else
            {
                return false;
            }

        }



        /* GET PARTS OF CALENDAR JOIN AND OUTPUT */

        private function outputCalendar()
        {
            $headerString = "<table><thead><tr>
                <th>
                    <span class='tri'>Sun</span>
                    <span class='single'>S</span>
                </th>
                <th>
                    <span class='tri'>Mon</span>
                    <span class='single'>M</span>
                </th>
                <th>
                    <span class='tri'>Tue</span>
                    <span class='single'>T</span>
                </th>
                <th>
                    <span class='tri'>Wed</span>
                    <span class='single'>W</span>
                </th>
                <th>
                    <span class='tri'>Thu</span>
                    <span class='single'>T</span>
                </th>
                <th>
                    <span class='tri'>Fri</span>
                    <span class='single'>F</span>
                </th>
                <th>
                    <span class='tri'>Sat</span>
                    <span class='single'>S</span>
                </th>
            </tr></thead><tbody><tr>";
            $footerString = "</tbody></table>";
            $beginBlanks = $this->getBlankDays($this->firstDayNum,"begin");
            $endBlanks = $this->getBlankDays($this->lastDayNum,"end");
            $days = $this->getDays($this->days_in_month,$this->month,$this->year);

            return $headerString . $beginBlanks . $days . $endBlanks . $footerString;
        }



        /* GET BLANK NON MONTH DAYS FOR FILLERS */

        private function getBlankDays($blanks,$pos)
        {
            $blank_days = $blanks;
            $blank_string = "";
            $day_count = $pos == 'begin' ? 1 : 6;
            if($pos == 'begin')
            {

                while($blank_days > 0)
                {
                    $blank_string .= "<td class='blank_day'></td>";
                    $blank_days = $blank_days - 1;
                }
                $day_count ++;

            }
            else
            {

                while($blank_days < 6)
                {
                    $blank_string .= "<td class='blank_day'></td>";
                    $blank_days = $blank_days + 1;
                }
                $day_count --;

            }

            return $blank_string;
        }



        /* GET DAYS OF THE MONTH WITH EVENTS INPUT */

        private function getDays()
        {
            $day_num = 1;
            $day_count = $this->firstDayNum + 1;
            $days_string = "";
            $dt = new \DateTime();
            $dt->setTimeZone(new \DateTimeZone($this->timezone));
            $today = $dt->format('Ynj');
            while($day_num <= $this->days_in_month)
            {
                
                //$day_id = $this->year.$this->month.$day_num;
                if(($this->month < 10) && ($day_num < 10)){
                    $day_id = $this->year. 0 .$this->month. 0 .$day_num;
                }elseif(($this->month < 10)){
                    $day_id = $this->year. 0 .$this->month.$day_num;
                }elseif(($day_num < 10)){
                    $day_id = $this->year.$this->month. 0 .$day_num;
                }else{
                    $day_id = $this->year.$this->month.$day_num;
                }
                $day_events = !$this->generateEvents($day_id,$this->eventsGroupedByDay) ? null : $this->generateEvents($day_id,$this->eventsGroupedByDay);

                $no_events = $day_events == null ? "no_events" : "has_events";
                $days_string .= $today == $day_id ? '<td class="today '.$no_events.'">' : '<td class="'.$no_events.'">';
                $days_string .='<a href="/day/'.$this->year.'/'.$this->month.'/'.$day_num.'" class="title"><span>today</span><em>'.$day_num.'</em></a>';
                $days_string .= $day_events;
                $days_string .='</td>';

                $day_num++;
                $day_count++;

                if($day_count > 7)
                {
                    $days_string .= "</tr><tr>";
                    $day_count = 1;
                }
            }
            return $days_string;
        }


        /* GET FIRST WEEKDAY OF THE MONTH - SHORT FORM (Sun,Mon,Tue...) */

        private function firstOfMonth($month,$year)
        {
            return date("D", strtotime($month.'/01/'.$year.' 00:00:00'));
        }



        /* GET LAST WEEKDAY OF THE MONTH - SHORT FORM (Sun,Mon,Tue...) */

        private function lastOfMonth($month,$year)
        {
            return date("D", strtotime('-1 second',strtotime('+1 month',strtotime($month.'/01/'.$year.' 00:00:00'))));
        }


    }
?>