<?php
ob_start();
echo "date : ".date('Y-d-m h:i:s')."\n";
print_r($_GET);
$ret = ob_get_contents();
ob_end_clean();
$fp = fopen("log.txt","a"); // ouverture du fichier en écriture
fputs($fp, "\n"); // on va a la ligne
fputs($fp, "$ret");
fclose($fp);
/*
Tester le header de la requette HTTP
*/
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/smt.php');

$errors = '';
$result = false;
$smt = new Smt();


$ref = $_GET['Reference'];
$act = $_GET['Action'];
$par = $_GET['Param'];

$temp = explode("TN-",$ref);
$id = str_replace("CMD","",$temp[0]);

$id = intval($id)-22002;

$cart = new Cart($id);
$Value  = floatval($cart->getOrderTotal(true, 3));

  $decimals = log10(abs($Value));
  $decimals = - (intval(min($decimals, 0)) - 3);
       
    $format = "%." . $decimals . "f";

$montant = sprintf($format, $Value);

switch ($act) {
	case "DETAIL":
			$tampon = "Reference=".$ref. "&Action=".$act."&Reponse=".$montant;
			break;
	case "ERREUR":
		//	$smt->validateOrder($id, _PS_OS_ERROR_, 0, $smt->displayName, $smt->l('erreur ').$act);
			$tampon = "Reference=".$ref. "&Action=".$act. "&Reponse=OK";
			break;
	case "ACCORD":
	
			$tampon = "Reference=".$ref. "&Action=".$act. "&Reponse=OK";
			$smt->validateOrder(intval($id), 55, floatval($montant), $smt->displayName, $smt->l('transaction ').$par.' '.$tampon);
			break;
	case "REFUS":
		//	$smt->validateOrder($id, _PS_OS_ERROR_, 0, $smt->displayName, $smt->l('erreur ').$act);
			$tampon ="Reference=".$ref. "&Action=".$act. "&Reponse=OK";
			break;
	case "ANNULATION":
	
			$id_order = Order::getOrderByCartId($id);
			if($id_order>0){
				$history = new OrderHistory();
				$history->id_order = intval($id_order);
				$history->changeIdOrderState(6, intval($id_order));
				$history->addWithemail();
			}
			$tampon ="Reference=".$ref. "&Action=".$act. "&Reponse=OK";
			//$smt->validateOrder($id, 6, 0, $smt->displayName, $smt->l('Annulation ').$tampon );
			break;
}

echo $tampon ;
$fp = fopen("log.txt","a"); // ouverture du fichier en écriture
fputs($fp, "\n"); // on va a la ligne
fputs($fp, "\n$tampon");
fclose($fp);


?>
