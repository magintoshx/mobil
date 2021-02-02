<?php
$db_error = "Veritabanı işlemi sırasında hata oluştu. Lütfen daha sonra tekrar deneyiniz.";
$user_error = "Bu işlem için yetkiniz bulunmuyor. Lütfen tekrar deneyiniz.";
$hepsi_doldur = "Lütfen tüm alanları doldurunuz";
$biisurl = "https://biis.com.tr/";


function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_my_car_type($id){
	global $db;
	return mysqli_fetch_assoc(mysqli_query($db,"select car_type from driver_list where id='$id' limit 1"))["car_type"];
}
function system_keys($key){
    global $db;
    $m = mysqli_query($db,"select ".$key." from sys_keys limit 1");
    return mysqli_fetch_assoc($m)[$key];
}
function mailGonder($template, $to, $type)
{
    require("app/inc/mail/src/Exception.php");
    require("app/inc/mail/src/PHPMailer.php");
    require("app/inc/mail/src/SMTP.php");
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = system_keys("temp_mail_secure");
    $mail->Host = system_keys("temp_mail_host");
    $mail->Port = system_keys("temp_mail_port");
    $mail->IsHTML(true);
    $mail->SetLanguage("tr", "phpmailer/language");
    $mail->CharSet = "utf-8";
    $mail->Username = system_keys("temp_mail_adress");
    $mail->Password = system_keys("temp_mail_password");
    $mail->SetFrom(system_keys("temp_mail_adress"), "Biis");
    $mail->AddAddress($to);
    $mail->Subject = "Şifre sıfırlama bağlantısı";
    $mail->Body = $template;
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    if (!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}

function uye_olmali()
{
    return;
}

function uye_olmamali()
{
    return;
}

function temizle($data)
{
    return $data;
}

function getDriverRate($id){
	global $db;
	$sofor = mysqli_fetch_assoc(mysqli_query($db,"select avg(star) as rate from travel_lists where driver_id='$id' and (status=2 or status=(-1)) and star>0"));
	return intval(round($sofor["rate"]));
}
function sendNotifySingle($deviceid,$baslik,$text){

	$content = array(
            "en" => $text
            );

        $fields = array(
            'app_id' => "841a45f9-2721-47f3-8da7-ac589721231a",
            'include_player_ids' => array($deviceid),
            'headings' => array("en" => $baslik),
            'contents' => $content
        );

        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                   'Authorization: Basic MDQ5ZGVmZDMtNTRiNS00OTJlLTg5YzgtOGZlNzNlZmIyOWRm'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

	/*
	$data_string = '{
  "app_id": "841a45f9-2721-47f3-8da7-ac589721231a",
  "contents": {
    "en": "'.$text.'"
  },
  "headings": {
    "en": "'.$baslik.'"
  },
  "data": {},
  "include_player_ids": [
    "'.$deviceid.'"
  ]
}';

$ch = curl_init('https://onesignal.com/api/v1/notifications');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);

$result = json_decode(curl_exec($ch));*/
}
function sendNotifySingleSound($deviceid,$baslik,$text){

	 $content = array(
            "en" => $text
            );

        $fields = array(
            'app_id' => "c9e745e5-e8c7-46dc-b03d-ab3386f9dbdd",
            'include_player_ids' => array($deviceid),
            'headings' => array("en" => $baslik),
            'contents' => $content
        );

        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                   'Authorization: Basic MTYzY2E4NjgtMjY3ZC00ODk1LWI4YTAtNzI3MDc0MjNmZTA5'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
}

function sendNotifySingleSoundUsers($deviceid,$baslik,$text){

	 $content = array(
            "en" => $text
            );

        $fields = array(
            'app_id' => "12e688ca-4cc1-435f-9d59-e743f3e98c11",
            'include_player_ids' => array($deviceid),
            'headings' => array("en" => $baslik),
            'contents' => $content
        );

        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                   'Authorization: Basic NjIzYmFmZWItNjg3Ny00ODNjLTlkY2YtMGY3MGVhZDdjZjcx'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
}

function getOneId($id){
	global $db;
	$cek = mysqli_query($db,"select oneid from users where id='$id' limit 1");
	if(mysqli_num_rows($cek)<1){
		return false;
	}else{
		return mysqli_fetch_assoc($cek)["oneid"];
	}
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
}

function sortByDistance($a, $b)
{
    $a = $a['uzaklik'];
    $b = $b['uzaklik'];
    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
}

function mailKontrol($mail)
{
    return filter_var($mail, FILTER_VALIDATE_EMAIL);
}
