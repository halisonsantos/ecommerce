<?php 
#iniciar o uso de sessões
session_start();
#vendor
require_once("vendor/autoload.php");
#namespace
use \Slim\Slim;
#rota, criando uma nova aplicação
$app = new Slim();

$app->config('debug', true);
#puxando os arquivos que possuem as rotas
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

$app->run();

 ?>