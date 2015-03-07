<?php

error_reporting(0);
//@set_time_limit(0);//SAE里无效
date_default_timezone_set("Asia/Shanghai");
header('Content-type: text/html; charset=utf-8');//json

define('IS_SAE', true);
define('LOG_TABLE', "hjh_log");
//$Config['SAE'] = false;