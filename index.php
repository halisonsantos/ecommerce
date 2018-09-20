<?php 
#vendor
require_once("vendor/autoload.php");
#namespace
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
#rota, criando uma nova aplicação
$app = new Slim();

$app->config('debug', true);
#rota 1
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});
#rota da página de admin
$app->get('/admin', function() {
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->run();

 ?>