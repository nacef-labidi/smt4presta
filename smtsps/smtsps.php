<?php

class SmtSps extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'smtsps';
		$this->tab = 'payments_gateways';
		$this->version = '1.5';
		
		$this->currencies = true;
		
		$this->currencies_mode = 'radio';

        parent::__construct();

		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('SMT');
        $this->description = $this->l('Monetique tunisie.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (Configuration::get('SMT_AFFILIE') == 0)
			$this->warning = $this->l('Veuillez parametrer votre module en renseignant le numero de terminal fourni pas l\'SMT');
		if ($_SERVER['SERVER_NAME'] == 'localhost')
			$this->warning = $this->l('Vous etes en local vous ne pouvez pas valider la commande!');
	}

	
	
	/**
	* Returns Production URL or Test URL (user preference)
	*
	* @return string url
	*/
	
	public function getSMTUrl()
	{
			return Configuration::get('SMT_TEST') ? 'http://196.203.10.183/paiement/' : 'https://www.smt-sps.com.tn/paiement/index.asp';
	}

	public function install()
	{
	$query = "INSERT IGNORE INTO `ps_order_state` (
			`id_order_state` ,
			`invoice` ,
			`send_email` ,
			`color` ,
			`unremovable` ,
			`hidden` ,
			`logable` ,
			`delivery`
			)
			VALUES (
			'55', '0', '0', 'lightblue', '1', '0', '0', '0'
			)";
	$query2 = "INSERT IGNORE INTO `ps_order_state_lang` (
			`id_order_state` ,
			`id_lang` ,
			`name` ,
			`template`
			)
			VALUES (
			'55', '1', 'Awaiting Card payment', ''
			), (
			'55', '2', 'En attente du paiement par carte bancaire', ''
			)";
		if (!parent::install()
			OR !Configuration::updateValue('SMT_AFFILIE', 0)
			OR !Configuration::updateValue('SMT_TEST', 1)
			OR !$this->registerHook('payment')
			OR !$this->registerHook('paymentReturn')
			OR !Db::getInstance()->Execute($query)
			OR !Db::getInstance()->Execute($query2))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('SMT_AFFILIE')
			OR !Configuration::deleteByName('SMT_TEST')
			OR !parent::uninstall())
			return false;
		return true;
	}

	public function getContent()
	{
		$this->_html = '<h2>Monetique Tunisie</h2>';
		if (isset($_POST['submitSMT']))
		{
			if (empty($_POST['affilie']))
				$this->_postErrors[] = $this->l('Le num&eacute;ro de terminal est requis.');
		
		if (!sizeof($this->_postErrors))
			{
				
				Configuration::updateValue('SMT_AFFILIE', strval($_POST['affilie']));
				Configuration::updateValue('SMT_TEST', intval($_POST['smt_test']));

				$this->displayConf();
			}
			else
				$this->displayErrors();
		}

		$this->displaySMT();
		$this->displayFormSettings();
		return $this->_html;
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="alert error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ol>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ol>
		</div>';
	}
	
	/**
	*Put The block to display in Modules list in html propriety
	*
	*/
	public function displaySMT()
	{
		$this->_html .= '
	
	
		<img src="../modules/smtsps/smt.gif" style="float:left; margin-right:15px;" />
		<b>'.$this->l('Ce module vous permet de r&eacute;aliser des paiement avec des cartes banquaire.').'</b><br /><br />
		<div style="clear:both;">&nbsp;</div>';
	}

	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('SMT_AFFILIE', 'SMT_TEST'));
		$affilie = array_key_exists('affilie', $_POST) ? $_POST['affilie'] : (array_key_exists('SMT_AFFILIE', $conf) ? $conf['SMT_AFFILIE'] : '');
		$smt_test = array_key_exists('smt_test', $_POST) ? $_POST['smt_test'] : (array_key_exists('SMT_TEST', $conf) ? $conf['SMT_TEST'] : '');

		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="clear: both;">
		<fieldset>
			<legend><img src="../img/admin/contact.gif" />'.$this->l('Configuration').'</legend>
			<label>'.$this->l('Num&eacute;ro du Terminal').'</label>
			<div class="margin-form"><input type="text" size="33" name="affilie" value="'.htmlentities($affilie, ENT_COMPAT, 'UTF-8').'" /></div>
			<div class="margin-form">
				<input type="radio" name="smt_test" value="1" '.($smt_test? 'checked="checked"' : '').' /> <label class="t">'.$this->l('Environnement de Test').'</label>
				<input type="radio" name="smt_test" value="0" '.(!$smt_test ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('En production').'</label>
			</div>
			<input type="submit" name="submitSMT" value="'.$this->l('Mettre &agrave; jour').'" class="button" /></center>
		</fieldset>
		</form><br /><br />
		';
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return ;

		return $this->display(__FILE__, 'smtsps.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;

		return $this->display(__FILE__, 'confirmation.tpl');
	}

	
	function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = false)
	{
		if (!$this->active)
			return ;

		parent::validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars);
	}
}
