<?php

require_once('libraries/movabletypeClass/class.wpclient.php');

$uid = 'cognidoxsupport';
$pass = 'dsf3jsLisw3gq';
$host = 'cognidox.wordpress.com';
$path = '/xmlrpc.php';

$blog = new wpclient($uid, $pass, $host, $path);
$blogid = 11726516;
$postid = 117265160000155;

$res = $blog->getPost($postid);

var_dump($res);
?>
