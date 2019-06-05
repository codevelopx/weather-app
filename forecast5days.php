<?php

class Forecast5days {

    private $url='';
    private $step='';
    public $data;
    public $dataTemp = array();

    function __construct($url, $step){
        $this->url = $url;
        $this->step = $step;
    }

    function loadData(){

        $error = false;

        set_error_handler(
            function () {
                throw new ErrorException("Nieprawidłowa nazwa miasta lub nie mogę połączyć się z API", 123);
            }
        );
        
        try {
            $response  = file_get_contents($this->url);
            $this->data  = json_decode($response, true);
        }
        catch (Exception $e) {
            echo $e->getMessage();
            $error = true;
        }        
        restore_error_handler(); 

        return $error;      
    }

    function showForecast(){

        echo "5-cio dniowa prognoza pogody dla:";
        echo $this->data['city']['name'].",".$this->data['city']['country'];
        echo "<br/><br/>";

        echo '<div class="forecast"><div class="chart"> <div id="chart_div"></div></div><div class="dataContainer">';

        $imgSrc="http://openweathermap.org/img/w/";
        //  $dataTemp = array();
        $dataPrev='';
        $color = true;

        foreach($this->data['list'] as $day => $value) { 
            $array = array();
            $desc = $value['weather'][0]['description'];
            $temp = $value['main']['temp'];
            $pressure = $value['main']['pressure'];
            $wind = $value['wind']['speed'];
            $dt_txt = $value['dt_txt'];
            $iconCode = $value['weather'][0]['icon'];

            $imgSrc="http://openweathermap.org/img/w/".$iconCode.".png";

            $date = explode(" ",$dt_txt);

        
            if($day>=$this->step){

                if( $dataPrev == $date[0]){
                    if($color)
                    $bgcColor = "#B8C4CC";
                    else 
                    $bgcColor = "#A1ACB3";
                }
                else{
                    $dataPrev = $date[0];
                    if($color){
                        $color=!$color;
                        $bgcColor = "#A1ACB3";
                    }
                    else{
                        $bgcColor = "#B8C4CC";
                        $color = !$color;
                    }
                    
                }                

            echo '<div class="data">';
            echo "<div class='hour'>".substr($date[1],0,-3)."</div>";
            echo "<div class='icon'><img src='$imgSrc' alt='weather icon'></div>";
            echo "<div class='temp'>$temp &deg;C</div>";
            echo "<div class='wind'>$wind m/s</div>";
            echo "<div class='pressure'>$pressure hpa</div>";
            echo '<div class="date" style="background-color:'.$bgcColor.'">'.$date[0]."</div>";
            echo "</div>";
            }
            
            $this->dataTemp[] =  array(substr($date[1],0,-3),$temp);
        }
        echo '</div></div>'; 
    }

    function dataPoints(){
        return array_slice($this->dataTemp, $this->step, 10);
    }

}

?>