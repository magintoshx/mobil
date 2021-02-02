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
				subject='$subject', message='$message', user_type=1
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
					mysqli_query($db,"update driver_list set carlat='$mylat', carlng='$mylng' where id='$userid' limit 1");
				}
				$arr=array();
				$transfers = mysqli_query($db,"select * from travel_lists where driver_id='$userid' and status=1");
				while($trans = mysqli_fetch_assoc($transfers)){
					$arr["applylist"][] = $trans;
				}
				$syskey = system_keys("distance_driver_km");
				$mycartype = get_my_car_type($userid);
				$kactaksi = mysqli_query($db,"select * from travel_lists where status = 0 and cartype='$mycartype' and DISTANCE1($mylat,$mylng, fromlat, fromlng, 'km') < $syskey");
				while($kt = mysqli_fetch_assoc($kactaksi)){
				    $vars = mysqli_query($db,"select id from istenmeyencagirlar where user_id='$userid' and cagri_id='{$kt["id"]}' limit 1");
				    if(mysqli_num_rows($vars)>0){
				        continue;
                    }
					$arr["waitlist"][] = $kt;
				    break;
				}

				$notify = mysqli_fetch_assoc(mysqli_query($db,"select count(*) as adet from notifications where user_id='$userid' and user_type=1"));
				$arr["bildirimSayi"] = $notify["adet"];
				$arr["phonenumber"] = system_keys("phonenumber");
				$durumum = mysqli_fetch_assoc(mysqli_query($db,"select kabuldurum from driver_list where id='$userid' limit 1"));
				$json = array('status' => 1, 'message' => $arr,"durum"=>$durumum["kabuldurum"]);
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
				$user = mysqli_query($db,"select name, avatar, phone, mail, id,car_plate,arac_yil,arac_model,created_at,car_type,kimlikfoto,aracgorseli,ruhsat from driver_list where id='$userid' limit 1");
				$arr["user"] = mysqli_fetch_assoc($user);
				$aractip = mysqli_fetch_assoc(mysqli_query($db,"select isim from car_list where id='{$arr["user"]["car_type"]}' limit 1"));
                $arr["user"]["aractip"] = $aractip["isim"];
				$seyahats = mysqli_query($db,"select * from travel_lists where driver_id='$userid' and status > 1 order by id desc limit 30");
				$arr["gecmis"] = null;
				while($seyahat = mysqli_fetch_assoc($seyahats)){
					$arr["gecmis"][] = $seyahat;
				}

				$bas = date('Y-m-d');
				$ciro = 0;
				$sql = "select money,odenen,km from travel_lists where driver_id='$userid' and DATE(created_at)='$bas' and status=2";
				$tops = mysqli_query($db,$sql);
				while($top = mysqli_fetch_assoc($tops)){
					if($top["odenen"]) $ciro += floatval($top["odenen"]);
					else $ciro += floatval($top["money"]) * floatval($top["km"]);
				}
				$json = array('status' => 1, 'message' => $arr,"ciro"=>round($ciro,1));
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
				$telefonsayi = mysqli_query($db,"select id from driver_list where phone = '$phone' and id<>'$userid'");
				$mailsayi = mysqli_query($db,"select id from driver_list where mail = '$mail' and id<>'$userid'");
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
							$sql = mysqli_query($db,"select id from driver_list where id='$userid' and login_token='$token' and password='$old' limit 1");
							if(mysqli_num_rows($sql)<1){
								$json = array('status' => 0, 'message' => "Kullanımdaki şifrenizi hatalı girdiniz");
							}else{
								$newpass = md5(md5($pass));
								$guncelle = mysqli_query($db,"update driver_list set name='$name',mail='$mail', phone='$phone', password='$newpass' where id='$userid' limit 1");
								if($guncelle){
									$json = array('status' => 1, 'message' => "Bilgileriniz başarıyla güncellendi");
								}else{
									$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
								}
							}
						}
					}else{
						$guncelle = mysqli_query($db,"update driver_list set name='$name',mail='$mail', phone='$phone' where id='$userid' limit 1");
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
				$sql = mysqli_query($db,"select name, avatar, car_type, car_plate,carlat, carlng from driver_list where car_type='$cartype'");
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
					$update = mysqli_query($db,"update driver_list set avatar='$filename_path' where id='$userid' limit 1");
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
				$sql = mysqli_query($db,"select * from notifications where user_id='$userid' and user_type=1 order by id desc");
				while($oku = mysqli_fetch_assoc($sql)){
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
					$kactaksi = mysqli_query($db,"select * from driver_list where car_type='$cartype' and DISTANCE1($mylat,$mylng, carlat, carlng, 'km') < $syskey");
					if(mysqli_num_rows($kactaksi)<1){
						$json = array('status' => 0, 'message' => "Konumunuza yönlendirilebilecek araç bulunamadı. Farklı araç tipi seçerek deneyebilirsiniz.");
					}else{
						$ekle = mysqli_query($db,"insert into travel_lists set user_id='$userid', fromlat='$fromlat', fromlng='$fromlng', fromtext='$fromtext', tolat='$tolat', tolng='$tolng', totext='$totext',
						cartype='$cartype', km='$km', money='$money'
						");
						if($ekle){
							$transfers = mysqli_query($db,"select * from travel_lists where user_id='$userid' and status = 1 or status = 0 limit 1");
							$json = array('status' => 1, 'message' => "Konumunuza yönlendirilecek araç bekleniyor", "travel_list"=>mysqli_fetch_assoc($transfers));
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
						$durum = mysqli_query($db,"update travel_lists set star='$srate' where id='$s_id' limit 1");
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
			$userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $id = temizle($input["id"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Seyehat bilgisine ulaşılamadı");
			}else{
					$travel = mysqli_fetch_assoc(mysqli_query($db,"select fromlat,fromlng,tolat,tolng from travel_lists where id='$id' limit 1"));
					$json = array('status' => 1, 'message' => $travel);
			}
			echo json_encode($json);
	break;
	case 16:
			$userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $id = temizle($input["id"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Seyahat bilgisine ulaşılamadı");
			}else{
				$durumbak = mysqli_query($db,"select id from travel_lists where id='$id' and driver_id=0 limit 1");
				if($durumbak<1){
					$json = array('status' => 0, 'message' => "Transfer başkası tarafından kabul edilmiş");
				}else{
					$isial = mysqli_query($db,"update travel_lists set driver_id='$userid', status=1 where id='$id' limit 1");
					if($isial){
						$json = array('status' => 1, 'message' => "Transfer tarafınızca başarıyla kabul edildi. İyi yolculuklar.");
					}else{
						$json = array('status' => 0, 'message' => "Transfer kabul edilirken hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
				}
			}
			echo json_encode($json);
			break;
	case 17:
			$userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $id = temizle($input["id"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($id)){
				$json = array('status' => 0, 'message' => "Seyehat bilgisine ulaşılamadı");
			}else{
				$durumbak = mysqli_query($db,"select id from travel_lists where id='$id' and driver_id='$userid' limit 1");
				if($durumbak<1){
					$json = array('status' => 0, 'message' => "Bu işlem için yetkiniz bulunmuyor");
				}else{
					$isial = mysqli_query($db,"update travel_lists set driver_id='0', status=0 where id='$id' limit 1");
					if($isial){
						$json = array('status' => 1, 'message' => "İşlem başarıyla gerçekleştirildi");
					}else{
						$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
					}
				}
			}
			echo json_encode($json);
	break;
        case 18:
            $userid = temizle($input["userid"]);
            $token = temizle($input["token"]);
            $id = temizle($input["id"]);

            $sorgula = mysqli_fetch_assoc(mysqli_query($db,"select km,money from travel_lists where id='$id' and driver_id='$userid' limit 1"));
            $kms = $sorgula["km"];
            $money = $sorgula["money"];
            $ucret = $kms*$money;

            if(empty($userid) || empty($token)){
                $json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
            }elseif(empty($id)){
                $json = array('status' => 0, 'message' => "Seyehat bilgisine ulaşılamadı");
            }elseif(empty($ucret) || !$ucret){
                $json = array('status' => 0, 'message' => "Lütfen ücret bilgisini girin");
            }else{
                $durumbak = mysqli_query($db,"select id from travel_lists where id='$id' and driver_id='$userid' limit 1");
                if($durumbak<1){
                    $json = array('status' => 0, 'message' => "Bu işlem için yetkiniz bulunmuyor");
                }else{
                    $isial = mysqli_query($db,"update travel_lists set status=2,odenen='$ucret' where id='$id' limit 1");



                    $bakiyeCek = mysqli_fetch_assoc(mysqli_query($db,"select * from driver_list where id='$userid'"));
					$guncelbakiye = $bakiyeCek["bakiye"];

                    $bakiye = $ucret*0.9;
                    $toplam = $guncelbakiye+$bakiye;
                    $bakiyeguncelle = mysqli_query($db,"update driver_list set bakiye='$toplam' where id='$userid' limit 1");




                    if($isial){
                        $json = array('status' => 1, 'message' => "Transfer başarıyla gerçekleştirildi olarak işaretlendi");
                    }else{
                        $json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
                    }
                }
            }
            echo json_encode($json);
            break;
        case 19:
            $userid = temizle($input["userid"]);
            $token = temizle($input["token"]);
            $durum = temizle($input["durum"]);

            if(empty($userid) || empty($token)){
                $json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
            }else{
                $isial = mysqli_query($db,"update driver_list set kabuldurum='$durum' where id='$userid' limit 1");
                if($isial){
                    $json = array('status' => 1, 'message' => "Durum başarıyla değiştirildi");
                }else{
                    $json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
                }
            }
            echo json_encode($json);
            break;
            case 20:
        $userid = temizle($input["userid"]);
        $token = temizle($input["token"]);
        $id = temizle($input["id"]);

        if(empty($userid) || empty($token)){
            $json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
        }else{
            $cek = mysqli_query($db,"select id from istenmeyencagirlar where user_id='$userid' and cagri_id='$id' limit 1");
            if(mysqli_num_rows($cek)<1){
                $isial = mysqli_query($db,"insert into istenmeyencagirlar set user_id='$userid',cagri_id='$id'");
                if($isial){
                    $json = array('status' => 1, 'message' => "ok");
                }else{
                    $json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
                }
            }else{
                $json = array('status' => 1, 'message' => "ok");
            }
        }
        echo json_encode($json);
        break;
			case 21:
	        $userid = temizle($input["userid"]);
	        $token = temizle($input["token"]);
	        $onekey = temizle($input["onekey"]);

			if(empty($userid) || empty($token)){
				$json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
			}elseif(empty($onekey)){
				$json = array('status' => 0, 'message' => "Key bilgisine ulaşılamadı");
			}else{
					$gunc = mysqli_fetch_assoc(mysqli_query($db,"update driver_list set one_key='$onekey' where id='$userid' limit 1"));
					$json = array('status' => 1, 'message' => "ok");

			}
			 echo json_encode($json);
	break;
    case 22:
    $userid = temizle($input["userid"]);
    $token = temizle($input["token"]);
    if(empty($userid) || empty($token))
    {
       $json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
    }
    else
    {
      $cartypedriver = mysqli_fetch_assoc(mysqli_query($db,"select car_type from driver_list where id='$userid' limit 1"));
      $cartypedriveryeni = $cartypedriver["car_type"];

      $arr = array();
      $rezervasyons = mysqli_query($db,"select * from travel_rezer where cartype='$cartypedriveryeni' order by id desc limit 30");
      $arr["rezervasyon"] = null;
      while($rezervasyon = mysqli_fetch_assoc($rezervasyons))
      {
        $arr["rezervasyon"][] = $rezervasyon;
      }
     $json = array('status' => 1, 'message' => $arr);
    }
    echo json_encode($json);
    break;
    case 23:
    $userid = temizle($input["userid"]);
    $token = temizle($input["token"]);
    $id = temizle($input["id"]);
    if(empty($userid) || empty($token))
    {
       $json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
    }
    else
    {
      $sorgula = mysqli_fetch_assoc(mysqli_query($db,"select * from travel_rezer where id='$id' limit 1"));
      $durums = $sorgula["driver_id"];

      if ($durums==0)
      {
        $travelrezer = mysqli_fetch_assoc(mysqli_query($db,"select * from travel_rezer where id='$id'"));
				$kullanicid = $travelrezer["user_id"];

        $oneidbulucu = mysqli_fetch_assoc(mysqli_query($db,"select * from users where id='$kullanicid'"));
				$oneid = $oneidbulucu["one_key"];

        sendNotifySingleSoundUsers($oneid,"Bilgilendirme","Oluşturduğunuz rezarvasyon şoför tarafından alındı.");

        $gunc = mysqli_fetch_assoc(mysqli_query($db,"update travel_rezer set driver_id='$userid',status='1' where id='$id' limit 1"));
        $json = array('status' => 1, 'message' => "ok");
      }
      else
      {
        $json = array('status' => 0, 'message' => 'Bu transfer daha önce alınmış.');

      }
    }
    echo json_encode($json);
    break;
    case 24:
    $userid = temizle($input["userid"]);
    $id = temizle($input["id"]);
    $token = temizle($input["token"]);
    if(empty($userid) || empty($token))
    {
       $json = array('status' => 0, 'message' => "Token bilgisine ulaşılamadı");
    }
    else
    {
      $arr = array();
      $rezervasyons = mysqli_query($db,"select travel_rezer.fromtext,travel_rezer.description,travel_rezer.totext,travel_rezer.created_at,users.name,users.phone,travel_rezer.fromlat,travel_rezer.fromlng,travel_rezer.tolat,travel_rezer.tolng,travel_rezer.rez_tarih,travel_rezer.rez_saat from travel_rezer JOIN users ON users.id=travel_rezer.user_id where travel_rezer.id='$id' and travel_rezer.driver_id='$userid'");
      $arr["rezervasyon"] = null;
      while($rezervasyon = mysqli_fetch_assoc($rezervasyons)){
        $arr["rezervasyon"] = $rezervasyon;
      }
     $json = array('status' => 1, 'message' => $arr);
    }
    echo json_encode($json);
    break;
  }
