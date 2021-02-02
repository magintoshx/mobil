<?php
require_once("app/inc/connect.php");
require_once("app/inc/data.php");
//sleep(1);
uye_olmamali();

    switch ($input["s"]) {
        case 1:
                $user = temizle($input["phone"]);
                $pass = temizle($input["password"]);
                $oneId = temizle($input["oneId"]);

                $pass = md5(md5($pass));
				$json="";
                $count = mysqli_query($db, "select id,sms_confirm, phone,one_key from users where phone = '$user' and password='$pass' limit 1");
                if (mysqli_num_rows($count) < 1) {
                    $json = array('status' => 0, 'message' => 'Girmiş olduğunuz bilgilere ait kullanıcı bulunamadı');
                    echo json_encode($json);
                } else {
					$rs = generateRandomString(35);
					$oku = mysqli_fetch_assoc($count);
					if($oku["sms_confirm"]==0){
						$json = array('status' => 2, 'message' => $oku);
					}else{
						if($oku["one_key"] == $oneId)
						$guncelle = mysqli_query($db,"update users set login_token='$rs' where id='".$oku["id"]."' limit 1");
							else
						$guncelle = mysqli_query($db,"update users set login_token='$rs', one_key='$oneId' where id='".$oku["id"]."' limit 1");
						if($guncelle){
							$cek = mysqli_query($db, "select * from users where id='".$oku["id"]."' limit 1");
							$json = array('status'=>1,'message'=>mysqli_fetch_assoc($cek));
						}else{
							$json = array('status'=>0,'message'=>"İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
						}
					}
				    echo json_encode($json);
                }

            break;
        case 2:
		        $phone = temizle(trim($input["phone"]));
                $mail = temizle(trim($input["mail"]));
                $name = temizle(trim($input["name"]));
                $pass = temizle($input["pass"]);
                $repass = temizle($input["repass"]);
                $kosul = temizle($input["kosul"]);
				if(!$phone){
					$json = array('status' => 0, 'message' => 'Lütfen telefon numaranızı giriniz');
				}elseif(!$mail){
					$json = array('status' => 0, 'message' => 'Lütfen mail adresinizi giriniz');
				}elseif(!mailKontrol($mail)){
					$json = array('status' => 0, 'message' => 'Mail adresiniz kriterlere uygun değil');
				}elseif(strlen($name)<3){
					$json = array('status' => 0, 'message' => 'Adınız ve soyadınız çok kısa');
				}elseif($pass != $repass){
					$json = array('status' => 0, 'message' => 'Girdiğiniz şifreler aynı değil');
				}elseif(strlen($pass)<6 || strlen($pass)>20){
					$json = array('status' => 0, 'message' => 'Şifreniz 6 karakterden küçük, 20 karakterden büyük olamaz');
				}elseif(!$kosul){
					$json = array('status' => 0, 'message' => 'Kullanım koşullarını kabul etmeniz gerekmektedir');
				}else{
					$pcount = mysqli_query($db, "select * from users where phone = '$phone' limit 1");
					if (mysqli_num_rows($pcount) > 0) {
						$json = array('status' => 0, 'message' => 'Girmiş olduğunuz telefon numarası kullanılmakta');
					}else{
						$count = mysqli_query($db, "select * from users where mail = '$mail' limit 1");
						if (mysqli_num_rows($count) > 0) {
							$json = array('status' => 0, 'message' => 'Girmiş olduğunuz mail adresi kullanılmakta');
						}else{
							require_once('app/inc/sms/MesajPaneliApi.php');
									try {
									$tel = str_replace("(", "", $phone);
									$tel = str_replace(")", "", $tel);
									$tel = str_replace("-", "", $tel);
									$tel = str_replace(" ", "", $tel);
									$str = '1234567890';
									$kodGen = substr(str_shuffle($str), 0, 6);
									$smsApi = new MesajPaneliApi();
									$data = array('msg' => 'Hesabınızı onaylayabilmeniz için kodunuz:' . $kodGen, 'tel' => array($tel));
									$smsCevap = $smsApi->topluMesajGonder('BIIS', $data);
									$sif = md5(md5($pass));
									$z = mysqli_query($db, "insert into users set password='$sif', name='$name', mail='$mail', phone='$phone',
									sms_key='$kodGen'");
									if ($z) {
										$lastid = mysqli_insert_id($db);
										$json = array('status' => 1, 'message' => "Hesabınızı onaylamanız için telefon numaranıza doğrulama kodu gönderildi", "userid"=>$lastid);
									} else {
										$json = array('status' => 0, 'message' => $db_error);
									}
									} catch (Exception $e) {
									$json = array('status' => 0, 'message' => $e->getMessage());
								}
						}
					}
				}

            echo json_encode($json);

            break;
        case 3:
            $userid = temizle($input["userid"]);
            if (empty($userid)) {
                $json = array('status' => 0, 'message' => "Bilgilerinize ulaşılamadı");
            } else {
                $sql = mysqli_query($db, "select id,mail,phone from users where id='$userid' limit 1");
                if (mysqli_num_rows($sql) < 1) {
                    $json = array('status' => 0, 'message' => "Bilgilerinize ait kullanıcı bulunamadı");
                } else {
                    $oku = mysqli_fetch_assoc($sql);
                    $tel = str_replace("(", "", $oku["phone"]);
                    $tel = str_replace(")", "", $tel);
                    $tel = str_replace("-", "", $tel);
                    $tel = str_replace(" ", "", $tel);
                    require_once('app/inc/sms/MesajPaneliApi.php');
                    try {
                        $str = '1234567890';
                        $kodGen = substr(str_shuffle($str), 0, 6);
                        $smsApi = new MesajPaneliApi();
                        $data = array('msg' => 'Hesabınızı onaylayabilmeniz için kodunuz:' . $kodGen, 'tel' => array($tel));
                        $smsCevap = $smsApi->topluMesajGonder('BIIS', $data);
                        mysqli_query($db, "update users set sms_key='$kodGen' where id='" . $oku["id"] . "'");
                        $json = array('status' => 1, 'message' => "Doğrulama kodu gönderildi. Lütfen telefonunuza gelen kodu istenilen alana yazınız.");
                    } catch (Exception $e) {
                        $json = array('status' => 0, 'message' => $e->getMessage());
                    }
                }
            }
            echo json_encode($json);
            break;
        case 4:
		    $userid = temizle($input["userid"]);
		    $smskod = temizle($input["smskod"]);
            if (empty($userid) || empty($smskod)) {
                $json = array('status' => 0, 'message' => "Bilgilerinize ulaşılamadı");
            } else {
                $sql = mysqli_query($db, "select id from users where id='$userid' and sms_key='$smskod'");
                if (mysqli_num_rows($sql) < 1) {
                    $json = array('status' => 0, 'message' => "Girdiğiniz doğrulama kodu geçersiz");
                } else {
                    $sonid = mysqli_fetch_assoc($sql)["id"];
					$rs = generateRandomString(35);
                    $onay = mysqli_query($db,"update users set sms_confirm=1, login_token='$rs' where id='$sonid'");
                    if($onay){
                        //$kisi = mysqli_fetch_assoc(mysqli_query($db,"select * from users where id='$sonid' limit 1"));
                        $json = array('status' => 1, 'message' => "Hesabınız başarıyla oluşturuldu. Artık işleminizi gerçekleştirebilirsiniz.","userid"=>$userid,"token"=>$rs);
                    }else{
                        $json = array('status' => 0, 'message' => "Sistemsel hata oluştu. Lütfen daha sonra tekrar deneyin");
                    }
                }
            }
            echo json_encode($json);
            break;
        case 5:
            $phone = temizle($input["phone"]);
            if (empty($phone)) {
                $json = array('status' => 0, 'message' => "Lütfen telefon numaranızı yazınız");
            } else {
                $sql = mysqli_query($db, "select id, phone from users where phone='$phone' limit 1");
                if (mysqli_num_rows($sql) < 1) {
                    $json = array('status' => 0, 'message' => "Telefon numarasına ait bir hesap bulunamadı");
                } else {

					$oku = mysqli_fetch_assoc($sql);
                    $tel = str_replace("(", "", $oku["phone"]);
                    $tel = str_replace(")", "", $tel);
                    $tel = str_replace("-", "", $tel);
                    $tel = str_replace(" ", "", $tel);
                    require_once('app/inc/sms/MesajPaneliApi.php');
                    try {
                        $str = '1234567890';
                        $kodGen = substr(str_shuffle($str), 0, 6);
                        $smsApi = new MesajPaneliApi();
                        $data = array('msg' => 'Şifrenizi değiştirebilmeniz için kodunuz:' . $kodGen, 'tel' => array($tel));
                        $smsCevap = $smsApi->topluMesajGonder('BIIS', $data);
                        mysqli_query($db, "update users set pass_key='$kodGen' where id='" . $oku["id"] . "'");
                        $json = array('status' => 1, 'message' => "Kodunuz gönderildi. Lütfen telefonunuza gelen kodu istenilen alana yazınız.");
                    } catch (Exception $e) {
                        $json = array('status' => 0, 'message' => $e->getMessage());
                    }

                }
            }
            echo json_encode($json);
            break;
        case 6:
            $phone = temizle($input["phone"]);
            $pass = temizle($input["pass"]);
            $repass = temizle($input["repass"]);
            $code = temizle($input["code"]);
            if (empty($phone) || empty($pass) || empty($repass) || empty($code)) {
                $json = array('status' => 0, 'message' => "Lütfen tüm alanları doldurunuz");
            }elseif($pass != $repass){
				$json = array('status' => 0, 'message' => "Şifreler aynı değil");
			}elseif(strlen($pass)<6 || strlen($pass)>20){
				$json = array('status' => 0, 'message' => "Şifreniz 6 karakterden jüçük, 20 karakterden büyük olamaz");
			} else {
                $sql = mysqli_query($db, "select * from users where phone='$phone' and pass_key='$code'");
                if (mysqli_num_rows($sql) < 1) {
                    $json = array('status' => 0, 'message' => "Girmiş olduğunuz onay kodu hatalı");
                } else {
					$oku = mysqli_fetch_assoc($sql);
					$newpass = md5(md5($pass));
					$yeni = mysqli_query($db,"update users set password='$newpass' where id='".$oku["id"]."' limit 1");
					if($yeni){
						 $json = array('status' => 1, 'message' => "Şifreniz başarıyla değiştirildi");
					}else{
						 $json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin");
					}
                }
            }
            echo json_encode($json);
            break;
        case 7:
            $telefon = temizle($_GET["telefon"]);
            if (empty($telefon)) {
                $json = array('status' => 0, 'message' => $user_error);
            } else {
                $sql = mysqli_query($db, "select id from users where telefon='$telefon'");
                if (mysqli_num_rows($sql) < 1) {
                    $json = array('status' => 0, 'message' => "Kullanıcı bilgilerine erişilemedi");
                } else {
                    $tel = str_replace("(", "", $telefon);
                    $tel = str_replace(")", "", $tel);
                    $tel = str_replace("-", "", $tel);
                    $tel = str_replace(" ", "", $tel);
                    require_once('app/inc/sms/MesajPaneliApi.php');
                    try {
                        $oku = mysqli_fetch_assoc($sql);
                        $str = '1234567890';
                        $kodGen = substr(str_shuffle($str), 0, 6);
                        $smsApi = new MesajPaneliApi();
                        $data = array('msg' => 'Hesabınızı onaylayabilmeniz için kodunuz:' . $kodGen, 'tel' => array($tel));
                        $smsCevap = $smsApi->topluMesajGonder('BIIS', $data);
                        mysqli_query($db, "update users set smsKod='$kodGen' where id='" . $oku["id"] . "'");
                        $json = array('status' => 1, 'message' => 'Doğrulama kodunuz gönderildi');
                    } catch (Exception $e) {
                        $json = array('status' => 0, 'message' => $e->getMessage());
                    }
                }
            }
            echo json_encode($json);
            break;
        case 8:
            $email = temizle($input["email"]);
            $id = temizle($input["id"]);
            $name = temizle($input["name"]);
            if (empty($id) || empty($name)) {
                $json = array('status' => 0, 'message' => "Facebook girişi esnasında hata oluştu");
            } else {
                $sql = mysqli_query($db, "select * from users where facebook_key='$id' limit 1");
                if (mysqli_num_rows($sql) > 0) {
					$rs = generateRandomString(35);
					$update = mysqli_query($db,"update users set login_token='$rs' where facebook_key='{$id}'");
					$sqlguncel = mysqli_query($db, "select * from users where facebook_key='$id' limit 1");
					$ben = mysqli_fetch_assoc($sqlguncel);

                    /*if ($ben["onay"] == "0") {
                        $json = array('status' => 3, 'message' => $ben);
                    } else {
                        $json = array('status' => 1, 'message' => $ben);
                    }*/
					$json = array('status' => 1, 'message' => $ben);
                } else {
                    $str = '1234567890abcdefghijklmnoprstuvyzwxABCDEFGHIJKLMNOPRSTUVYZXW.';
                    $kodGen = substr(str_shuffle($str),0,8);
                    $newpass = md5(md5($kodGen));
					$rs = generateRandomString(35);
                    $yeni = mysqli_query($db,"insert into users set name='$name',login_token='$rs',facebook_key='$id', mail='$email', password='$newpass'");
                    if($yeni){
                        $sql1 = mysqli_query($db,"select * from users where facebook_key='$id' limit 1");
                        $json = array('status'=>1,'message'=>mysqli_fetch_assoc($sql1));
                    }else{
                        $json = array('status'=>0,'message'=>"Facebook girişi esnasında hata oluştu");
                    }
                    //$json = array('status' => 2, 'message' => "ok");
                }
            }
            echo json_encode($json);
            break;
        case 9:
            $is_validQ = GUMP::is_valid($_POST, array(
                'telefon' => 'required',
                'ad_soyad' => 'required',
                'mail' => 'required|valid_email',
                'kosullar' => 'required',
            ));
            if ($is_validQ === true) {
                $telefon = temizle($_POST["telefon"]);
                $mail = temizle($_POST["mail"]);
                $ad_soyad = temizle($_POST["ad_soyad"]);
                $kosullar = temizle($_POST["kosullar"]);
                $kampanya = temizle($_POST["kampanya"]);
                $faceid = temizle($_POST["faceid"]);
                $dogum_tarihi = temizle($_POST["dogum_tarihi"]);

                $count = mysqli_query($db, "select * from users where telefon = '$telefon' limit 1");
                if (mysqli_num_rows($count) > 0) {
                    $json = array('status' => 0, 'message' => 'Girmiş olduğunuz telefon numarası kullanılmakta');
                } else {
                    $count = mysqli_query($db, "select * from users where mail = '$mail' limit 1");
                    if (mysqli_num_rows($count) > 0) {
                        $json = array('status' => 0, 'message' => 'Girmiş olduğunuz mail adresi kullanılmakta');
                    } else {
                        require_once('app/inc/sms/MesajPaneliApi.php');
                        try {
                            $str = '1234567890';
                            $kodGen = substr(str_shuffle($str), 0, 6);

                            $str1 = '1234567890abcdefghijklmnoprstuvyzwxABCDEFGHIJKLMNOPRSTUVYZXW.';
                            $kodGen1 = substr(str_shuffle($str), 0, 8);
                            $sif = md5(md5($kodGen1));

                            $z = mysqli_query($db, "insert into users set telefon='$telefon', mail='$mail', ad_soyad='$ad_soyad', kampanya_haber='$kampanya', sifre='$sif', smsKod='$kodGen', facebookId='$faceid',dogum_tarihi='$dogum_tarihi'");
                            if ($z) {
                                $tel = str_replace("(", "", $telefon);
                                $tel = str_replace(")", "", $tel);
                                $tel = str_replace("-", "", $tel);
                                $tel = str_replace(" ", "", $tel);
                                $smsApi = new MesajPaneliApi();
                                $data = array('msg' => 'Hesabınızı onaylayabilmeniz için kodunuz:' . $kodGen, 'tel' => array($tel));
                                $smsCevap = $smsApi->topluMesajGonder('BIIS', $data);

                                $json = array('status' => 1, 'message' => "Hesabınızı onaylamanız için telefon numaranıza doğrulama kodu gönderildi");
                            } else {
                                $json = array('status' => 0, 'message' => $db_error);
                            }
                        } catch (Exception $e) {
                            $json = array('status' => 0, 'message' => $e->getMessage());
                        }
                    }

                }
            } else {
                $json = array('status' => 0, 'message' => $is_validQ[0]);
            }
            echo json_encode($json);

            break;
		case 10:
			$json = array('status' => 1, 'message' => system_keys("kullanim_kosullari"));
			 echo json_encode($json);
		break;
		 case 11:
		   $aa = array();
                $sql = mysqli_query($db, "select * from car_list");
                while($s = mysqli_fetch_assoc($sql)){
					$aa[] = $s;
				}
            $json = array('status' => 1, 'message' => $aa);
            echo json_encode($json);
            break;

			  case 12:
		        $phone = temizle(trim($input["phone"]));
                $mail = temizle(trim($input["mail"]));
                $name = temizle(trim($input["name"]));
                $pass = temizle($input["pass"]);
                $repass = temizle($input["repass"]);
                $kosul = temizle($input["kosul"]);
                $carmodel = temizle($input["carmodel"]);
                $caryil = temizle($input["caryil"]);
                $carplate = temizle($input["carplate"]);
                $cartype = temizle($input["cartype"]);
                $kimlikfoto = temizle($input["kimlikfoto"]);
                $aracgorseli = temizle($input["aracgorseli"]);
                $ruhsat = temizle($input["ruhsat"]);
                $d2belge = temizle($input["d2belge"]);
                $sabikafoto = temizle($input["sabikafoto"]);
				if(!$phone){
					$json = array('status' => 0, 'message' => 'Lütfen telefon numaranızı giriniz');
				}elseif(!$mail){
					$json = array('status' => 0, 'message' => 'Lütfen mail adresinizi giriniz');
				}elseif(!mailKontrol($mail)){
					$json = array('status' => 0, 'message' => 'Mail adresiniz kriterlere uygun değil');
				}elseif(strlen($name)<3){
					$json = array('status' => 0, 'message' => 'Adınız ve soyadınız çok kısa');
				}elseif($pass != $repass){
					$json = array('status' => 0, 'message' => 'Girdiğiniz şifreler aynı değil');
				}elseif(strlen($pass)<6 || strlen($pass)>20){
					$json = array('status' => 0, 'message' => 'Şifreniz 6 karakterden küçük, 20 karakterden büyük olamaz');
				}/*elseif(!$kosul){
					$json = array('status' => 0, 'message' => 'Kullanım koşullarını kabul etmeniz gerekmektedir');}*/
				elseif(!$carmodel){
					$json = array('status' => 0, 'message' => 'Araç modelini belirtmeniz gerekmektedir');}
				elseif(!$caryil){
					$json = array('status' => 0, 'message' => 'Araç yılını belirtmeniz gerekmektedir');}
				elseif(!$carplate){
					$json = array('status' => 0, 'message' => 'Araç plakaasını belirtmeniz gerekmektedir');}
        elseif(!$cartype){
          $json = array('status' => 0, 'message' => 'Araç tipini seçmeniz gerekmektedir');
        }
        elseif(!$aracgorseli || strlen($aracgorseli)<10)
        {
          $json = array('status' => 0, 'message' => 'Araç görselini plaka gözükecek şekilde seçmeniz gerekmektedir');
        }
        elseif(!$kimlikfoto || strlen($kimlikfoto)<10)
        {
          $json = array('status' => 0, 'message' => 'Kimlik fotoğrafınızı seçmeniz gerekmektedir');
        }
        elseif(!$sabikafoto || strlen($sabikafoto)<10)
        {
          $json = array('status' => 0, 'message' => 'Sabıka belgesini seçmeniz gerekmektedir');
        }
        else
        {
					$pcount = mysqli_query($db, "select * from driver_list where phone = '$phone' limit 1");
					if (mysqli_num_rows($pcount) > 0) {
						$json = array('status' => 0, 'message' => 'Girmiş olduğunuz telefon numarası kullanılmakta');
					}else{
						$count = mysqli_query($db, "select * from driver_list where mail = '$mail' limit 1");
						if (mysqli_num_rows($count) > 0) {
							$json = array('status' => 0, 'message' => 'Girmiş olduğunuz mail adresi kullanılmakta');
						}
            else
            {
              if($cartype==9)
              {
                if(!$ruhsat || strlen($ruhsat)<10)
                {
                  $json = array('status' => 0, 'message' => 'Belediye ruhsatını seçmeniz gerekmektedir');
                }
                else if(!$d2belge || strlen($d2belge)<10)
                {
                  $json = array('status' => 0, 'message' => 'D2 Belgesini seçmeniz gerekmektedir');
                }
                else
                {
                  $hata = false;
                  $filename_path = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string = str_replace('data:image/png;base64,', '', $kimlikfoto);
                  $base64_string = str_replace(' ', '+', $base64_string);
                  $decoded = base64_decode($base64_string);
                  $oldu = file_put_contents("app/data/img/".$filename_path,$decoded);
                  if(!$oldu){$hata = true;}

                  $filename_path1 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string1 = str_replace('data:image/png;base64,', '', $aracgorseli);
                  $base64_string1 = str_replace(' ', '+', $base64_string1);
                  $decoded1 = base64_decode($base64_string1);
                  $oldu1 = file_put_contents("app/data/img/".$filename_path1,$decoded1);
                  if(!$oldu1){$hata = true;}

                  $filename_path2 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string2 = str_replace('data:image/png;base64,', '', $ruhsat);
                  $base64_string2 = str_replace(' ', '+', $base64_string2);
                  $decoded2 = base64_decode($base64_string2);
                  $oldu2 = file_put_contents("app/data/img/".$filename_path2,$decoded2);
                  if(!$oldu2){$hata = true;}

                  $filename_path3 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string3 = str_replace('data:image/png;base64,', '', $d2belge);
                  $base64_string3 = str_replace(' ', '+', $base64_string3);
                  $decoded3 = base64_decode($base64_string3);
                  $oldu3 = file_put_contents("app/data/img/".$filename_path3,$decoded3);
                  if(!$oldu3){$hata = true;}

                  $filename_path4 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string4 = str_replace('data:image/png;base64,', '', $sabikafoto);
                  $base64_string4 = str_replace(' ', '+', $base64_string4);
                  $decoded4 = base64_decode($base64_string4);
                  $oldu4 = file_put_contents("app/data/img/".$filename_path4,$decoded4);
                  if(!$oldu4){$hata = true;}

                  if($hata)
                  {
                      $json = array('status' => 0, 'message' => "Görseller yüklenirken hata oluştu. Lütfen daha sonra tekrar deneyin.");
                  }
                  else
                  {
                      $sif = md5(md5($pass));
                      $z = mysqli_query($db, "insert into driver_list set password='$sif', name='$name', mail='$mail', phone='$phone',avatar='avatarsofor.png',car_type='$cartype',car_plate='$carplate',arac_model='$carmodel',arac_yil='$caryil',kimlikfoto='$filename_path',aracgorseli='$filename_path1',ruhsat='$filename_path2',d2belge='$filename_path3',sabikabelge='$filename_path4'");
                      if ($z)
                      {
                          $lastid = mysqli_insert_id($db);
                          $json = array('status' => 1, 'message' => "Başvurunuz başarıyla alındı. Gerekli kontrollerin ardından tarafınıza bilgi verilecektir. Teşekkür ederiz.", "userid" => $lastid);
                      }
                      else
                      {
                          $json = array('status' => 0, 'message' => $db_error);
                      }
                  }
                }
              }
              else
              {
                if(!$ruhsat || strlen($ruhsat)<10)
                {
                  $json = array('status' => 0, 'message' => 'Ruhsat fotoğrafını seçmeniz gerekmektedir');
                }
                else
                {
                  $hata = false;
                  $filename_path = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string = str_replace('data:image/png;base64,', '', $kimlikfoto);
                  $base64_string = str_replace(' ', '+', $base64_string);
                  $decoded = base64_decode($base64_string);
                  $oldu = file_put_contents("app/data/img/".$filename_path,$decoded);
                  if(!$oldu){$hata = true;}

                  $filename_path1 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string1 = str_replace('data:image/png;base64,', '', $aracgorseli);
                  $base64_string1 = str_replace(' ', '+', $base64_string1);
                  $decoded1 = base64_decode($base64_string1);
                  $oldu1 = file_put_contents("app/data/img/".$filename_path1,$decoded1);
                  if(!$oldu1){$hata = true;}

                  $filename_path2 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string2 = str_replace('data:image/png;base64,', '', $ruhsat);
                  $base64_string2 = str_replace(' ', '+', $base64_string2);
                  $decoded2 = base64_decode($base64_string2);
                  $oldu2 = file_put_contents("app/data/img/".$filename_path2,$decoded2);
                  if(!$oldu2){$hata = true;}

                  $filename_path4 = rand(1,99).md5(time().uniqid()).".jpg";
                  $base64_string4 = str_replace('data:image/png;base64,', '', $sabikafoto);
                  $base64_string4 = str_replace(' ', '+', $base64_string4);
                  $decoded4 = base64_decode($base64_string4);
                  $oldu4 = file_put_contents("app/data/img/".$filename_path4,$decoded4);
                  if(!$oldu4){$hata = true;}

                  if($hata)
                  {
                      $json = array('status' => 0, 'message' => "Görseller yüklenirken hata oluştu. Lütfen daha sonra tekrar deneyin.");
                  }
                  else
                  {
                      $sif = md5(md5($pass));
                      $z = mysqli_query($db, "insert into driver_list set password='$sif', name='$name', mail='$mail', phone='$phone',avatar='avatarsofor.png',car_type='$cartype',car_plate='$carplate',arac_model='$carmodel',arac_yil='$caryil',kimlikfoto='$filename_path',aracgorseli='$filename_path1',ruhsat='$filename_path2',d2belge='binek',sabikabelge='$filename_path4'");
                      if ($z)
                      {
                          $lastid = mysqli_insert_id($db);
                          $json = array('status' => 1, 'message' => "Başvurunuz başarıyla alındı. Gerekli kontrollerin ardından tarafınıza bilgi verilecektir. Teşekkür ederiz.", "userid" => $lastid);
                      }
                      else
                      {
                          $json = array('status' => 0, 'message' => $db_error);
                      }
                  }
                }

              }

						}
					}
				}
            echo json_encode($json);
            break;

    }
