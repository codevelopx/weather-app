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
    // date_default_timezone_set("UTC");

    require_once("forecast5days.php");
    require_once("currentWeatherData.php");
    require_once("myForecast.php");

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

    <div class="container">

        <?php
        $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&APPID=96993feca341f53ea0847433eae53af5";
        $currentWeatherData = new CurrentWeatherData($url);
        $currentWeatherData->showCurrrentWeather();

        ?>


        <div class="wrapperForecast">
            <div class="search">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="search">
                    <input type="text" name="city" placeholder="City..." value="<?php echo $city; ?>">
                    <input type="submit" value="ok">
                </form>
            </div>

            <?php

            $url1 = "https://api.openweathermap.org/data/2.5/forecast?q=$city&units=metric&APPID=96993feca341f53ea0847433eae53af5";

            $forecast5days = new Forecast5days($url1, $step);
            // if($forecast5days->loadData())
            //  return 0;
            $forecast5days->showForecast();
            $dataPoints = $forecast5days->dataPoints();

            if ($step > 0) {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?city=' . $city . '&step=' . ($step - 1) . '"><div class="arrowLeft"></div></a>';
            }

            if ($step <= 30) {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?city=' . $city . '&step=' . ($step + 1) . '"><div class="arrowRight"></div></a>';
            }

            ?>
        </div>
    </div>

    <?php
    $myForecast = new myForecast($currentWeatherData->sunRiseSet(), $forecast5days->data());

    echo '<br>';
    $dataTime = $myForecast->dataTime();
    print_r($dataTime);
    $myForecast->startDayHour();
    $myForecast->endDayHour();
    $myForecast->myForecastData();
    $myForecast->showMyForecast();

    ?>

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
        }
    </script>

</body>

</html>