<!-- prezentacja aktualnej pogody -->

<?php
require_once("getDataFromApi.php");

class CurrentWeatherData  extends GetDataFromApi
{
    private $sunrise;
    private $sunset;

    function __construct($url)
    {
        parent::__construct($url);
        $this->sunrise = $this->data['sys']['sunrise'];
        $this->sunset = $this->data['sys']['sunset'];
    }

    function sunRiseSet()
    {
        return array($this->sunrise, $this->sunset);
    }


    function showCurrrentWeather()
    {
        $iconCode = $this->data['weather'][0]['icon'];
        $imgSrc = "http://openweathermap.org/img/w/" . $iconCode . ".png";

        echo '<div class="currentWeather">';
        echo '<div class="top">';

        echo '<div class="title"><p>';
        echo 'Weather in ';
        echo $this->data['name'], ', ', $this->data['sys']['country'];
        echo '</p></div>';

        echo '<div class="weather">';
        echo "<img src='$imgSrc' alt='weather icon'>";
        echo '<p>', round($this->data['main']['temp']), ' ', '&deg;C</p>';
        echo '</div>';

        echo '<div class="description">';
        echo  $this->data['weather'][0]['description'];
        echo '</div>';

        echo '<div class="date">';
        echo  date("H:i M:y", $this->data['dt']);
        echo '</div>';

        echo '</div>';

        echo '<div class="tableCurrentWeather">';
        echo '<table>
        <tr>
        <td>Wind</td>
        <td>', $this->data['wind']['speed'], ' m/s', '</td>
        </tr>',
            '<tr>
        <td>Clouds</td>
        <td>',
            $this->data['clouds']['all'],
            '</td>
        </tr>',
            '<tr>
        <td>Pressure</td>
        <td>',
            $this->data['main']['pressure'],
            ' hpa',
            '</td>
        </tr>',
            '<tr>
        <td>Humidity</td>
        <td>',
            $this->data['main']['humidity'],
            '%',
            ' hpa',
            '</td>
        </tr>',
            '<tr>
        <td>Sunrise</td>
        <td>',
            date('H:i', $this->data['sys']['sunrise']),
            '</td>
        </tr>',
            '<tr>
        <td>Sunset</td>
        <td>',
            date('H:i', $this->data['sys']['sunset']),
            '</td>
        </tr>',
            '<tr>
        <td>Geo <br> coords</td>
        <td>[',
            $this->data['coord']['lon'],
            ', ',
            $this->data['coord']['lat'],
            ']</td>
        </tr>

        </table>';
        echo '</div>';
        echo '</div>';
    }
}
