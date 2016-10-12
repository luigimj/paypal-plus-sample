<?php
// Define Sandbos or Live ( change the # )
	$MODUS=0 ; // 0 = sandbox / 1 = live
	define('CURRENCYCODE', 'EUR' );
	define('LOCALECODE', 'de_DE' );
	define('PRICE_IS_NET', false );
if ($MODUS==1) {
	define('PP_MODUS','live');
	define('PP_DATA', 'data_live.json');
	define('PPURL','api.paypal.com');
	define('PP_CLIENTID', 'ATDM6LjStNN--7g2YvuNsbz_Lemzmc7EOokCRZQb0z3yovDDFvrEzEwq3vqV2X9xO_zEj4NnZ620lut0' );
	define('PP_SECRET', 'EJGLIEApAJgca8_cnIawKV1RTuOCdR7Jon41L4av7mkuL4u1wgZyot_r_1vMuQpraCHgFT1Sd2ru7nIJ' );
}else{
	define('PP_MODUS','sandbox');
	define('PP_DATA', 'data_test.json');
	define('PPURL','api.sandbox.paypal.com');
	define('PP_CLIENTID', 'AX6xv28-EmeJqaMhnY_XJaJxGeJh6101IMAKiiqBC2aJXfFGnefrswLfq1JYFdjqUQnxTB61F5N41Spa' );
	define('PP_SECRET', 'EOeiWqfwydwGVn2bHWOTTg6BHOvS-sZURVULlN5KDQmdSLOdtAjdfsERB11biNY1pog_K7XUeMVQ0Lv9' );
}
	define('BRAND_NAME','PayPal Plus T-Shirt Shop');
	define('URL_RETURN', $_SESSION['BASE_URL'].'/paypal/index.php?action=return');
	define('URL_CANCLE', $_SESSION['BASE_URL'].'/paypal/index.php?action=cancel');
	define('URL_LOGO', 'https://static.e-junkie.com/sslpic/137759.64a570be1ddd78ed2cb0d47d0d8ddbbc.jpg');
	define('BANK_DATA', 'Bitte Zahlen an Test Company GmbH. IBAN DE93838031321080808</b>');

## NO CHANGES BELOW !! ########################################################################
// set timzone
	date_default_timezone_set('UCT');
// build API Credentials
	define('API_CREDENTIALS', PP_CLIENTID.':'.PP_SECRET);
function GetAccessToken(){

$data_url='logs/'.PP_DATA;

//Get Access Token
if(empty($_SESSION['access_token'])){
# check for valid access token
	$ts_now = time();
	$jsonDATA = (array) json_decode(file_get_contents( $data_url, true));
	if (!empty($jsonDATA)) {
		$_SESSION['expiry'] = $jsonDATA['expiry'];
		$_SESSION['access_token'] = $jsonDATA['access_token'];
		$_SESSION['app_id'] = $jsonDATA['app_id'];
		$_SESSION['token_type'] = $jsonDATA['token_type'];
		$_SESSION['webprofilID'] = $jsonDATA['webprofilID'];
		} else { echo '<br> ERROR - NO DATA';} 

	if ( $ts_now > $jsonDATA['expiry'] ) {
		$url='https://'.PPURL.'/v1/oauth2/token';
		$JSONrequest= 'grant_type=client_credentials';

		$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			//curl_setopt($ch, CURLOPT_SSLCERT, $sslcertpath);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Accept: application/json',
				'Accept-Language: de_DE'
				));
			curl_setopt($ch, CURLOPT_USERPWD, API_CREDENTIALS);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONrequest);

		$result = curl_exec($ch); 
		$resultGetAccessToken = json_decode($result,true);
#echo '<br><hr> result :'; print_r($resultGetAccessToken);
		curl_close ($ch);
		$_SESSION['expiry'] = time() + $resultGetAccessToken['expires_in'];
		$_SESSION['access_token'] = $resultGetAccessToken['access_token'];
		$_SESSION['app_id'] = $resultGetAccessToken['app_id'];
		$_SESSION['token_type'] = $resultGetAccessToken['token_type'];
		$jsonSTRING = '{ "expiry":"'.$resultGetAccessToken['expiry'].'" , "access_token":"'.$_SESSION['access_token'].'" , "app_id":"'.$_SESSION['app_id'].'","token_type":"'.$_SESSION['token_type'].'","webprofilID":"'.$_SESSION['webprofilID'].'"}';
		file_put_contents($data_url, $jsonSTRING);
	} 
}
if(empty($_SESSION['webprofilID'])){
	$url='https://'.PPURL.'/v1/payment-experience/web-profiles';
	$JSONrequest= '{"name": "PP_T-Shit_Shop_'.rand(0,10000).'"
	,"presentation": {
		"brand_name": "'.BRAND_NAME.'",
		"logo_image": "'.URL_LOGO.'"

	}
	,"input_fields": {
		"allow_note": false,
		"no_shipping": 2,
		"address_override": 1
	}}';
	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_SSLCERT, $sslcertpath);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Bearer '.$_SESSION['access_token']
			));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONrequest);

	$result = curl_exec($ch); 
	$resultGetExpreienceProfile = json_decode($result,true);
