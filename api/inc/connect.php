<?php
error_reporting(0);
$db = mysqli_connect("localhost","u9540912_vip","istanbul1453","u9540912_vip");
mysqli_set_charset($db,"utf8");
if (mysqli_connect_errno())
  {
 $json = array('status'=>0, 'message'=>'Veritabanı hatası');
		echo json_encode($json);
		exit;
  }