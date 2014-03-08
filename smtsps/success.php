<?php
ob_start();
echo "date : ".date('Y-d-m h:i:s')."\n";
echo "Succes";
echo '\n';
print_r($_POST);
$ret = ob_get_contents();
ob_end_clean();
$fp = fopen("log.txt","a"); // ouverture du fichier en écriture
fputs($fp, "\n"); // on va a la ligne
fputs($fp, "$ret");
fclose($fp);
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/smtsps.php');

$errors = '';
$result = false;
$smt = new SmtSps();

$ref = $_POST['Reference'];
$act = $_POST['Action'];
$par = $_POST['Param'];



$temp = explode("TN-",$ref);
$id = str_replace("CMD","",$temp[0]);
$id = intval($id)-22002;


$cart = new Cart($id);
$id_order = Order::getOrderByCartId($id);
$order = new order($id_order);
if($order->getCurrentState() != 2){
$history = new OrderHistory();
$history->id_order = intval($id_order);
$history->changeIdOrderState(2, intval($id_order));
$history->addWithemail();
}
$customer = new Customer(intval($cart->id_customer));
$url =  Tools::getHttpHost(false, true).__PS_BASE_URI__;
$smt_id = intval($smt->id);

$redirectTo = 'http://'.$url.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.$cart->id.'&id_module='.$smt_id.'&slowvalidation';
header('Location: '.$redirectTo);
?>
