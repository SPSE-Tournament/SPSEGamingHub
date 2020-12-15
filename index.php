<?php
session_start();
mb_internal_encoding("UTF-8");
require_once realpath(__DIR__ . "/vendor/autoload.php");
function autoLoad($cls) {
  if (preg_match('/Kontroler$/', $cls) or preg_match('/Controller$/', $cls)) {
    require('controllers/' . $cls . '.php');
  } else {
    require('models/' . $cls . '.php');
  }
}
spl_autoload_register("autoLoad");
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
Db::connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PW'], $_ENV['DB_DBNAME']);
$router = new RouterController();
$router->parse(array($_SERVER['REQUEST_URI']));
$router->showView();
?>
