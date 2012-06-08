<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/smtsps.php');

$reference = $_GET['Reference'];
$action = $_GET['Action'];
$param = $_GET['Param'];

switch($action) {
  case "DETAIL":
    if ($cart = new Cart($reference)) {
      echo "Reference=".$reference."&Action=".$action."&Reponse=".$cart->getOrderTotal();
    }
    break;

  case "ERREUR":
    echo "Reference=".$reference. "&Action=".$action."&Reponse=OK";
    break;

  case "ACCORD":
    $sps = new SmtSps();
    $cart = new Cart(intval($reference));
    $sps->validateOrder(intval($reference), Configuration::get('PS_OS_PAYMENT'), $cart->getOrderTotal(), $paymentMethod = 'SPS',
      NULL, array('transaction_id' => $param), NULL, false, false);
    echo "Reference=".$reference. "&Action=".$action."&Reponse=OK";
    break;

  case "REFUS":
    echo "Reference=".$reference. "&Action=".$action."&Reponse=OK";
    break;

  case "ANNULATION":
    echo "Reference=".$reference. "&Action=".$action."&Reponse=OK";
    break;
}
