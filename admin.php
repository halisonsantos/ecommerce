<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;


#rota da página de admin
$app->get('/admin/', function() {
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
#tela de esqueci senha
$app->get("/admin/forgot",function(){

	$page = new PageAdmin([
			#desabilitando o header e o footer padrão
			"header" => false,
			"footer" => false
		]);

	$page->setTpl("forgot");
});
#
$app->post("/admin/forgot",function(){
	#pega o email que o usuário botou via post	
	#método que faz as verificações
	$user = User::getForgot($_POST["email"]);
	#fazer um redrect pra confirmar pra pessoa que o email foi enviado com sucesso
	header("Location:/admin/forgot/sent");
	exit;
});
#tela de confirmação de email
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
			#desabilitando o header e o footer padrão
			"header" => false,
			"footer" => false
		]);

	$page->setTpl("forgot-sent");

});
#página de reset de senha
$app->get("/admin/forgot/reset", function(){
	#validar o código e recuperar de qual usuário pertence o código
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
			#desabilitando o header e o footer padrão
			"header" => false,
			"footer" => false
		]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});
#resetar a senha
$app->post("/admin/forgot/reset", function(){
	#validar de novo o código
	$forgot = User::validForgotDecrypt($_POST["code"]);
	#chama o método que informa que o processo de recuperação de senha já foi usado
	User::setForgotUsed($forgot["idrecovery"]);
	#carregando o objeto usuário
	$user = new User();

	$user->get((int)$forgot["iduser"]);
	#criptografando a senha
	$password = password_hash($_POST["password"],PASSWORD_DEFAULT,[
			"cost"=>12
	]);

	#método pra salvar a senha do reset
	$user->setPassword($password);
	#mostrar a confirmação do reset
	$page = new PageAdmin([
			#desabilitando o header e o footer padrão
			"header" => false,
			"footer" => false
		]);

	$page->setTpl("forgot-reset-success");

});

?>