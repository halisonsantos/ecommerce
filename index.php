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
#listar usuários
$app->get("/admin/users", function(){
	#verificar se usuário está logado e tem acesso ao administrativo
	User::verifyLogin();

	#Método statico pra listar
	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users",array(
		"users"=>$users

	));

});
#create .. get tras o template
$app->get("/admin/users/create", function(){
	#verificar se usuário está logado e tem acesso ao administrativo
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});
#deletar usuário(obs: o delete tem q ficar antes do update pq o slim framework vai ver q tem o /delete primeiro, se ficar em baixo o slim vai entender q é tudo tudo q vem depois do iduser faz parte do id)
$app->get("/admin/users/:iduser/delete", function($iduser){
	#verificar se usuário está logado e tem acesso ao administrativo
	User::verifyLogin();
	#carregar o usuários para ter certeza que ele existe no banco
	$user = new User();
	
	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;


});

#update .. vai passar o id do usuário
$app->get("/admin/users/:iduser", function($iduser){
	#verificar se usuário está logado e tem acesso ao administrativo
	User::verifyLogin();

	$user = new User();
 	#fazendo um get passando o número do id
	$user->get((int)$iduser);
 
  	$page = new PageAdmin();
 	#passar um array
   	$page ->setTpl("users-update", array(
        "user"=>$user->getValues()
   	));
});
#criar a rota pra salvar quando é post vai fazer o insert dos dados
$app->post("/admin/users/create", function(){
	#verificar se usuário está logado e tem acesso ao administrativo
	User::verifyLogin();

	$user = new User();
	#se o iadmin for definido o valor dele é 1 se não é 0
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);
	#execultar o insert no banco
	$user->save();

	header("Location: /admin/users");
	exit;

});
#salvar a edição
$app->post("/admin/users/:iduser", function($iduser){
	#verificar se usuário está logado e tem acesso ao administrativo
	User::verifyLogin();	

	$user = new User();
	#se o iadmin for definido o valor dele é 1 se não é 0
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	#carregar os dados passando o id
	$user->get((int)$iduser);
	#setar os dados do post
	$user->setData($_POST);
	#fazer o update
	$user->update();
	#redireciona para a lista de usuários
	header("Location: /admin/users");
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
	#método pra salvar a senha do reset
	$user->setPassword($_POST["password"]);
	#mostrar a confirmação do reset
	$page = new PageAdmin([
			#desabilitando o header e o footer padrão
			"header" => false,
			"footer" => false
		]);

	$page->setTpl("forgot-reset-success");

});



$app->run();

 ?>