<?php

//	This is the CONTROLER
session_start();

if (!empty($_POST)) { $REQUEST = $_POST; }
if (!empty($_GET)) 	{ $REQUEST = $_GET; }

if (!empty($REQUEST)) {

	include("function.php");
	$DISPLAY = 'none';
	$_SESSION['status'] = 'none';

	switch ($REQUEST['action']) {
		
		case 'sale':
			$_SESSION['ItemName'] = filter_var($REQUEST['product'].' '.$REQUEST['option1'].' '.$REQUEST['option2'], FILTER_SANITIZE_STRING);
			$_SESSION['ItemQty'] = round($REQUEST['qty'],2);
			$_SESSION['ItemTaxRate'] = round($REQUEST['tax_rate'] / 100,2);
			
			if (PRICE_IS_NET == false){  // Price includes tax
				$_SESSION['ItemPrice'] = round($REQUEST['price'] / (1+$_SESSION['ItemTaxRate']),2);
				$_SESSION['TotalItemAmount'] = $_SESSION['ItemPrice'] * $_SESSION['ItemQty'];
				$_SESSION['ShippingAmt'] = round($REQUEST['shipping'] / (1+$_SESSION['ItemTaxRate']),2);
				$_SESSION['TotalTaxAmt'] = ($REQUEST['price'] * $_SESSION['ItemQty']) + $REQUEST['shipping'] - $_SESSION['TotalItemAmount'] - $_SESSION['ShippingAmt'];
				$_SESSION['TotalOrderAmount'] = $_SESSION['TotalItemAmount'] + $_SESSION['ShippingAmt'] + $_SESSION['TotalTaxAmt'];
			} else {	// price not includes tax
				$_SESSION['ItemPrice'] = round($REQUEST['price'] * (1+$_SESSION['ItemTaxRate']),2);
				$_SESSION['TotalItemAmount'] = $_SESSION['ItemPrice'] * $_SESSION['ItemQty'];
				$_SESSION['ShippingAmt'] = round( $REQUEST['shipping'] * (1+$_SESSION['ItemTaxRate']),2);
				$_SESSION['TotalTaxAmt'] = $_SESSION['TotalItemAmount'] + $_SESSION['ShippingAmt'] - ($REQUEST['price'] * $_SESSION['ItemQty']) - $REQUEST['shipping'];
				$_SESSION['TotalOrderAmount'] = $_SESSION['TotalItemAmount'] + $_SESSION['ShippingAmt'] + $_SESSION['TotalTaxAmt'];
			}

			include('html/header.phtml');
			include('html/cart.phtml');
			include('html/footer.phtml');
		break;

		case 'pay':
			$_SESSION['first_name'] = filter_var($REQUEST['first_name'], FILTER_SANITIZE_STRING);
			$_SESSION['last_name'] = filter_var($REQUEST['last_name'], FILTER_SANITIZE_STRING);
			$_SESSION['address1'] = filter_var($REQUEST['address1'], FILTER_SANITIZE_STRING);
			$_SESSION['address2'] =filter_var($REQUEST['address2'], FILTER_SANITIZE_STRING);
			$_SESSION['city'] = filter_var($REQUEST['city'], FILTER_SANITIZE_STRING);
			$_SESSION['zip'] = filter_var($REQUEST['zip'], FILTER_SANITIZE_STRING);
			$_SESSION['state'] = filter_var($REQUEST['state'], FILTER_SANITIZE_STRING);
			$_SESSION['country'] = filter_var($REQUEST['country'], FILTER_SANITIZE_STRING);
			$_SESSION['phone'] = filter_var($REQUEST['phone'], FILTER_SANITIZE_STRING);
			$_SESSION['email'] = filter_var($REQUEST['email'], FILTER_SANITIZE_STRING);
			header('Location: '.$_SESSION['BASE_URL'].'/paypal/index.php?action=payment');
			exit;
		break;

		case 'payment':
			GetAccessToken();
			if(!empty($_SESSION['access_token'])){
				$DISPLAY = "paywall";
				include( 'html/header.phtml');
				GetApprovalURL();
				
				if ($_SESSION['status'] == 'created') { 
					include('html/payment.phtml'); 
				}
				else { 
					header('Location: '.$_SESSION['BASE_URL'].'/paypal/index.php?action=cart_change');exit;
				}
				
				include('html/footer.phtml');
				exit;
			} else { 
				include('html/header.phtml');
				include('html/error.phtml');
				include('html/footer.phtml');
				exit;
			}
		break;

		case 'bank':
			$DISPLAY = "bank";
			include('html/header.phtml');
			include('html/thankyou.phtml');
			include('html/footer.phtml');
		break;

		case 'return':
			$_SESSION['token'] = urldecode($REQUEST['token']) ;
			$_SESSION['PayerID'] = urldecode($REQUEST['PayerID']) ;
			include( 'html/header.phtml');
			ExecutePayment();
			if ($_SESSION['status'] == 'approved'){include('html/thankyou.phtml');}
			else {include('html/error.phtml');}
			include('html/footer.phtml');
			exit;
		break;

		case 'cancel':
			header('Location: '.$_SESSION['BASE_URL'].'/paypal/index.php?action=payment');
			exit;
		break;

		case 'cart_change':
			include('html/header.phtml');
			include('html/cart.phtml');
			include('html/footer.phtml');
			exit;
		break;

		case '':
		break;

		case '':
		break;

	default:
		include('html/header.phtml');
		include('html/404.phtml');
		include('html/footer.phtml');
	}
	
} else { 
	include('html/header.phtml');
	include("html/404.phtml");
	include('html/footer.phtml');
}
?>