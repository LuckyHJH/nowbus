<?php

//获取网页内容
function get($url)
{
//try {
//	$content = file_get_contents('http://www.figocom.com/');
//}
//catch (Exception $e) {
//	echo 'Caught exception: ',  $e->getMessage(), "\n";
//}

	if (0) {//IS_SAE
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
			$data['info'] = json_encode($error);
		} else {
			$data['status'] = 0;
			$data['data'] = $content;
		}
	}
	return $data;
}

function response($res)
{
	exit(json_encode($res));
}

function record($type, $search = '', $status = '', $content = '')
{
	$now = time();
	$dbtable = LOG_TABLE;
	$ip = $_SERVER['REMOTE_ADDR'];
	$ua = $_SERVER['HTTP_USER_AGENT'];

	/**
	 * type没用:0 不区分
	 *          1 http://gzbusnow.sinaapp.com/index.php?c=busrunningv2&a=query&keyword=
	 *          //2 http://555.gzbusnow.sinaapp.com/index.php?c=busrunningv2&a=query&keyword=
	 * search:  即搜索的keyword
	 * status:  参照error()
	 */
	if (IS_SAE) {//IS_SAE
		$mysql = new SaeMysql();
		$mysql->runSql("INSERT INTO {$dbtable} (time, ip, ua, type, search, status, content) VALUES ('$now', '$ip', '$ua', '$type', '$search', '$status', '$content')");
//	if ($mysql->errno() != 0) echo $mysql->errmsg();
		$mysql->closeDb();
	} else {
		printf('%s - %s - %s - %s', $type, $search, $status, $content);
	}
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
		'CURL_ERROR' => array(2, '获取数据失败'),
		'DATA_NULL' => array(3, '服务繁忙，请稍后再试'),

		'BUS_NOT_EXIST' => array(11, '此线路不存在'),
	);
	$error = isset($status[$str]) ? array('status'=>$status[$str][0],'info'=>$status[$str][1])
		: array('status'=>99, 'info'=>'未知错误');
	return $error;
}
