<?php
    namespace App\ClientServices;
    
    class MyService
    {
        public function showPointageMonths($date1, $n)
        {
            $date2 = strtotime("+".$n." months", strtotime($date1));
            $date2 = date("Y-m-d", $date2);
            $start    = new \DateTime($date1);
            $start->modify('first day of this month');
            $end      = new \DateTime($date2);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period   = new \DatePeriod($start, $interval, $end);
            $tab = [];
            foreach ($period as $dt) {
                //echo $dt->format("Y-m") . "<br>\n";
                array_push($tab, $dt->format("m-Y"));
            }
            return $tab;
        }
        public function to_money($amount): string
        {
            $amount = floatval($amount);
            $y = number_format($amount, 2);
            $z = str_replace(",", " ", $y);

            return $z;
        }

        public function getAmount($money)
        {
            $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
            $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

            $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

            $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
            $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

            return (float) str_replace(',', '.', $removedThousandSeparator);
        }

    }