<?php
/**
 * Verifying whether the ticket is valid
 */
require 'functions.php';

$ret = ['ticket_verify'=>false, 'user_name'=>'', 'msg'=>'Invalid Access'];

/* getting app-domain ST */
$headers = getallheaders();

if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer') === 0) {
// if (isset($_POST)) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->auth('admin');
    $redis->select(1);

    $ticket = substr($headers['Authorization'], 7);
    //$ticket = $_POST['ticket'];

    $username = $redis->exists($ticket) ? $redis->get($ticket) : null;
    if ($username) {
        $ret = ['ticket_verify'=>true, 'user_name'=>$username, 'msg'=>'OK'];
        //验证后销毁这个ticket
        $redis->del($ticket);
        echo json_encode($ret);
        exit();
    }
    $ret['msg']='Authorization Error';
}
echo json_encode($ret);


