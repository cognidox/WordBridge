<?php

require_once('libraries/movabletypeClass/class.wpclient.php');

$uid = 'cognidoxsupport';
$pass = 'dsf3jsLisw3gq';
$host = 'cognidox.wordpress.com';
$path = '/xmlrpc.php';
$blogid = 11726516;
$postid = 155;

$blogname = 'cognidox';
$wpclient = new wpclient($uid, $pass, $host, $path);
$res = $wpclient->getPost($postid);
//$res = $wpclient->getCategories($blogid);
//$res = $wpclient->getTags($blogid);
//$res = $wpclient->getPageList($blogid);

var_dump($res);

?>
