<?php

require_once('config.php');
require_once('common.php');

if (isset($_GET['bus'])) {
	require_once('bus.php');
} else {
	record(0, '', 0, '直接登录网站首页');
	$error = error('SEARCH_NULL');
//	record(1, '', $error['status'], $error['info']);
	response($error);
}