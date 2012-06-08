<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/smtsps.php');

if (!$cookie->isLogged())
  Tools::redirect('authentication.php?back=order.php');

$sps = new SmtSps();
$cart = new Cart(intval($cookie->id_cart));
$currency = new Currency(intval($cart->id_currency));

$smarty->assign(array(
  'redirect_text' => 'Veuillez patienter pendant la redirection vers le serveur SPS',
  'cancel_text' => 'Annuler',
  'sps_url' => $sps->getSpsUrl(),
  'url' => Tools::getHttpHost(false, true).__PS_BASE_URI__,
  'affiliate' => $sps->getAffilie(),
  'currency' => $currency->iso_code,
  'reference' => str_pad($cart->id, 10, "0", STR_PAD_LEFT),
  'total' => str_replace('.', ',', trim(sprintf('%12.3f',$cart->getOrderTotal(true, 3)))),
  'session_id' => ''
));

$smarty->display(_PS_MODULE_DIR_.$sps->name.'/redirect.tpl');
