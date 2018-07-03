<?php
require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../fieldvmegamenu.php');
$flex = new fieldvmegamenu();

echo $flex->getProductCover(Tools::getValue('pID'));


?>