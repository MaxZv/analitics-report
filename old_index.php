<?php error_reporting(0);
const VISITORS_TOTAL_COUNT = 3749;
const CLICKS_COUNT = 545;

const VISITORS_COUNT_BEFORE = 3468;
const VISITORS_COUNT_CURRENT = 3313;

const CLICKS_COUNT_BEFORE = 520 ;
const CLICKS_COUNT_CURRENT = CLICKS_COUNT;

const AVG_TIME_BEFORE = '0:01:44';
const AVG_TIME_CURRENT = '0:01:44';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<?php
function status(){
    $status_array = [];
    $visitors_count_difference = round(VISITORS_COUNT_CURRENT * 100 / VISITORS_COUNT_BEFORE);
    $clicks_count_difference = round(CLICKS_COUNT_CURRENT * 100 / CLICKS_COUNT_BEFORE);
    if($visitors_count_difference > 100){
        $status_array['visitors_status']['difference'] = $visitors_count_difference - 100;
        $status_array['visitors_status']['decrease'] = false;
    }else{
        $status_array['visitors_status']['difference'] = 100 - $visitors_count_difference;
        $status_array['visitors_status']['decrease'] = true;
    }

    if($clicks_count_difference > 100){
        $status_array['clicks_status']['difference'] = $clicks_count_difference - 100;
        $status_array['clicks_status']['decrease'] = false;
    }else{
        $status_array['clicks_status']['difference'] = 100 - $clicks_count_difference;
        $status_array['clicks_status']['decrease'] = true;
    }
    return $status_array;
}

$status_array = status();

function xlsx_reader($file_name){
    require_once ('./Classes/PHPExcel.php');
    $data = [];
    $filepath = './reports/' . $file_name .'.xlsx';

    $type = PHPExcel_IOFactory::identify($filepath);
    $objReader = PHPExcel_IOFactory::createReader($type);

    $objPHPExcel = $objReader->load($filepath);

    $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
    foreach($rowIterator as $row){
        $cellIterator = $row->getCellIterator();
        foreach ($cellIterator as $cell) {
            $data[$row->getRowIndex()][] = $cell->getFormattedValue();
        }
    }
    $data = array_slice($data, 2);
    $data = array_slice($data, 5, -5);
    return $data;
}

function totalNumbers()
{

    $new_report_arr = [];
//    require_once './totalNumbers.php';
    $array = xlsx_reader('tu');
    $i = 0;
    foreach ($array as $arr) {
        $perc = $arr[2] * 100 / VISITORS_TOTAL_COUNT;
        $perc = round($perc, 1);
        $new_report_arr[$i]['Visitors'] = $arr[2];
        $new_report_arr[$i]['% of total'] = $perc . '%';
        $new_report_arr[$i]['URL'] = $arr[0];
        $new_report_arr[$i]['Device Category'] = $arr[1];
        $new_report_arr[$i]['BounceRate'] = $arr[3];
        $i++;
//        echo '<b>Number:</b> ' . $arr[1] . '; <b>Percentage:</b> ' . $perc . '%; <b>URL:</b> ' . $arr[0] . '; <b>Bounce:</b> ' . $arr[2] . '<br/>';
    }
    return $new_report_arr;
}

function clicks()
{
    $bounce_arr = xlsx_reader('bounce');

    $bounce_url = [];
    foreach ($bounce_arr as $br){
        array_push($bounce_url, $br[0]);
    }
//    require_once './clicks.php';
    $new_report_arr = [];
//    $clicks_arr = $array;
    $clicks_arr = xlsx_reader('clicks');


    $i = 0;
    foreach ($clicks_arr as $arr) {
        if(in_array($arr[0], $bounce_url)){
            foreach ($bounce_arr as $bounce) {
                if ($arr[0] == $bounce[0]) {
                    if($arr[1] == $bounce[1]) {
                        $perc = $arr[2] * 100 / CLICKS_COUNT;
                        $perc = round($perc, 1);
                        $new_report_arr[$i]['Clicks'] = $arr[2];
                        $new_report_arr[$i]['% of total'] = $perc . '%';
                        $new_report_arr[$i]['URL'] = $arr[0];
                        $new_report_arr[$i]['Device Category'] = $arr[1];
                        $new_report_arr[$i]['BounceRate'] = $bounce[2];
                        $i++;
//                echo '<b>Number:</b> ' . $arr[1] . '; <b>Percentage:</b> ' . $perc . '%; <b>URL:</b> ' . $arr[0] . '; <b>Bounce:</b> ' . $bounce[1] . '<br/>';
                    }else{
                        continue;
                    }
                }
            }
        }else{
            $perc = $arr[2] * 100 / CLICKS_COUNT;
            $perc = round($perc, 1);
            $new_report_arr[$i]['Clicks'] = $arr[2];
            $new_report_arr[$i]['% of total'] = $perc . '%';
            $new_report_arr[$i]['URL'] = $arr[0];
            $new_report_arr[$i]['Device Category'] = $arr[1];
            $new_report_arr[$i]['BounceRate'] = 0.0 . '%';
            $i++;
        }

    }
    return $new_report_arr;
}

