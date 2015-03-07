<?php

function get($url)
{
//try {
//	$content = file_get_contents('http://www.figocom.com/');
//}
//catch (Exception $e) {
//	echo 'Caught exception: ',  $e->getMessage(), "\n";
//}

	if (IS_SAE) {
		$f = new SaeFetchurl();
		$content = $f->fetch($url);
		if ($content === false) {
			$data['status'] = $f->errno();
			$data['info'] = $f->errno().' - '.$f->errmsg();
		} else {
			$data['status'] = 0;
			$data['data'] = $content;
		}
	} else {
		$content = @file_get_contents($url);
		$error = error_get_last();
		if (!empty($error)) {
//			var_dump($error);
//			record(1, $_GET['bus'], 2, $error);
//			res(array('status'=>2,'info'=>'获取数据错误'));
			$data['status'] = 2;
			$data['info'] = '获取数据错误';
		} else {
			$data['status'] = 0;
			$data['data'] = $content;
		}
	}
	return $data;
}

//function get($url, $search, $type)
//{
//	$data = fetchurl($url);
//	if ($data['error'] != 0) {
//		record($type, $search, 2, $data['msg']);
//		res(array('status'=>2, 'info'=>'获取数据错误'));
//	} else {
//		return $data;
//	}
//}

function res($res)
{
	header('Content-type: text/html; charset=utf-8');//json
	exit(json_encode($res));
}

function record($type, $search = '', $status = '', $content = '')
{
	$now = time();
	$dbtable = "hjh_log";
	$ip = $_SERVER['REMOTE_ADDR'];
	$ua = $_SERVER['HTTP_USER_AGENT'];

	$mysql = new SaeMysql();
	/**
	 * type没用:0 不区分
	 *          1 http://gzbusnow.sinaapp.com/index.php?c=busrunningv2&a=query&keyword=
	 *          //2 http://555.gzbusnow.sinaapp.com/index.php?c=busrunningv2&a=query&keyword=
	 * search:  即搜索的keyword
	 * status:  参照error()
	 */
	$mysql->runSql("INSERT INTO {$dbtable} (time, ip, ua, type, search, status, content) VALUES ('$now', '$ip', '$ua', '$type', '$search', '$status', '$content')");
//	if ($mysql->errno() != 0) echo $mysql->errmsg();
	$mysql->closeDb();
}

function error($str)
{
	/**
	 * 0* 不区分搜索bus时出现的错误
	 * 1* 搜索bus
	 * 2* 搜索station
	 */
	$status = array(
		'SEARCH_NULL' => array(1, '参数错误'),
		'CURL_ERROR' => array(2, '获取数据错误'),
		'DATA_NULL' => array(3, '获取数据失败，请稍后再试'),

		'BUS_NOT_EXIST' => array(11, '此线路不存在'),
	);
	$error = isset($status[$str]) ? array('status'=>$status[$str][0],'info'=>$status[$str][1])
		: array('status'=>99, 'info'=>'未知错误');
	return $error;
}


if (empty($_GET['bus'])) {
	$error = error('SEARCH_NULL');
	record(1, '', $error['status'], $error['info']);
	res($error);
} else {
	$bus = $_GET['bus'];
}
$data = array();
//$data['now'] = date('Y-m-d H:i:s');

$response = get('http://gzbusnow.sinaapp.com/index.php?c=busrunningv2&a=query&keyword='.$bus);
if ($response['status'] != 0) {
	$error = error('CURL_ERROR');
	record(1, $_GET['bus'], $error['status'], $response['info']);
	res($error);
}
$content = $response['data'];

$html = explode('bus_status_block', $content);
if (count($html) == 1) {
	if (strpos($content, '不存在此线路') !== false) {
		$error = error('BUS_NOT_EXIST');
		record(1, $_GET['bus'], $error['status'], $error['info']);
		res($error);
	} else {
		$error = error('DATA_NULL');
		record(1, $_GET['bus'], $error['status'], $content);

		preg_match('/；(\d+)秒后/is', $content, $match);
		empty($match[1]) OR $error['data']['CD'] = $match[1];
		res($error);
	}
} else {
	record(1, $_GET['bus'], 0, '');//TODO 成功是否需要记录？
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
res($res);

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
