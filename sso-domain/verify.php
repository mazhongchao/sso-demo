<?php
/**
 * Verifying whether the ticket is valid
 */

require 'functions.php';

$headers = getallheaders();
if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer') === 0) {
    $ticket = substr($headers['Authorization'], 7);
}
else {
    echo "Invalid Access";
    exit();
}

$ret = ['ticket_verify' => false, 'user'=>''];

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('admin');
$redis->select(1);
$username = $redis->exists($ticket) ? $redis->get($ticket) : null;
if ($username) {
    $sess = explode('~', $session_string);
    $user = $sess[0] ?? '';
    if($user){
        $app_sessionid = substr(md5(substr($session_string, 1,19)),1,16);
        $ret = ['ticket_verify'=>true, 'user'=>$username];

        //TODO：注册子系统地址：用ticket作为key 子系统地址作为value(set) 存redis

        echo json_encode($ret);
        exit();
    }
}
echo json_encode($ret);


