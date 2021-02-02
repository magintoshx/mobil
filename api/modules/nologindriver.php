<?php
require_once("app/inc/connect.php");
require_once("app/inc/data.php");
//sleep(1);
uye_olmamali();

    switch ($input["s"]) {
        case 1:
                $user = temizle($input["phone"]);
                $pass = temizle($input["password"]);
				
                $pass = md5(md5($pass));
				$json="";
                $count = mysqli_query($db, "select id, phone,status from driver_list where phone = '$user' and password='$pass' limit 1");
                if (mysqli_num_rows($count) < 1) {
                    $json = array('status' => 0, 'message' => 'Girmiş olduğunuz bilgilere ait kullanıcı bulunamadı');
                    echo json_encode($json);
                } else {
					$oku = mysqli_fetch_assoc($count);
					if($oku["status"]=="0"){
						$json = array('status'=>0,'message'=>"Hesabınız henüz kontrol aşamasındadır. Bilgilerinizin kontrol edilerek onaylanmasından sonra giriş yapabilirsiniz.");
					}else{
							$rs = generateRandomString(35);
						$guncelle = mysqli_query($db,"update driver_list set login_token='$rs' where id='".$oku["id"]."' limit 1");
						if($guncelle){
							$cek = mysqli_query($db, "select * from driver_list where id='".$oku["id"]."' limit 1");
							$json = array('status'=>1,'message'=>mysqli_fetch_assoc($cek));
						}else{
							$json = array('status'=>0,'message'=>"İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin.");
						}
					}
				
				    echo json_encode($json);
                }
            
            break;
   
        case 5:
            $phone = temizle($input["phone"]);
            if (empty($phone)) {
                $json = array('status' => 0, 'message' => "Lütfen telefon numaranızı yazınız");
            } else {
                $sql = mysqli_query($db, "select id, phone from driver_list where phone='$phone' limit 1");
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
                        mysqli_query($db, "update driver_list set pass_key='$kodGen' where id='" . $oku["id"] . "'");
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
                $sql = mysqli_query($db, "select * from driver_list where phone='$phone' and pass_key='$code'");
                if (mysqli_num_rows($sql) < 1) {
                    $json = array('status' => 0, 'message' => "Girmiş olduğunuz onay kodu hatalı");
                } else {
					$oku = mysqli_fetch_assoc($sql);
					$newpass = md5(md5($pass));
					$yeni = mysqli_query($db,"update driver_list set password='$newpass' where id='".$oku["id"]."' limit 1");
					if($yeni){
						 $json = array('status' => 1, 'message' => "Şifreniz başarıyla değiştirildi");
					}else{
						 $json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin");
					}              
                }
            }
            echo json_encode($json);
            break;
    }
