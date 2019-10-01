<?php
session_start();
mb_internal_encoding("UTF-8");
function autoLoad($cls) {
  if (preg_match('/Kontroler$/', $cls) or preg_match('/Controller$/', $cls)) {
    require('controllers/' . $cls . '.php');
  } else {
    require('models/' . $cls . '.php');
  }
}
spl_autoload_register("autoLoad");
Db::connect("127.0.0.1", "root", "", "spsegaminghub");
$router = new RouterController();
$router->parse(array($_SERVER['REQUEST_URI']));
$router->showView();
?>
