<!-- obróbka danych w celu wygenerowania dziennej prognozy pogody na podstawie danych uzyskanych z api z 5-cio dniowej 3 godzinnej prognozy
m.in ustalenie wschodu i zachodu słońca w celu określenia godzin dnia i nocy, by wybrać max i min temperaturę w każdym dniu prognozy
wyliczenie średnich wartości wiatru i ciśnienia dla każdego dnia
zapis uzyskanych danych do tablicy obiektów -->

<?php

class MyForecast
{

    private $sunRise;
    private $sunSet;
    private $data;
    private $startDayIndex;
    private $endDayIndex;
    private $dataTime = array();
    private $dateMyForecast = array();
    private $pressureMyForecast = array();
    private $windMyForecast = array();
    public $myForecast = array();


    function __construct($sunRiseSet, $data)
    {
        $this->sunRise = intval(date('H', $sunRiseSet[0])) * 60 + intval(date('i', $sunRiseSet[0])); //minuts
        $this->sunSet = intval(date('H', $sunRiseSet[1])) * 60 + intval(date('i', $sunRiseSet[1]));
        $this->data = $data;
    }

    function dataTime()
    {
        for ($i = 0; $i < 40; $i++) {

            if (!$this->dataTime) {
                $this->dataTime[$i] = intval(date('H', ($this->data['list'][$i]['dt'] - date("Z")))) * 60;
                $i++;
            }

            foreach ($this->dataTime as $value) {
                if ($value !== intval(date('H', ($this->data['list'][$i]['dt'] - date("Z")))) * 60) {
                    $this->dataTime[$i] = intval(date('H', ($this->data['list'][$i]['dt'] - date("Z")))) * 60;
                } else {
                    sort($this->dataTime);
                    return  $this->dataTime;
                }
            }
        }
    }

    function startDayHour()
    {
        foreach ($this->dataTime as $key => $value) {

            if ($this->sunRise <= $value) {

                if ($value - $this->sunRise <= 90) {

                    $this->startDayIndex = $key;
                    return;
                } else {

                    $this->startDayIndex = $key - 1;
                    return;
                }
            }
        }
    }

    function endDayHour()
    {

        foreach ($this->dataTime as $key => $value) {

            if ($this->sunSet >= $this->dataTime[7]) {
                $this->endDayHour = $this->dataTime[7];
                $this->endDayIndex = 7;
                return;
            } else {
                if ($this->sunSet <= $value) {
                    if ($value - $this->sunSet <= 90) {
                        $this->endDayIndex = $key;
                        return;
                    } else {

                        $this->endDayIndex = $key - 1;
                        return;
                    }
                }
            }
        }
    }

    function myForecastData()
    {
        $tempDay = array();
        $tempD = array();
        $tempNight = array();
        $tempN = array();
        $wind = array();
        $pressure = array();
        $maxTempD = array();
        $minTempN = array();


        foreach ($this->data['list'] as $key => $value) {
            if (!$key) {
                $currentData = date('Y-m-d', $value['dt']);
                $prevData = $currentData;
            }


            if ($currentData !== (date('Y-m-d', $value['dt']))) {


                if (($this->dataTime[$this->startDayIndex] <=  intval(date('H', ($value['dt'] - date("Z")))) * 60) && ($this->dataTime[$this->endDayIndex] >=  intval(date('H', ($value['dt'] - date("Z")))) * 60)) {
                    // echo 'Zapamiętujemy temperatury';
                    array_push($tempDay, $value['main']['temp']);
                }

                if (($this->dataTime[$this->startDayIndex] >=  intval(date('H', ($value['dt'] - date("Z")))) * 60) || ($this->dataTime[$this->endDayIndex] <=  intval(date('H', ($value['dt'] - date("Z")))) * 60)) {
                    // echo 'NOC !!!!!!!!!!!!!!!!!';
                    array_push($tempNight, $value['main']['temp']);
                }


                if ($prevData !== date('Y-m-d', $value['dt'])) {
                    // echo '<br>Kolejny dzień:';

                    if ($tempDay) {
                        array_push($tempD, $tempDay);
                        array_splice($tempDay, 0);
                    }
                    if ($tempNight) {
                        array_push($tempN, $tempNight);
                        array_splice($tempNight, 0);
                    }

                    array_push($this->dateMyForecast, date('D j M', $value['dt']));

                    $prevData = date('Y-m-d', $value['dt']);

                    if ($wind) {
                        array_push($this->windMyForecast, (array_sum($wind) / count($wind)));
                        array_splice($wind, 0);
                    }
                    if ($pressure) {
                        array_push($this->pressureMyForecast, (array_sum($pressure) / count($pressure)));
                        array_splice($pressure, 0);
                    }
                }
                array_push($wind, $value['wind']['speed']);
                array_push($pressure, $value['main']['pressure']);
            }
        }


        for ($i = 0; $i < count($tempD); $i++) {
            array_push($maxTempD, max($tempD[$i]));
        }

        for ($i = 0; $i < count($tempN); $i++) {
            array_push($minTempN, min($tempN[$i]));
        }

        for ($i = 0; $i < count($tempD); $i++) {

            $this->myForecast[$i] = new stdClass();

            $this->myForecast[$i]->date = $this->dateMyForecast[$i];
            $this->myForecast[$i]->maxTemp = $maxTempD[$i];
            $this->myForecast[$i]->minTemp = $minTempN[$i];
            $this->myForecast[$i]->wind = round($this->windMyForecast[$i], 2);
            $this->myForecast[$i]->pressure = round($this->pressureMyForecast[$i], 2);
        }
    }


    function showMyForecast()
    {
        echo '<div class="myForecastTable">';
        echo '<table>';

        foreach ($this->myForecast as $key => $value) {
            echo '<tr>';
            echo '<td><div>';
            echo $value->date;
            echo '</div></td>';
            echo '<td>';
            echo '<div class="temp">';
            echo $value->maxTemp,  '&deg;C';
            echo '</div>';
            echo '<div class="temp">';
            echo $value->minTemp, '&deg;C';;
            echo '</div>';
            echo '<div>';
            echo $value->wind, ' m/s';
            echo '</div>';
            echo '<div>';
            echo $value->pressure, ' hpa';
            echo '</div>';
            echo '</td>';
        }
        echo '</table>';

        echo ' </div >';
    }
}
