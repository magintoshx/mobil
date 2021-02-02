<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

    /**
     * @package posnettest
     */
    //phpinfo();
    //Include POSNET Class
    require_once('Modules/PosnetXML/posnet.php');

	////////////////////////////////////////////////////////////
	//PHP4 - PHP5 uyumluluk için eklenilmiştir. 
	$POST;
	$GET;
	if ((floatval(phpversion()) >= 5) && ((ini_get('register_long_arrays') == '0') || (ini_get('register_long_arrays') == '')))
	{
	  $POST =& $_POST;
	  $GET =& $_GET;
	} 
	else 
	{
	  $POST =& $HTTP_POST_VARS;
	  $GET =& $HTTP_GET_VARS;
	}
    ////////////////////////////////////////////////////////////
	
	$cardnumber = str_replace("-","",$cardnumber);
	$validdate = explode("/",$validdate);
	$validdate = $validdate[1].$validdate[0];
	
	$ucret = explode(".",$ucret);
	$ucrett = intval($ucret[0]);
	if(count($ucret)>1){
		$ucrettt = intval($ucret[1]);
	}else{
		$ucrettt = 0;
	}
	$ucret = ($ucrett * 100) + $ucrettt;
	
    //$hostname = $POST['hostname'];
    $hostname = "https://www.posnet.ykb.com/PosnetWebService/XML";
    //$hostname = "http://setmpos.ykb.com/PosnetWebService/XML";
    //$mid = $POST['mid'];
    $mid = "6793224111";
    //$tid = $POST['tid'];
    $tid = "67345541";
    //$trantype = $POST['trantype'];
    $trantype = "Sale";
    $ccno = $cardnumber;
    $expdate = $validdate;
    $cvc = $cardcvc;
    $orderid = $orderid;
    $amount = $ucret;
    $wpamount = $ucret;
    $currencycode = "YT";
    $instnumber = "00";
    $multpoint = "00";
    $extpoint = "000000";
    $hostlogkey = "";
    $authcode = "";
    $vftcode = "K001";
    $koicode = "";

    $posnet = new Posnet;
    //$posnet->SetDebugLevel(2);
    $posnet->UseOpenssl();
    $posnet->SetURL($hostname);
    $posnet->SetMid($mid);
    $posnet->SetTid($tid);
   // $posnet->SetUsername($username);
   // $posnet->SetPassword($password);
    $posnet->SetKOICode($koicode);
     
    if ($trantype == "Auth") {
        $posnet->DoAuthTran(
        $ccno,
            $expdate, // Ex : 0703 - Format : YYMM
        $cvc,
            $orderid,
            $amount, // Ex : 1500->15.00 YTL
        $currencycode, // Ex : YT
        $instnumber // Ex : 05
        );
    }
    else if ($trantype == "Sale") {
        $posnet->DoSaleTran(
        $ccno,
            $expdate, // Ex : 0703 - Format : YYMM
        $cvc,
            $orderid,
            $amount, // Ex : 1500->15.00 YTL
        $currencycode, // Ex : YT
        $instnumber // Ex : 05
        );
    }
    else if ($trantype == "SaleWP") {
        $posnet->DoSaleWPTran(
        $ccno,
            $expdate, // Ex : 0703 - Format : YYMM
        $cvc,
            $orderid,
            $amount, // Ex : 1500->15.00 YTL
            $wpamount, // Ex : 1500->15.00 YTL            
        $currencycode, // Ex : YT
        $instnumber // Ex : 05
        );
    }
    else if ($trantype == "Capture") {
        $posnet->DoCaptTran(
        $hostlogkey,
            $authcode,
            $amount,
            $currencycode, // Ex :YT
        $instnumber // Ex : 05
        );
    }
    else if ($trantype == "AuthRev") {
        $posnet->DoAuthReverseTran(
        $hostlogkey,
            $authcode );
    }
    else if ($trantype == "SaleRev") {
        $posnet->DoSaleReverseTran(
        $hostlogkey,
            $authcode );
    }
    else if ($trantype == "CaptureRev") {
        $posnet->DoCaptReverseTran(
        $hostlogkey,
            $authcode );
    }
    else if ($trantype == "Return") {
        $posnet->DoReturnTran(
            $hostlogkey,
            $amount,
            $currencycode // Ex :YT
        );
    }
    else if ($trantype == "PNTU") {
        $posnet->DoPointUsageTran(
        $ccno,
            $expdate, // Ex : 0703 - Format : YYMM
        $orderid,
            $amount, // Ex : 1500->15.00 YTL
        $currencycode // Ex : YT
        );
    }
    else if ($trantype == "PNTV") {
        $posnet->DoPointReverseTran(
        $hostlogkey);
    }
    else if ($trantype == "PNTR") {
        $posnet->DoPointReturnTran(
            $hostlogkey,
            $wpamount,
            $currencycode // Ex :YT
        );
    }
    else if ($trantype == "PNTI") {
        $posnet->DoPointInquiryTran(
        $ccno,
            $expdate // Ex : 0703 - Format : YYMM
        );
    }
    // VFT Transactions
    else if ($trantype == "VFTI") {
        $posnet->DoVFTInquiry(
        $ccno,
            $amount, // Ex : 1500->15.00 YTL
        $instnumber, // Ex : 05
        $vftcode );
    }
    else if ($trantype == "VFTS") {
        $posnet->DoVFTSale(
        $ccno,
            $expdate, // Ex : 0703 - Format : YYMM
        $cvc,
            $orderid,
            $amount, // Ex : 1500->15.00 YTL
        $currencycode, // Ex : YT
        $instnumber, // Ex : 05
        $vftcode );
    }
    else if ($trantype == "VFTR") {
        $posnet->DoVFTSaleReverse(
        $hostlogkey,
            $authcode );
    }
    // KOI Transactions
    else if ($trantype == "KOIInq") {
        $posnet->DoKOIInquiry($ccno);
    }
    else{
		
	}
	
	if($posnet->GetApprovedCode() === '1'){
		$ekle = mysqli_query($db,"insert into requests set
				title='$baslik', user_id='$user', address='$adres', description='$aciklama', category='$kategori',
				starttime='$baslangic', finishtime='$bitis', price='$fiyat', price_method='$odemetip', latlng='$latlng',person_number='$kisisayisi', address_from='$transferadres1', address_to='$transferadres2'
				");
				if($ekle){
					$lastid = mysqli_insert_id($db);
					if($gorevresimleri){
					$gorevresims = json_decode($gorevresimleri);
					foreach($gorevresims as $gorevresim){
						mysqli_query($db,"insert into request_images set request_id='$lastid', url='$gorevresim'");
					}
					}
					
					$json = array('status' => 1, 'message' => "Talep başarıyla oluşturuldu");
				}else{
					$json = array('status' => 0, 'message' => "İşlem esnasında hata oluştu. Lütfen daha sonra tekrar deneyin");
				}
	echo json_encode($json);
	}else{
		$json = array('status' => 0, 'message' => "Hata: ".$posnet->GetResponseText());
		echo json_encode($json);
	}
	
?>