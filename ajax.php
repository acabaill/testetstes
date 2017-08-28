<?php
//PHP function call by ajax
include_once('../../config/config.inc.php');
include_once('../../init.php');

$id = (isset($_GET['product_id'])) ? $_GET['product_id'] : FALSE;
 if($id)
 {
        $procart = Module::getInstanceByName('Omnisense');
        die ($procart->hookAjaxCall($id));
 }
 else
 {
     echo json_encode(array());

 }
?>