#echo '<br><hr> result :'; print_r($resultGetExpreienceProfile);
	curl_close ($ch);
	$_SESSION['webprofilID'] = $resultGetExpreienceProfile['id'];
	$jsonSTRING = '{ "expiry":"'.$_SESSION['expiry'].'" , "access_token":"'.$_SESSION['access_token'].'" , "app_id":"'.$_SESSION['app_id'].'","token_type":"'.$_SESSION['token_type'].'","webprofilID":"'.$_SESSION['webprofilID'].'"}';
	file_put_contents($data_url, $jsonSTRING);
}
}

Function GetApprovalURL(){
$_SESSION['invoiceID'] = 'PPP'.rand(0,1000000);
$url='https://'.PPURL.'/v1/payments/payment';
$JSONrequest='{
	"intent": "sale",
	"payer": { "payment_method": "paypal" },
	
	"transactions": [ {
			"amount": {
				"currency": "'.CURRENCYCODE.'",
				"total": "'.$_SESSION['TotalOrderAmount'].'",
				"details": {
					"subtotal": "'.$_SESSION['TotalItemAmount'].'",
					"tax": "'.$_SESSION['TotalTaxAmt'].'",
					"shipping": "'.$_SESSION['ShippingAmt'].'"
				}
			},
			"invoice_number":"'.$_SESSION['invoiceID'].'",
			"item_list": {
				"items": [
					{
						"quantity": "'.$_SESSION['ItemQty'].'",
						"name": "'.$_SESSION['ItemName'].'",
						"price": "'.$_SESSION['ItemPrice'].'",
						"currency": "'.CURRENCYCODE.'"
					}
				],
				"shipping_address": {
					"recipient_name": "'.$_SESSION['first_name'].' '.$_SESSION['last_name'].'",
					"line1": "'.$_SESSION['address1'].'",
					"line2": "'.$_SESSION['address2'].'",
					"city": "'.$_SESSION['city'].'",
					"state": "'.$_SESSION['state'].'",
					"postal_code": "'.$_SESSION['zip'].'",
					"country_code": "'.$_SESSION['country'].'",
					"phone": "'.$_SESSION['phone'].'"
				}
			}
		}],
		
	"redirect_urls": {
		"return_url": "'.URL_RETURN.'",
		"cancel_url":  "'.URL_CANCLE.'"
	},
	"experience_profile_id":"'.$_SESSION['webprofilID'].'"
}';
	#print($JSONrequest);
	#print('<br>');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_SSLCERT, $sslcertpath);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: Bearer '.$_SESSION['access_token']
		));
		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONrequest);

	$result = curl_exec($ch); 
	$resultGetApprovalURL = json_decode($result,true);
	curl_close ($ch);
#echo '<br><hr> result :'; print_r($resultGetApprovalURL);
	$_SESSION['status'] = $resultGetApprovalURL['state'];
		if ($_SESSION['status'] != 'created'){
			$_SESSION['error'] = json_encode($resultExecutePayment);
			}

	$_SESSION['approvalURL'] = $resultGetApprovalURL['links'][1]['href'];;
	$_SESSION['redirectURL'] = $resultGetApprovalURL['links'][2]['href'];
	$_SESSION['payID'] = $resultGetApprovalURL['id'];
}

Function ExecutePayment(){
$url='https://'.PPURL.'/v1/payments/payment/'.$_SESSION['payID'].'/execute';
$JSONrequest='{"payer_id":"'.$_SESSION['PayerID'].'",
	"transactions": [{
			"amount": {
				"currency": "'.CURRENCYCODE.'",
				"total": "'.$_SESSION['TotalOrderAmount'].'",
				"details": {
					"subtotal": "'.$_SESSION['TotalItemAmount'].'",
					"tax": "'.$_SESSION['TotalTaxAmt'].'",
					"shipping": "'.$_SESSION['ShippingAmt'].'"
				}
			},
			"invoice_number":"'.$_SESSION['invoiceID'].'",
			"item_list": {
				"items": [
					{
						"quantity": "'.$_SESSION['ItemQty'].'",
						"name": "'.$_SESSION['ItemName'].'",
						"price": "'.$_SESSION['ItemPrice'].'",
						"currency": "'.CURRENCYCODE.'"
					}
				],
				"shipping_address": {
					"recipient_name": "'.$_SESSION['first_name'].' '.$_SESSION['last_name'].'",
					"line1": "'.$_SESSION['address1'].'",
					"line2": "'.$_SESSION['address2'].'",
					"city": "'.$_SESSION['city'].'",
					"state": "'.$_SESSION['state'].'",
					"postal_code": "'.$_SESSION['zip'].'",
					"country_code": "'.$_SESSION['country'].'",
					"phone": "'.$_SESSION['phone'].'"
				}
			}
		}
	]}
}
'; 
//var_dump($JSONrequest);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_SSLCERT, $sslcertpath);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: Bearer '.$_SESSION['access_token']
		));

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $JSONrequest);

	$resultExecutePayment = json_decode(curl_exec($ch),true); 
	curl_close ($ch);
	$_SESSION['status'] = $resultExecutePayment['state'];
		if ($_SESSION['status'] != 'approved'){
			$_SESSION['error'] = json_encode($resultExecutePayment);
			}
#echo '<br><hr> result :'; print_r($resultExecutePayment);
#echo json_encode($resultExecutePayment, JSON_PRETTY_PRINT);
	$filename='PP_'.date('Y-m-d_H-i-s').'.html';
	file_put_contents('logs/'.$filename, json_encode($resultExecutePayment));

}

?>
