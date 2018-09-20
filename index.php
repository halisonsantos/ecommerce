<?php 
#vendor
require_once("vendor/autoload.php");
#namespace
use \Slim\Slim;
use \Hcode\Page;
#rota, criando uma nova aplicação
$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");


});

$app->run();

 ?>