function avgTime()
{
//    require_once './avgTime.php';
    $array = xlsx_reader('avg');

    $new_report_arr = [];
    $i = 0;
    foreach ($array as $arr) {
        $new_report_arr[$i]['Avg Time'] = $arr[2];
        $new_report_arr[$i]['URL'] = $arr[0];
        $new_report_arr[$i]['Device Category'] = $arr[1];
        $new_report_arr[$i]['BounceRate'] = $arr[3];
        $i++;
//        echo '<b>Avg Time:</b> ' . $arr[1] . '; <b>URL:</b> ' . $arr[0] . '; <b>Bounce:</b> ' . $arr[2] . '<br/>';
    }
    return $new_report_arr;
}

?>
<style>
    .container h2 {
        font-weight: bold;
    }
    table   {
        border-collapse: collapse;
        border: 1px solid black;
    }
    table  th, table  td {
        border: 1px solid black;
        padding: 5px;
        font-weight: normal;
    }

    table thead th {
        font-size: 16px !important;
        font-weight: bold;
    }
</style>
<div class="container" style="text-align: center">
    <div class="visitors">
        <h2>Number of visitors</h2>
        <div class="report">
            <table>
                <thead>
                <tr>
                    <th>Visitors</th>
                    <th>% of total</th>
                    <th>URL</th>
                    <th>Device Category</th>
                    <th>BounceRate</th>
                </tr>
                </thead>
                <?php
                $visitors = totalNumbers();
                foreach ($visitors as $item):?>
                    <tr>
                        <th><?= $item['Visitors']?></th>
                        <th><?= $item['% of total']?></th>
                        <th><?= $item['URL']?></th>
                        <th><?= $item['Device Category']?></th>
                        <th><?= $item['BounceRate']?></th>
                    </tr>
                <?php endforeach;?>
            </table>
        </div>
    </div>
    <br/>
    <div class="clicks">
        <h2>ChatNow clicks</h2>
        <div class="report">
            <table>
                <thead>
                <tr>
                    <th>Clicks</th>
                    <th>% of total</th>
                    <th>URL</th>
                    <th>Device Category</th>
                    <th>BounceRate</th>
                </tr>
                </thead>
                <?php
                $clicks = clicks();
                foreach ($clicks as $item):?>
                    <tr>
                        <th><?= $item['Clicks']?></th>
                        <th><?= $item['% of total']?></th>
                        <th><?= $item['URL']?></th>
                        <th><?= $item['Device Category']?></th>
                        <th><?= $item['BounceRate']?></th>
                    </tr>
                <?php endforeach;?>
            </table>
        </div>
    </div>
    <br/>
    <div class="avg-time">
        <h2>Average time spent</h2>
        <div class="report">
            <table>
                <thead>
                <tr>
                    <th>Avg Time</th>
                    <th>URL</th>
                    <th>Device Category</th>
                    <th>BounceRate</th>
                </tr>
                </thead>
                <?php
                $avg_time = avgTime();
                foreach ($avg_time as $item):?>
                    <tr>
                        <th><?= $item['Avg Time']?></th>
                        <th><?= $item['URL']?></th>
                        <th><?= $item['Device Category']?></th>
                        <th><?= $item['BounceRate']?></th>
                    </tr>
                <?php endforeach;?>
            </table>


        </div>
    </div>
</div>

<div class="status" style="width: auto">
    <p>
        compared to the previous day:
    </p>
    <ul>
        <li>number of users (<?php echo $status_array['visitors_status']['decrease'] ? 'decreased' : 'increased'?> by about <?= $status_array['visitors_status']['difference'];?>%)</li>
        <li>chatNow clicks (<?php echo $status_array['clicks_status']['decrease'] ? 'decreased' : 'increased'?> by about <?= $status_array['clicks_status']['difference'];?>%)</li>
        <li>avgTime on all pages (without significant changes)</li>
    </ul>
</div>
</body>
</html>