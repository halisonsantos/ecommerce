<?php 
#iniciar o uso de sessões
session_start();
#vendor
require_once("vendor/autoload.php");
#namespace
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
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
#precisa validar se a pessoa está logada
	#criando um método statico que verifica isso
	User::verifyLogin();

    
	$page = new PageAdmin();

	$page->setTpl("index");

});
#rota login admin
$app->get('/admin/login', function(){

	$page = new PageAdmin([
			#desabilitando o header e o footer padrão
			"header" => false,
			"footer" => false
		]);

	$page->setTpl("login");

});

$app->post('/admin/login', function(){
	#Criar classe user, método statico login para receber o post de login e password se não estourar um erro 
	User::login($_POST["login"], $_POST["password"]);
	#será redirecionado
	header("Location: /admin");
	exit;

});
#rota para deslogar
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});

$app->run();

 ?>