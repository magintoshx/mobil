<?php
require_once("app/inc/connect.php");
require_once("app/inc/data.php");
require_once("app/inc/gump/gump.class.php");
uye_olmali();

    switch ($input["s"]) {
        case 1:
			$userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			$list = null;
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}else{
				$sql = mysqli_query($db,"select * from message_list where driver_id='$userid'");
				while($ssql = mysqli_fetch_assoc($sql)){
					$ok = mysqli_fetch_assoc(mysqli_query($db,"select * from message_data where ml_id='".$ssql["id"]."' order by id desc limit 1"));
					if(!$ok){
						mysqli_query($db,"delete from message_list where id='".$ssql["id"]."' limit 1");
					}else{
						$ssql["sonmesaj"] = $ok;
						$driver = mysqli_fetch_assoc(mysqli_query($db,"select * from users where id='".$ssql["user_id"]."' limit 1"));
						$ssql["driver"] = $driver;
						$list[] = $ssql;
					}
				}
				$json = array('status' => 1, 'message' => $list);
			}
			echo json_encode($json);
            break;
        case 2:
			$userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			$id = temizle($input["id"]);
			$list = null;
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Sohbet bilgisine ulaşılamadı");
			}else{
				$mlist = mysqli_query($db,"select * from message_list where id='$id' and driver_id='$userid' limit 1");
				if(mysqli_num_rows($mlist)>0){
					$sql = mysqli_query($db,"delete from message_data where ml_id='$id'");
					if($sql){
						$sil = mysqli_query($db,"delete from message_list where id='$id' and driver_id='$userid' limit 1");
						if($sil){
							$json = array('status' => 1, 'message' => "Sohbet başarıyla silindi");
						}else{
							$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
						}
					}else{
						$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
				}else{
					$json = array('status' => 0, 'message' => "Yetkisiz işlem gerçekleştirdiniz");
				}

			}
			echo json_encode($json);
            break;
		case 3:
			$userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			$id = temizle($input["id"]);
			$list = array();
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Sohbet bilgisine ulaşılamadı");
			}else{
				$sql = mysqli_query($db,"select id as _id, text as text,
					created_at as createdAt, user_id, send_user_type
				from message_data where ml_id='$id' order by id desc");
				if(mysqli_num_rows($sql)>0){
					while($oku = mysqli_fetch_assoc($sql)){
						if($oku["send_user_type"] == 0){
							$us = mysqli_fetch_assoc(mysqli_query($db,"select id as _id, name as name, avatar as avatar from users where id = '{$oku["user_id"]}'"));
						}else{
							$us = mysqli_fetch_assoc(mysqli_query($db,"select id as _id, name as name, avatar as avatar from driver_list where id = '{$oku["user_id"]}'"));
						}
						if($us["avatar"]){
							$us["avatar"] = "http://www.vipkoin.com/taksi/app/data/img/".$us["avatar"];
						}else{
							$us["avatar"] = "http://www.vipkoin.com/taksi/app/data/img/noavatar.png";
						}
						$oku["user"] = $us;
						$list[] = $oku;
					}
				}else{
					$list = array();
				}
					$json = array('status' => 1, 'message' => $list);
			}
			echo json_encode($json);
            break;
		break;
       case 4:
	        $userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			$id = temizle($input["id"]);
			$msg = temizle(trim($input["msg"]));
			$list = null;
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Sohbet bilgisine ulaşılamadı");
			}elseif(empty($msg)){
				$json = array('status' => 0, 'message' => "Lütfen mesajınızı yazınız");
			}else{
				$sql = mysqli_query($db,"select * from message_list where id='$id' and driver_id='$userid' limit 1");
				if(mysqli_num_rows($sql)>0){
					$ekle = mysqli_query($db,"insert into message_data set ml_id='$id', user_id='$userid', text='$msg', send_user_type=1");
					if($ekle){
						$oku = mysqli_fetch_assoc($sql);
						if($oku["user_id"]){
							$cek = mysqli_query($db,"select * from users where id='{$oku["user_id"]}' limit 1");
							if(mysqli_num_rows($cek)>0){
								$ver = mysqli_fetch_assoc($cek);
								$data_string = '{ "app_id": "8c9c639a-20d6-438e-a267-a4a042a4e3fd",
									"contents": {
									"en": "Sürücümüz '.$ver["name"].' yeni mesaj gönderdi: '.$msg.'"
									},
									"headings": {
									"en": "Yeni Mesaj Bilgisi"
									},
									"data": {},
									"include_player_ids": [
									"'.$ver["one_key"].'"
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
									$gonderildi = json_decode(curl_exec($ch));
							}
						}



						$json = array('status' => 1, 'message' => "Mesaj başarıyla gönderildi");
					}else{
						$json = array('status' => 0, 'message' => "Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.");
					}
				}else{
					$json = array('status' => 0, 'message' => "Yetkisiz işlem gerçekleştirdiniz");
				}
			}
			echo json_encode($json);
	   break;
	   	case 5:
	        $userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			$id = temizle($input["id"]);
			$list = null;
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Kullanıcı bilgisine ulaşılamadı");
			}else{
				$idinu = mysqli_fetch_assoc(mysqli_query($db,"select user_id from travel_lists where id='$id' limit 1"));
				$sql = mysqli_query($db,"select id from message_list where user_id='{$idinu["user_id"]}' and driver_id='$userid' limit 1");
				if(mysqli_num_rows($sql)>0){
					$oku = mysqli_fetch_assoc($sql)["id"];
					$list=$oku;
				}else{
					$ekle = mysqli_query($db,"insert into message_list set driver_id='$userid', user_id='{$idinu["user_id"]}'");
					if($ekle){
						$lastid = mysqli_insert_id($db);
						$list=$lastid;
					}
				}
				$json = array('status' => 1, 'message' => $list);
			}
			echo json_encode($json);
	   break;


    }
