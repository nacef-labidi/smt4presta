<?php

if (!defined('_PS_VERSION_'))
	exit;

class SmtSps extends PaymentModule {

  private $_html = '';
  private $_postErrors = array();

	public function __construct() {
		$this->name = "smtsps";
    $this->tab = "payments_gateways";
    $this->version = "1.0.0";

    $this->currencies = true;
    $this->author = "Nacef LABIDI <nacef.labidi@gmail.com>";

    parent::__construct();

    $this->page = basename(__FILE__, '.php');
    $this->displayName = $this->l('SMT SPS');
    $this->description = $this->l('Paiement à travers SPS de Société Monétique Tunisie.');
	}

  public function install() {
    if (!parent::install()
        OR !$this->createSpsTbl()
        OR !Configuration::updateValue('SMTSPS_AFFILIE', '')
        OR !Configuration::updateValue('SMTSPS_SANDBOX', 1)
        OR !$this->registerHook('payment')
        OR !$this->registerHook('paymentReturn'))
      return false;
    return true;
  }

  public function uninstall()
  {
    if (!Configuration::deleteByName('SMTSPS_AFFILIE')
        OR !Configuration::deleteByName('SMTSPS_SANDBOX')
        OR !$this->dropSpsTbl()
        OR !parent::uninstall())
      return false;
    return true;
  }

  function createSpsTbl() {
    $db = Db::getInstance();
    $query = "CREATE TABLE `"._DB_PREFIX_."order_sps` (
      `id_payment` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `id_order` INT NOT NULL,
      `id_transaction` TEXT NOT NULL,
      `status` TEXT NOT NULL) ENGINE=InnoDB;";
    $db->Execute($query);
    return true;
  }

  function dropSpsTbl() {
    $db = Db::getInstance();
    $query = "DROP TABLE `"._DB_PREFIX_."order_sps`;";
    $db->Execute($query);
    return true;
  }

  function hookPayment($params) {
    global $smarty;

    $smarty->assign(array(
      'this_path' => $this->_path,
      'this_path_ssl' => Configuration::get('PS_FO_PROTOCOL').$_SERVER['HTTP_HOST'].__PS_BASE_URI__."modules/{$this->name}/"
    ));

    return $this->display(__FILE__, 'smtsps.tpl');
  }

  private function _displayForm() {
    $sandbox = Configuration::get('SMTSPS_SANDBOX');
    $this->_html .= '
      <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
        <label>'.$this->l('Affilie').'</label>
        <div class="margin-form">
          <input type="text" name="affilie" value="'.Configuration::get('SMTSPS_AFFILIE').'" />
        </div>
        <label>'.$this->l('Sandbox').'</label>
        <div class="margin-form">
				  <input type="radio" name="sandbox" value="1" '.($sandbox ? 'checked="checked"' : '').' />
  				<label class="t">'.$this->l('Yes').'</label>
  				<input type="radio" name="sandbox" value="0" '.(!$sandbox ? 'checked="checked"' : '').' />
  				<label class="t">'.$this->l('No').'</label>
  		  </div>
  		  <div class="margin-form">
          <input type="submit" name="submit" value="'.$this->l('Update').'" class="button" />
        </div>
      </form>';
  }

  public function getContent() {
    if (Tools::isSubmit('submit')) {

      $this->_postValidation();

      if (!sizeof($this->_postErrors)) {
        Configuration::updateValue('SMTSPS_AFFILIE', Tools::getValue('affilie'));
        Configuration::updateValue('SMTSPS_SANDBOX', Tools::getValue('sandbox'));
        $this->_html .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
      }
      else {
        foreach($this->_postErrors as $error) {
          $this->_html .= '<div class="alert error">'.$error.'</div>';
        }
      }
    }

    $this->_displayForm();

    return $this->_html;
  }

  private function _postValidation() {
    if (Tools::getValue('affilie') == '') {
      $this->_postErrors[] = $this->l('The field "Affiliate" is mandatory.');
    }
  }

  public function getSpsUrl() {
    return Configuration::get('SMTSPS_SANDBOX') ? 'http://196.203.10.190/paiement/' : 'https://www.smt-sps.com.tn/paiement/';
  }

  public function getAffilie() {
    return Configuration::get('SMTSPS_AFFILIE');
  }


}