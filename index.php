<?php error_reporting(0);

const AVG_TIME_BEFORE = '0:01:44';
const AVG_TIME_CURRENT = '0:01:29';

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

function xlsx_reader($dir_name, $file_name){
    require_once ('./Classes/PHPExcel.php');
    $data = [];
    $filepath = './'. $dir_name .'/' . $file_name .'.xlsx';

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

function totalNumbers($dir_name)
{
    //    require_once './totalNumbers.php';
    $array = xlsx_reader($dir_name,'tu');
    $i = 1;
    $y = 1;
    $mobileTotalBounce = 0;
    $mobileTotalVisitors = 0;
    $desktopTotalBounce = 0;
    $desktopTotalVisitors = 0;
    foreach ($array as $arr) {
        if($i > 10 && $y > 10) break;
        if($arr[1] == 'mobile' || $arr[1] == 'tablet'){
            if($i > 10) continue;
            $mobileTotalBounce += $arr[3];
            $mobileTotalVisitors += $arr[2];
            $i++;
        }else{
            if($y > 10) continue;
            $desktopTotalBounce += $arr[3];
            $desktopTotalVisitors += $arr[2];
            $y++;
        }
    }
    $reportVisitors = [
            'mobile' => [
                'avgBounce' => round($mobileTotalBounce / $i, 1),
                'avgVisitors' => round($mobileTotalVisitors / $i, 1)
            ],
            'desktop' => [
                'avgBounce' => round($desktopTotalBounce / $i, 1),
                'avgVisitors' => round($desktopTotalVisitors / $i, 1)
            ]
    ];
  return $reportVisitors;
}

function clicks($dir_name)
{
    $bounce_arr = xlsx_reader($dir_name, 'bounce');
    $bounce_url = [];
    foreach ($bounce_arr as $br){
        array_push($bounce_url, $br[0]);
    }
    $clicks_arr = xlsx_reader($dir_name, 'clicks');

    $mobileTotalBounce = 0;
    $mobileTotalClicks = 0;
    $desktopTotalBounce = 0;
    $desktopTotalClicks = 0;
    $i = 1;
    $y = 1;
    foreach ($clicks_arr as $arr) {
        if(in_array($arr[0], $bounce_url)){
            foreach ($bounce_arr as $bounce) {
                if ($arr[0] == $bounce[0]) {
                    if($arr[1] == $bounce[1]) {
                        if($i > 10 && $y > 10) break;
                        if($arr[1] == 'mobile' || $arr[1] == 'tablet') {
                            if($i > 10) continue;
                            $mobileTotalBounce += $bounce[2];
                            $mobileTotalClicks += $arr[2];
                            $i++;
                        }else{
                            if($y > 10) continue;
                            $desktopTotalBounce += $bounce[2];
                            $desktopTotalClicks += $arr[2];
                            $y++;
                        }
                    }else{
                        continue;
                    }
                }
            }
        }else{
            if($arr[1] == 'mobile' || $arr[1] == 'tablet') {
                if($i > 10) continue;
                $mobileTotalBounce += 0;
                $mobileTotalClicks += $arr[2];
                $i++;
            }else{
                if($y > 10) continue;
                $desktopTotalBounce += 0;
                $desktopTotalClicks += $arr[2];
                $y++;
            }
        }

    }
    $reportClicks = [
            'mobile' => [
                'avgBounce' => round($mobileTotalBounce / 10, 1),
                'avgClicks' => round($mobileTotalClicks / 10, 1)
            ],
            'desktop' => [
                'avgBounce' => round($desktopTotalBounce / 10, 1),
                'avgClicks' => round($desktopTotalClicks / 10, 1)
            ]
    ];
    return $reportClicks;
}

function avgTime($dir_name)
{
    $array = xlsx_reader($dir_name, 'avg');

    $i = 0;
    $y = 0;
    $mobileTotalAVG = 0;
    $desktopTotalAVG = 0;
    foreach ($array as $arr) {
        $str_arr = explode(':', $arr[2]);
        $seconds = $str_arr[0] * 3600 + $str_arr[1] * 60 + $str_arr[2];
        if($i > 10 && $y > 10) break;
        if($arr[1] == 'mobile' || $arr[1] == 'tablet'){
            if($i > 10) continue;
            $mobileTotalAVG += $seconds;
            $i++;
        }else{
            if($y > 10) continue;
            $desktopTotalAVG += $seconds;
            $y++;
        }
    }
    $reportAvgTime = [
        'mobile' => [
            'totalAvgTime' => round($mobileTotalAVG / 10, 0),
        ],
        'desktop' => [
            'totalAvgTime' => round($desktopTotalAVG / 10, 0),
        ]
    ];
    return $reportAvgTime;
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
        </div>
    </div>
</div>

<div class="status" style="width: auto">
    comparing day before with current day:
    <ul>
        <li>top 10 pages of amount of visitors, clicks (chatNow)</li>
        <li>avg time of all visited pages</li>
    </ul>
    <h1><b>Desktop</b></h1>
    <h2>Visitors</h2>
    <ul>
        <li>average amount of visitors: (Before: <?= totalNumbers('Before')['desktop']['avgVisitors']?>) -> (Current: <?= totalNumbers('Current')['desktop']['avgVisitors']?>)</li>
        <li>average bounce rate: (Before: <?= totalNumbers('Before')['desktop']['avgBounce']?>%) -> (Current: <?= totalNumbers('Current')['desktop']['avgBounce']?>%)</li>
    </ul>
    <h2>Clicks</h2>
    <ul>
        <li>average amount of clicks: (Before: <?= clicks('Before')['desktop']['avgClicks']?>) -> (Current: <?= clicks('Current')['desktop']['avgClicks']?>)</li>
        <li>average bounce rate: (Before: <?= clicks('Before')['desktop']['avgBounce']?>%) -> (Current: <?= clicks('Current')['desktop']['avgBounce']?>%)</li>
    </ul>

   <h1><b>Mobile</b></h1>
    <h2>Visitors</h2>
    <ul>
        <li>average amount of visitors: (Before: <?= totalNumbers('Before')['mobile']['avgVisitors']?>) -> (Current: <?= totalNumbers('Current')['mobile']['avgVisitors']?>)</li>
        <li>average bounce rate: (Before: <?= totalNumbers('Before')['mobile']['avgBounce']?>%) -> (Current: <?= totalNumbers('Current')['mobile']['avgBounce']?>%)</li>
    </ul>
    <h2>Clicks</h2>
    <ul>
        <li>average amount of clicks: (Before: <?= clicks('Before')['mobile']['avgClicks']?>) -> (Current: <?= clicks('Current')['mobile']['avgClicks']?>)</li>
        <li>average bounce rate: (Before: <?= clicks('Before')['mobile']['avgBounce']?>%) -> (Current: <?= clicks('Current')['mobile']['avgBounce']?>%)</li>
    </ul>

    <h2><b>Average time on all visited pages</b></h2>
    <ul>
        <li>(Before: <?= AVG_TIME_BEFORE ?>) -> (Current: <?= AVG_TIME_CURRENT ?> )</li>
    </ul>
</div>
</body>
</html>