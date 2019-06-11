<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <title>Weather and forecast in your city</title>
</head>

<body>


    <?php
    require_once("forecast5days.php");
    require_once("currentWeatherData.php");
    require_once("myForecast.php");

    //sprawdzenie czy nazwa miasta byla już wpisana, a jak nie to  ustawienie na domyślne. Pobranie parametru $step do przewijania poziomego wykresu

    if (isset($_GET["city"])) {
        $city = $_GET["city"];
    } else {
        $city = "Warszawa";
    }

    if (isset($_GET["step"])) {
        $step = $_GET["step"];

        if (is_int((int)$step) && is_numeric($step)) {
            if ($step < 0)
                $step = 0;
            else if ($step > 30)
                $step = 30;
        } else
            $step = 0;
    } else {
        $step = 0;
    }
    ?>
    <!-- formularz podania nazwy miasta -->
    <div class="search">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="search">
            <input type="text" name="city" placeholder="City..." value="<?php echo $city; ?>">
            <input type="submit" value="ok">
        </form>
    </div>

    <div class="container">

        <!-- połączenie z api oraz wyświetlanie aktualnej pogody dla wybranego miasta -->
        <?php
        $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&APPID=96993feca341f53ea0847433eae53af5";
        $currentWeatherData = new CurrentWeatherData($url);
        if ($currentWeatherData->loadData()) {
            echo $currentWeatherData->loadData();
            return 0;
        }

        $currentWeatherData->showCurrrentWeather();

        ?>


        <div class="wrapperForecast">

            <!-- połączenie z api oraz wyświetlenie 5 dniowej prognozy pogody w cyklach 3 godzinnych -->

            <?php

            $url1 = "https://api.openweathermap.org/data/2.5/forecast?q=$city&units=metric&APPID=96993feca341f53ea0847433eae53af5";

            $forecast5days = new Forecast5days($url1, $step);
            // if ($forecast5days->loadData())
            //     return 0;
            $forecast5days->showForecast();

            //dane potrzebne do przekazanie dla skryptu JS w celu wygenerowania wykresu
            $dataPoints = $forecast5days->dataPoints();

            // strzałki do przewijania prognozy do przodu lub do tyłu
            if ($step > 0) {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?city=' . $city . '&step=' . ($step - 1) . '"><div class="arrowLeft"></div></a>';
            }

            if ($step <= 30) {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?city=' . $city . '&step=' . ($step + 1) . '"><div class="arrowRight"></div></a>';
            }

            ?>
        </div>
        <?php
        //wygenerowanie własnych danych na podstawie obróbki danych otrzymanych z api - prognoza 5-dniowa co 3 godziny
        $myForecast = new myForecast($currentWeatherData->sunRiseSet(), $forecast5days->data());

        $dataTime = $myForecast->dataTime();
        $myForecast->startDayHour();
        $myForecast->endDayHour();
        $myForecast->myForecastData();
        $myForecast->showMyForecast();

        ?>

    </div>

    <div class="alert">
        <p>Minimalna rozdzielczość dla strony to 600px</p>
    </div>

    <!-- skrypt do wygenerowania wykresu, odświeżany po zmianie wielkości okna -->
    <script>
        google.charts.load('current', {
            packages: ['corechart', 'line']
        });
        google.charts.setOnLoadCallback(drawBackgroundColor);

        function drawBackgroundColor() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Element');
            data.addColumn('number', 'Temp');

            data.addRows(<?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>)

            var options = {
                // hAxis: {
                //   title: 'Time'
                // },
                vAxis: {
                    //   title: "Temp. C",
                    textPosition: 'in',
                },
                width: '100%',
                height: 298,
                legend: {
                    position: 'none',
                },
                chartArea: {
                    left: 0,
                    top: 20,
                    width: '100%',
                    height: '85%',
                },

                animation: {
                    duration: 1000,
                    easing: 'out',
                    startup: true
                },

                backgroundColor: '#f1f8e9'
            };
            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
            chart.draw(data, options);

            function resize() {
                var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }

            window.onload = resize;
            window.onresize = resize;
        }
    </script>

</body>

</html>