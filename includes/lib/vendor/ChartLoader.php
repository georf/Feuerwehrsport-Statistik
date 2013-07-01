<?php

include_once (__DIR__.'/pChart/class/pData.class.php');
include_once (__DIR__.'/pChart/class/pDraw.class.php');
include_once (__DIR__.'/pChart/class/pImage.class.php');
include_once (__DIR__.'/pChart/class/pStock.class.php');
include_once (__DIR__.'/pChart/class/pPie.class.php');
include_once (__DIR__.'/pChart/class/pCache.class.php');


class ChartLoader
{
    public function __construct()
    {
        define('PCHARTDIR', __DIR__.'/pChart/');
    }


    public static function discipline2text($id, $sex)
    {
        $legend = 'Hakenleitersteigen';
        if ($id != 1) {
          if ($sex == 'male') {
            $legend = 'Hindernisbahn - Männer';
          } else {
            $legend = 'Hindernisbahn - Frauen';
          }
        }
        return $legend;
    }


    public static function stockChart($data, $legend = '', $title = '', $debug = false)
    {

        if ($debug) print_r($data);

        $MyData = new pData();

        foreach ($data as $name => $list) {
            $MyData->addPoints($list, $name);
        }

        if (isset($data['labels'])) {
            $MyData->setAbscissa('labels');
        }

        if ($title !== '') {
            $title .= ' - ';
        }

        $w = 700;
        $h = 400;
        $title .= 'Zeiten in Quartile - Ort';
        $title .= '          '.$legend;

        /* Create the pChart object */
        $myPicture = new pImage($w, $h, $MyData);

        /* Turn of Antialiasing */
        $myPicture->Antialias = FALSE;

        /* Draw the background #9FC5EE */
        $Settings = array("R"=>169, "G"=>217, "B"=>238);
        $myPicture->drawFilledRectangle(0, 0, $w, $h, $Settings);

        $Settings = array(
          "StartR"=>159, "StartG"=>197, "StartB"=>238,
          "EndR"=>133, "EndG"=>184, "EndB"=>238,
          "Alpha"=>80
        );
        $myPicture->drawGradientArea(0, 0, $w, 20,DIRECTION_VERTICAL, $Settings);

        /* Add a border to the picture #87A8CC*/
        $myPicture->drawRectangle(0, 0, $w-1, $h-1,array("R"=>135, "G"=>168, "B"=>204));

        /* Write the chart title */
        $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/calibri.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255));
        $myPicture->drawText(10, 18, $title,array("FontSize"=>11,"Align"=>TEXT_ALIGN_BOTTOMLEFT));

        /* Set the default font */
        $myPicture->setFontProperties(array("FontName"=>PCHARTDIR."fonts/UbuntuMono-R.ttf","FontSize"=>7,"R"=>0,"G"=>0,"B"=>0));

        /* Define the chart area */
        $myPicture->setGraphArea(40,30,660,340);

        /* Draw the scale */
        $scaleSettings = array(
          "XMargin"=>10,
          "YMargin"=>10,
          "Floating"=>TRUE,
          "GridR"=>200,
          "GridG"=>200,
          "GridB"=>200,
          "DrawSubTicks"=>TRUE,
          "CycleBackground"=>TRUE,
          "LabelRotation" => 90
        );
        $myPicture->drawScale($scaleSettings);

        /* Turn on Antialiasing */
        $myPicture->Antialias = TRUE;

        /* Enable shadow computing */
        $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

        /* Create the pStock object */
        $mystockChart = new pStock($myPicture,$MyData);

        /* Draw the stock chart */
        $stockSettings = array(
            "BoxUpR"=>255,
            "BoxUpG"=>255,
            "BoxUpB"=>255,
            "BoxDownR"=>255,
            "BoxDownG"=>255,
            "BoxDownB"=>255,
            "SerieMedian"=>"Median");
        $mystockChart->drawStockChart($stockSettings);


        $stockLists = array('Open', 'Close', 'Max', 'Min', 'Median');
        foreach ($data as $name => $list) {
            if (in_array($name, $stockLists)) {
                $MyData->setSerieDrawable($name, FALSE);
            } else {
                $MyData->setSerieDrawable($name, TRUE);
            }
        }

        /* Draw the line chart */
        $myPicture->drawLineChart(array(
            "BreakVoid" => false
        ));
        $myPicture->drawPlotChart(array("PlotSize"=>1,"DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-50,"BorderAlpha"=>80));

        /* Write the chart legend */
        $myPicture->drawLegend(500,10,array(
          "Style"=>LEGEND_NOBORDER,
          "Mode"=>LEGEND_HORIZONTAL,
          "FontR"=>255,"FontG"=>255,"FontB"=>255,
          "FontName"=>PCHARTDIR."fonts/calibri.ttf",
          "FontSize"=>10
        ));

        if ($debug) die();
        /* Render the picture (choose the best way) */
        $myPicture->autoOutput("pictures/example.drawLineChart.plots.png");
    }
}
