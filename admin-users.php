<?php 
##Usuários da admin##
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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



?>