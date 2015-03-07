<?php

//header('Content-type: text/html; charset=utf-8');//json
if (empty($_GET['bus'])) {exit(json_encode(array('status'=>1,'info'=>'请输入线路名称')));}
else {$bus = urlencode($_GET['bus']);}
date_default_timezone_set("Asia/Shanghai");
$data = array();
$data['now'] = date('Y-m-d H:i:s');
//$content = file_get_contents('http://gzbusnow.sinaapp.com/index.php?c=busrunningv2&a=query&keyword='.$bus);
$content = file_get_contents('busrunning.html');
$html = explode('bus_status_block',$content);
if (count($html) == 1) {
	if (strpos($content, '不存在此线路') !== false)
		exit(json_encode(array('status'=>2,'info'=>'此线路不存在')));
	else
		exit(json_encode(array('status'=>3,'info'=>'获取数据失败，请稍后再试')));
}

//获取路线名称
//preg_match('/name=\"keyword\" value=\"(.+?)\"/is', $html[0], $match);
//$data['bus'] = $match[1];
//获取更新时间
preg_match('/；(\d+)秒后/is', $html[0], $match);
$data['CD'] = $match[1];



$result = $bus = array();

//获取总站和时间
//preg_match('/<div class=\"bus_direction\">(.+?):(.+?)（(.+?)；.+?<\/div>/is', $html[1], $match);
//$bus['name'] = $match[1];
//$bus['place'] = $match[2];
//$bus['time'] = $match[3];
preg_match('/<div class=\"bus_direction\">(.+?):(.+?)-(.+?)（(.+?)；.+?<\/div>/is', $html[1], $match);
$bus['name'] = trim($match[1]);
$bus['from'] = trim($match[2]);
$bus['to'] = trim($match[3]);
$bus['time'] = trim($match[4]);

//获取上行到站情况
preg_match_all('/<td>\s*?(.*?)\s*?<\/td>/i', $html[1], $match);
foreach ($match[1] as $k=>$v) {
	$result[$k]['status'] = trim($v);
//	echo htmlspecialchars(trim($v)).'<br>------------------<br>';
}
//获取上行站名（中文）
preg_match_all('/<td>([^\s]*?)&nbsp\;/is', $html[1], $match);
foreach ($match[1] as $k=>$v) {
	$result[$k]['station'] = $v;
}
//获取上行站名（urlencode）
//preg_match_all('/keyword=(.*?)\"/i', $html[1], $match);
//foreach ($match[1] as $k=>$v) {
//	$result[$k]['url'] = trim($v);
//}
$bus['result'] = $result;
$data['up'] = $bus;



$result = $bus = array();

//获取总站和时间
//preg_match('/<div class=\"bus_direction\">(.+?):(.+?)（(.+?)；.+?<\/div>/is', $html[2], $match);
//$bus['name'] = $match[1];
//$bus['place'] = $match[2];
//$bus['time'] = $match[3];
preg_match('/<div class=\"bus_direction\">(.+?):(.+?)-(.+?)（(.+?)；.+?<\/div>/is', $html[2], $match);
$bus['name'] = trim($match[1]);
$bus['from'] = trim($match[2]);
$bus['to'] = trim($match[3]);
$bus['time'] = trim($match[4]);

//获取下行到站情况
preg_match_all('/<td>\s*?(.*?)\s*?<\/td>/i', $html[2], $match);
foreach ($match[1] as $k=>$v) {
	$result[$k]['status'] = trim($v);
}
//获取下行站名
preg_match_all('/<td>([^\s]*?)&nbsp\;/is', $html[2], $match);
foreach ($match[1] as $k=>$v) {
	$result[$k]['station'] = $v;
}
$bus['result'] = $result;
$data['down'] = $bus;



$res['status'] = 0;
$res['info'] = '';
$res['data'] = $data;
echo json_encode($res);

exit;

$content = file_get_contents('station.html');
//获取站名
preg_match('/<p><b>(.+?)<\/b>/is', $content, $match);
var_dump($match[1]);


//$str1='321';
//$str2='123';
//$res=similar_text($str1,$str2,$per);
//var_dump($res);
//var_dump($per);
