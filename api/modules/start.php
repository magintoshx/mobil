<?php
require_once("app/inc/connect.php");
require_once("app/inc/data.php");
require_once("app/inc/gump/gump.class.php");
uye_olmali();

    switch ($input["s"]) {
        case 1:
            $id = temizle($_GET["id"]);
            $pass = temizle($_GET["pass"]);
            $oneid = temizle($_GET["oneid"]);
            $count = mysqli_query($db, "select * from users where BINARY id = '$id' and password='$pass' limit 1");
            if (mysqli_num_rows($count) < 1) {
                $json = array('status' => 0, 'message' => 'Girmiş olduğunuz bilgilere ait kullanıcı bulunamadı');
                echo json_encode($json);
            } else {
				$kisi = mysqli_fetch_assoc($count);
				mysqli_query($db,"update users set oneid='$oneid' where id='$id' limit 1");
				$kisi["homeaddress"] = mysqli_fetch_assoc(mysqli_query($db,"select ha.*, c.city as city_text, co.country as country_text from home_address ha, city c, country co where ha.user_id='$id' and c.id=ha.city and co.id=ha.country limit 1"));
				$kisi["yildiz"] = mysqli_fetch_assoc(mysqli_query($db,"select avg(rc.point) as point from request_apply ra, request_comment rc where ra.user_id='$id' and rc.ra_id = ra.id"))["point"];
                $json = array('status' => 1, 'message' => $kisi);
                echo json_encode($json);
            }

            break;
        case 2:
            $userid = temizle($input["userid"]);
			$phone = temizle($input["phone"]);
			$name = temizle(trim($input["name"]));
			$mail = temizle(trim($input["mail"]));
			$subject = temizle(trim($input["subject"]));
			$message = temizle(trim($input["message"]));
			if(empty($userid) || empty($name) || empty($mail) || empty($subject) || empty($message)){
				$json = array('status' => 0, 'message' => "Lütfen zorunlu alanları doldurunuz");
			}elseif(!mailKontrol($mail)){
				$json = array('status' => 0, 'message' => "Mail adresiniz kriterlere uygun değil");
			}else{
				$sql = mysqli_query($db,"insert into help_message set user_id='$userid', name='$name', phone='$phone', mail='$mail',
				subject='$subject', message='$message'
				");
				if($sql){
					$json = array('status' => 1, 'message' => "Talebiniz başarıyla oluşturuldu. En kısa sürede tarafınıza dönüş yapılacaktır.");
				}else{
					$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
				}
			}
			echo json_encode($json);
            break;
       case 3:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $mylat = temizle($input["mylat"]);
	        $mylng = temizle($input["mylng"]);
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}else{
				if($mylat && $mylng){
					mysqli_query($db,"update users set carlat='$mylat', carlng='$mylng' where id='$userid' limit 1");
				}

				$arr=array();
				$carlist = mysqli_query($db,"select * from car_list order by sira asc");
				while($car = mysqli_fetch_assoc($carlist)){
					$car["secilmis"] = false;
					$arr["carlist"][] = $car;
				}
				$transfers = mysqli_query($db,"select * from travel_lists where user_id='$userid' and (status = 0 or status=1 or (status=2 and goruldu=0)) limit 1");
				$arr["travel_list"] =  mysqli_fetch_assoc($transfers);
if($arr["travel_list"]["status"] == 2){
  mysqli_query($db,"update travel_lists set goruldu = 1 where id ='{$arr["travel_list"]["id"]}' ");
}
				$car_list = mysqli_query($db,"select * from car_list where id='{$arr["travel_list"]["cartype"]}' limit 1");
				$arr["car_list"] =  mysqli_fetch_assoc($car_list);

        $list = array();
				$sql = mysqli_query($db,"select name, avatar, car_type, car_plate,carlat, carlng from driver_list where carlat<>'' limit 100");
				while($oku = mysqli_fetch_assoc($sql)){
					$list[] = $oku;
				}

        $arr["aracpois"] = $list;
				if($arr["travel_list"]["status"] == 1){
					$sofor = mysqli_fetch_assoc(mysqli_query($db,"select id,name,avatar,car_plate,carlat,carlng from driver_list where id='{$arr["travel_list"]["driver_id"]}'"));
					$sofor["rating"] = getDriverRate($sofor["id"]);
					$arr["travel_list"]["driver"] = $sofor;
				}
				$notify = mysqli_fetch_assoc(mysqli_query($db,"select count(*) as adet from notifications where user_id='$userid' and user_type=0 and status=0"));
				$arr["bildirimSayi"] = $notify["adet"];

				$notify1 = mysqli_fetch_assoc(mysqli_query($db,"select count(Distinct md.ml_id) as adet from message_data md, message_list ml where ml.user_id='$userid' and md.ml_id = ml.id and md.user_id != '$userid' and md.status = 0"));
				$arr["mesajSayi"] = $notify1["adet"];


        $musteriphone = mysqli_fetch_assoc(mysqli_query($db,"select * from users where id='$userid'"));

				$arr["musteriphone"] = $musteriphone["phone"];

        $driverphone = mysqli_fetch_assoc(mysqli_query($db,"select phone from driver_list where id='{$arr["travel_list"]["driver_id"]}'"));

				$arr["driverphone"] = $driverphone["phone"];

        //$arr["carlist"]=[];
        //bu kod ozeldir









                $mm = system_keys("musteribilgilendirmemesaji");
				$json = array('status' => 1, 'message' => $arr,"mesaj"=>$mm);
			}

			 echo json_encode($json);
	   break;
	   case 4:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}else{
				$arr = array();
				$user = mysqli_query($db,"select name, avatar, phone, mail, id from users where id='$userid' limit 1");
				$arr["user"] = mysqli_fetch_assoc($user);
				$seyahats = mysqli_query($db,"select * from travel_lists where user_id='$userid' and status > 1 order by id desc");
				$arr["gecmis"] = null;
				$i=0;
				while($seyahat = mysqli_fetch_assoc($seyahats)){
					$seyahat["key"] = $i;
					$i++;
					$arr["gecmis"][] = $seyahat;
				}
				$json = array('status' => 1, 'message' => $arr);
			}
			 echo json_encode($json);
	   break;
	   	   case 5:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $name = temizle(trim($input["name"]));
	        $mail = temizle(trim($input["mail"]));
	        $pass = temizle($input["pass"]);
	        $repass = temizle($input["repass"]);
	        $oldpass = temizle($input["oldpass"]);
	        $phone = temizle($input["phone"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($name)){
				$json = array('status' => 0, 'message' => "Lütfen isim alanını doldurunuz");
			}elseif(empty($phone)){
				$json = array('status' => 0, 'message' => "Lütfen telefon numaranızı giriniz");
			}elseif(strlen($phone)<10){
				$json = array('status' => 0, 'message' => "Lütfen telefon numaranızı 10 karakter olacak şekilde yazınız");
			}elseif(empty($mail)){
				$json = array('status' => 0, 'message' => "Lütfen mail alanını doldurunuz");
			}elseif(!mailKontrol($mail)){
				$json = array('status' => 0, 'message' => "Mail adresiniz kriterlere uygun değil");
			}
			else{
				$telefonsayi = mysqli_query($db,"select id from users where phone = '$phone' and id<>'$userid'");
				$mailsayi = mysqli_query($db,"select id from users where mail = '$mail' and id<>'$userid'");
				if(mysqli_num_rows($telefonsayi)>0){
					$json = array('status' => 0, 'message' => "Telefon numarası başka üye tarafından kullanılmakta");
				}elseif(mysqli_num_rows($mailsayi)>0){
					$json = array('status' => 0, 'message' => "Mail adresi başka üye tarafından kullanılmakta");
				}else{
					if($pass){
						if(!$oldpass){
							$json = array('status' => 0, 'message' => "Lütfen kullanımdaki şifrenizi giriniz");
						}
						elseif(strlen($pass)<6 || strlen($pass)>20){
							$json = array('status' => 0, 'message' => "Şifreniz 6 karakterden küçük, 20 karakterden büyük olamaz");
						}elseif($pass != $repass){
							$json = array('status' => 0, 'message' => "Girmiş olduğunuz şifreler aynı değil");
						}else{
							$old = md5(md5($oldpass));
							$sql = mysqli_query($db,"select id from users where id='$userid' and login_token='$token' and password='$old' limit 1");
							if(mysqli_num_rows($sql)<1){
								$json = array('status' => 0, 'message' => "Kullanımdaki şifrenizi hatalı girdiniz");
							}else{
								$newpass = md5(md5($pass));
								$guncelle = mysqli_query($db,"update users set name='$name',mail='$mail', phone='$phone', password='$newpass' where id='$userid' limit 1");
								if($guncelle){
									$json = array('status' => 1, 'message' => "Bilgileriniz başarıyla güncellendi");
								}else{
									$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
								}
							}
						}
					}else{
						$guncelle = mysqli_query($db,"update users set name='$name',mail='$mail', phone='$phone' where id='$userid' limit 1");
								if($guncelle){
									$json = array('status' => 1, 'message' => "Bilgileriniz başarıyla güncellendi");
								}else{
									$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
								}
					}

				}
			}


			 echo json_encode($json);
	   break;
	   case 6:
		$userid = temizle($input["userid"]);
		$token = temizle($input["token"]);
		$cartype = temizle($input["cartype"]);
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($cartype)){
				$json = array('status' => 0, 'message' => "Araç bilgisine ulaşılamadı");
			}else{
				$list = array();
				$sql = mysqli_query($db,"select name, avatar, car_type, car_plate,carlat, carlng from driver_list where car_type='$cartype' and carlat<>'' limit 20");
				while($oku = mysqli_fetch_assoc($sql)){
					$list[] = $oku;
				}
				$car = mysqli_fetch_assoc(mysqli_query($db,"select * from car_list where id='$cartype' limit 1"));
				$json = array('status' => 1, 'message' => $list, 'cartype'=>$car);
			}

		echo json_encode($json);
	   break;
	   case 7:

	   $userid = temizle($input["userid"]);
		$token = temizle($input["token"]);
		$image = temizle($input["image"]);
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($image)){
				$json = array('status' => 0, 'message' => "Görsel verilerine ulaşılamadı");
			}else{
				$filename_path = md5(time().uniqid()).".jpg";
				$base64_string = str_replace('data:image/png;base64,', '', $image);
				$base64_string = str_replace(' ', '+', $base64_string);
				$decoded = base64_decode($base64_string);
				$oldu = file_put_contents("app/data/img/".$filename_path,$decoded);
				if($oldu){
					$update = mysqli_query($db,"update users set avatar='$filename_path' where id='$userid' limit 1");
					if($update){
						$json = array('status' => 1, 'message' => "Avatar başarıyla belirlendi");
					}else{
						$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
				}else{
					$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
				}

			}

		echo json_encode($json);


	   break;
	    case 8:
			$userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}else{
				$list = null;
				$i=0;
				$sql = mysqli_query($db,"select * from notifications where user_id='$userid' and user_type=0 order by id desc");
				while($oku = mysqli_fetch_assoc($sql)){
					$oku["key"] = $i;
					$i++;
					mysqli_query($db,"update notifications set status = 1 where id='{$oku["id"]}' limit 1");
					$list[] = $oku;
				}
				$json = array('status' => 1, 'message' => $list);
			}
		echo json_encode($json);
		break;
	case 9:
			$userid = temizle($input["userid"]);
			$token = temizle($input["token"]);
			$fromlat = temizle($input["fromlat"]);
			$fromlng = temizle($input["fromlng"]);
			$fromtext = temizle($input["fromtext"]);
			$tolat = temizle($input["tolat"]);
			$tolng = temizle($input["tolng"]);
			$totext = temizle($input["totext"]);
			$cartype = temizle($input["cartype"]);
			$km = temizle($input["km"]);
			$money = temizle($input["money"]);
			$mylat = temizle($input["mylat"]);
			$mylng = temizle($input["mylng"]);
			$odeme = temizle($input["odeme"]);
      $odemetipi = temizle($input["odemetipi"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($mylat) || empty($mylng)){
				$json = array('status' => 0, 'message' => "Konum bilgileri alınamadı");
			}elseif(empty($fromlat) || empty($fromlng) || empty($fromtext) || empty($tolat) || empty($tolng) || empty($totext)){
				$json = array('status' => 0, 'message' => "Gidilecek rota bilgileri belirlenmedi");
			}elseif(empty($cartype)){
				$json = array('status' => 0, 'message' => "Yönlendirilecek araç tipi belirlenmedi");
			}
			else{
				$varmi = mysqli_query($db,"select id from travel_lists where user_id='$userid' where status = 1 or status = 0 limit 1");
				if(mysqli_num_rows($varmi)>0){
					$json = array('status' => 0, 'message' => "Daha önce oluşturmuş olduğunuz aktif bir talebiniz mevcut");
				}else{
					$syskey = system_keys("distance_driver_km");
					$kactaksi = mysqli_query($db,"select * from driver_list where car_type='$cartype' and DISTANCE1($fromlat,$fromlng, carlat, carlng, 'km') < $syskey");
					if(mysqli_num_rows($kactaksi)<1){
						$json = array('status' => 0, 'message' => "Konumunuza yönlendirilebilecek araç bulunamadı. Farklı araç tipi seçerek deneyebilirsiniz.");
					}else{
						$ekle = mysqli_query($db,"insert into travel_lists set user_id='$userid', fromlat='$fromlat', fromlng='$fromlng', fromtext='$fromtext', tolat='$tolat', tolng='$tolng', totext='$totext',
						cartype='$cartype', km='$km', money='$money',payment_type='$odeme',odemetipi='$odemetipi'
						");
						if($ekle){
							$transfers = mysqli_query($db,"select * from travel_lists where user_id='$userid' and status = 1 or status = 0 limit 1");
							//$mm = mysqli_fetch_assoc(mysqli_query($db,"select musteribilgilendirmemesaji from sys_keys limit 1"))["musteribilgilendirmemesaji"];
							$mm = system_keys("musteribilgilendirmemesaji");
							while($var = mysqli_fetch_assoc($kactaksi)){
								if($var["kabuldurum"] == "1" && $var["one_key"]) sendNotifySingleSound($var["one_key"],"Bilgilendirme","Çevrenizde yeni bir transfer isteği var.");
							}
							$json = array('status' => 1, 'message' => "Konumunuza yönlendirilecek araç bekleniyor", "travel_list"=>mysqli_fetch_assoc($transfers),"mesaj"=>$mm);
						}else{
							$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
						}
					}
				}
			}
		echo json_encode($json);
	break;
	case 10:
			$userid = temizle($input["userid"]);
			$token = temizle($input["token"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}
			else{
				$varmi = mysqli_query($db,"select id from travel_lists where user_id='$userid' and status = 0 limit 1");
				if(mysqli_num_rows($varmi)<1){
					$json = array('status' => 0, 'message' => "Talebiniz onaylandığı için iptal işlemini gerçekleştiremedik. Lütfen yardım için irtibata geçiniz.");
				}else{
					$iptalbasvuru = mysqli_query($db,"update travel_lists set status=3 where user_id='$userid' and status=0 limit 1");
					if($iptalbasvuru){
						$json = array('status' => 1, 'message' => "Yönlendirmeniz başarıyla iptal edildi");
					}else{
						$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
				}
			}
	echo json_encode($json);
	break;
	case 11:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $id = temizle($input["id"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Seyahat bilgisine ulaşılamadı");
			}else{
				$arr = array();
				$kontrol = mysqli_query($db,"select * from travel_lists where id='$id' and user_id='$userid' and status > 1 limit 1");
				if(mysqli_num_rows($kontrol)<1){
					$json = array('status' => 0, 'message' => "Seyahat bilgisine ulaşılamadı");
				}else{
					$durum = mysqli_query($db,"update travel_lists set status=(-1) where id='$id' and user_id='$userid' and status > 1 limit 1");
					if($durum){
						$seyahats = mysqli_query($db,"select * from travel_lists where user_id='$userid' and status > 1 order by id desc");
						while($seyahat = mysqli_fetch_assoc($seyahats)){
							$arr[] = $seyahat;
						}
						$json = array('status' => 1, 'message' => "Seyahat bilgisi başarıyla silindi", "data"=>$arr);
					}else{
						$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
				}
			}
			 echo json_encode($json);
	break;
	case 12:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $id = temizle($input["id"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Bildirim bilgisine ulaşılamadı");
			}else{
					$durum = mysqli_query($db,"delete from notifications where id='$id' and user_id='$userid' limit 1");
					if($durum){
						$json = array('status' => 1, 'message' => "Bildirim başarıyla silindi");
					}else{
						$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
			}
			 echo json_encode($json);
	break;
	case 13:
			$userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $s_id = temizle($input["s_id"]);
	        $srate = temizle($input["srate"]);
          $yorum = temizle($input["yorum"]);


			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($s_id)){
				$json = array('status' => 0, 'message' => "Puanlanacak seyahat bilgisine ulaşılamadı");
			}elseif(empty($srate)){
				$json = array('status' => 0, 'message' => "Lütfen puanınızı belirtiniz");
			}else{
				$oku = mysqli_query($db,"select * from travel_lists where id='$s_id' and user_id='$userid' and status=2 limit 1");
				if(mysqli_num_rows($oku)<1){
					$json = array('status' => 0, 'message' => "Seyahat bilgisine ulaşılamadı");
				}else{
					$ok = mysqli_fetch_assoc($oku);
					if($ok["star"] != 0){
						$json = array('status' => 0, 'message' => "Bu seyahati daha önce puanlamışsınız");
					}else{
						$durum = mysqli_query($db,"update travel_lists set star='$srate',yorum='$yorum' where id='$s_id' limit 1");
						if($durum){
							$json = array('status' => 1, 'message' => "Seyahatinize puan başarıyla eklendi");
						}else{
							$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
						}
					}
				}

			}
			 echo json_encode($json);	break;
	case 14:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $id = temizle($input["id"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Sürücü bilgisine ulaşılamadı");
			}else{
					$travel = mysqli_fetch_assoc(mysqli_query($db,"select driver_id from travel_lists where id='$id' and user_id='$userid' limit 1"));
					$durum = mysqli_fetch_assoc(mysqli_query($db,"select name,avatar,car_plate from driver_list where id='{$travel["driver_id"]}' limit 1"));
					$json = array('status' => 1, 'message' => $durum);

			}
			 echo json_encode($json);
	break;
	case 15:
		$user = temizle($_GET["user"]);
		$ss = mysqli_query($db,"select * from siparisler where user_id='$user' and durum=0");
		$sipa = mysqli_query($db,"select * from adresler where user_id='$user' and aktifMi=1 limit 1");
		$kuryede = null;
		if(mysqli_num_rows($ss)>0){
			$kuryede = mysqli_fetch_assoc($ss);
			$kuryede["adres"] = mysqli_fetch_assoc($sipa);
			$kuryede["kurye_bilgi"] = mysqli_fetch_assoc(mysqli_query($db,"select lat,lng from kuryeler where id='".$kuryede["kurye_id"]."'"));
			$kuryede["market_bilgi"] = mysqli_fetch_assoc(mysqli_query($db,"select isim, lat,lng from marketler where id='".$kuryede["market_id"]."'"));
		}else{
			$kuryede = null;
		}
		 $json = array('status' => 1, 'message' => $kuryede);
		echo json_encode($json);
	break;
	case 16:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $onekey = temizle($input["onekey"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($onekey)){
				$json = array('status' => 0, 'message' => "Key bilgisine ulaşılamadı");
			}else{
					$gunc = mysqli_fetch_assoc(mysqli_query($db,"update users set one_key='$onekey' where id='$userid' limit 1"));
					$json = array('status' => 1, 'message' => "ok");

			}
			 echo json_encode($json);
	break;
	  case 17:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}else{
				$arr=array();
				$notify = mysqli_fetch_assoc(mysqli_query($db,"select count(*) as adet from notifications where user_id='$userid' and user_type=0 and status=0"));
				$arr["bildirimSayi"] = $notify["adet"];

				$notify1 = mysqli_fetch_assoc(mysqli_query($db,"select count(Distinct md.ml_id) as adet from message_data md, message_list ml where ml.user_id='$userid' and md.ml_id = ml.id and md.user_id != '$userid' and md.status = 0"));
				$arr["mesajSayi"] = $notify1["adet"];



				$json = array('status' => 1, 'message' => $arr);
			}

			 echo json_encode($json);
	   break;
	   case 18:
            $userid = temizle($input["userid"]);
            $tip = temizle($input["tip"]);

				$sql = mysqli_query($db,"insert into acildurum set user_id='$userid',kullanici_tipi=$tip");
				if($sql)
				{
					$json = array('status' => 1, 'message' => "Yes");
				}
				else
				{
					$json = array('status' => 0, 'message' => "No");
				}
			echo json_encode($json);
            break;
            case 19:
          			$userid = temizle($input["userid"]);
          			$token = temizle($input["token"]);
          			$fromlat = temizle($input["fromlat"]);
          			$fromlng = temizle($input["fromlng"]);
          			$fromtext = temizle($input["fromtext"]);
          			$tolat = temizle($input["tolat"]);
          			$tolng = temizle($input["tolng"]);
          			$totext = temizle($input["totext"]);
          			$cartype = temizle($input["cartype"]);
          			$km = temizle($input["km"]);
          			$money = temizle($input["money"]);
          			$mylat = temizle($input["mylat"]);
          			$mylng = temizle($input["mylng"]);
          			$odeme = temizle($input["odeme"]);
                $odemetipi = temizle($input["odemetipi"]);
                $secilenTarih = temizle($input["secilenTarih"]);
                $secilenSaat = temizle($input["secilenSaat"]);

          			if(empty($userid) || empty($token))
                {
          				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
          			}
                elseif(empty($mylat) || empty($mylng))
                {
          				$json = array('status' => 0, 'message' => "Konum bilgileri alınamadı");
          			}
                elseif(empty($fromlat) || empty($fromlng) || empty($fromtext) || empty($tolat) || empty($tolng) || empty($totext))
                {
          				$json = array('status' => 0, 'message' => "Gidilecek rota bilgileri belirlenmedi");
          			}
                elseif(empty($cartype))
                {
          				$json = array('status' => 0, 'message' => "Yönlendirilecek araç tipi belirlenmedi");
          			}
          			else
                {
                  $ekle = mysqli_query($db,"insert into travel_rezer set user_id='$userid', fromlat='$fromlat', fromlng='$fromlng', fromtext='$fromtext', tolat='$tolat', tolng='$tolng', totext='$totext',
                  cartype='$cartype', km='$km', money='$money',payment_type='$odeme',odemetipi='$odemetipi',rez_tarih='$secilenTarih',rez_saat='$secilenSaat'
                  ");
                  if($ekle)
                  {
                    $kactaksi = mysqli_query($db,"select one_key from driver_list");
                    while($var = mysqli_fetch_assoc($kactaksi))
                    {
      								sendNotifySingleSound($var["one_key"],"Bilgilendirme","Çevrenizde yeni bir rezervasyon isteği var.");
      							}
                    $json = array('status' => 1, 'message' => "Rezervasyonunuz başarıyla alınmıştır.");
                  }
                  else
                  {
                    $json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
                  }
          			}
          		echo json_encode($json);
          	break;
    }
