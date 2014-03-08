<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/smtsps.php');
$smt = new SmtSps();
$cart = new Cart(intval($cookie->id_cart));

$address = new Address(intval($cart->id_address_invoice));
$country = new Country(intval($address->id_country));
$state = NULL;
if ($address->id_state)
	$state = new State(intval($address->id_state));
$customer = new Customer(intval($cart->id_customer));
$affilie = Configuration::get('SMT_AFFILIE');
$currency_order = new Currency(intval($cart->id_currency));
$currency_module = $currency_order;


       
    
	
$Value  = floatval($cart->getOrderTotal(true, 3));

  $decimals = log10(abs($Value));
  $decimals = - (intval(min($decimals, 0)) - 3);
       
    $format = "%." . $decimals . "f";
$amount = sprintf($format, $Value);

if (!Validate::isLoadedObject($address) OR !Validate::isLoadedObject($customer))
	die($smt->l('Erreur de paiement : Addresse ou Client inconnu'));
$ref = $cart->id + 22002;
$randNumber = rand(999,100000);
$ref="CMD".$ref."TN-".$randNumber;

$smarty->assign(array(
	'reference'=>$ref,
	'redirect_text' => $smt->l('Veuillez patienter nous allons vous rediriger vers le serveur de paiement... Merci.'),
	'cancel_text' => $smt->l('Annuler'),
	'cart_text' => $smt->l('Mon panier'),
	'return_text' => $smt->l('Retour &agrave; la boutique'),
	'smt_url' => $smt->getSMTUrl(),
	'address' => $address,
	'country' => $country,
	'state' => $state,
	'amount' => $amount,
	'customer' => $customer,
	'sid' => $customer->secure_key,
	'total' => floatval($cart->getOrderTotal(true, 3)),
	'shipping' => Tools::ps_round(floatval($cart->getOrderShippingCost()) + floatval($cart->getOrderTotal(true, 6)), 2),
	'discount' => $cart->getOrderTotal(true, 2),
	'affilie' => $affilie,
	'currency_module' => $currency_module,
	'cart_id' => intval($cart->id),
	'products' => $cart->getProducts(),
	'smt_id' => intval($smt->id),
	'url' => Tools::getHttpHost(false, true).__PS_BASE_URI__
));
/*

			<input type="hidden" name="return" value="http://{$url}order-confirmation.php?key={$customer->secure_key}&id_cart={$cart_id}&id_module={$paypal_id}&slowvalidation" />
		
*/

if (is_file(_PS_THEME_DIR_.'modules/smtsps/redirect.tpl'))
	$smarty->display(_PS_THEME_DIR_.'modules/'.$smt->name.'/redirect.tpl');
else
	$smarty->display(_PS_MODULE_DIR_.$smt->name.'/redirect.tpl');

?>
