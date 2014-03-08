<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
header('Location:'.'http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__.'order.php')
?>