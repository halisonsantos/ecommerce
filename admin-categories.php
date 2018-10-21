<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
#rota para acessar o template de categoria
$app->get("/admin/categories", function(){
	#verifica se o usuário está logado
	User::verifyLogin();
	#Precisa criar uma classe category
	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories",[
		'categories'=>$categories
	]);

});
#rota para acessar o template de criar categoria
$app->get("/admin/categories/create", function(){
	#verifica se o usuário está logado
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});
#rota pra criar a categoria
$app->post("/admin/categories/create", function(){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();
	#vai pegar os dados do post e setar
	$category->setData($_POST);
	#salvar
	$category->save();

	header('Location: /admin/categories');
	exit;

});
#Rota para deletar categoria
$app->get("/admin/categories/:idcategory/delete", function($idcategory){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();
	#verificar se existe para poder excluir
	$category->get((int)$idcategory);
	#exclui 
	$category->delete();

	header('Location: /admin/categories');
	exit;
});
#Rota para mostrar tela de editar categoria
$app->get("/admin/categories/:idcategory", function($idcategory){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update",[
		'category'=>$category->getValues()
	]);
	
});
#editar categoria
$app->post("/admin/categories/:idcategory", function($idcategory){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);
	#carrega os dados atuais e coloca os novos dados do formulário
	$category->setData($_POST);
	#salva
	$category->save();

	header('Location: /admin/categories');
	exit;
});

#rota para mostrar a página das relações de produto e categoria
$app->get("/admin/categories/:idcategory/products", function($idcategory){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();
	#carregando a categoria
	$category->get((int)$idcategory);
	#volta a página do site
	$page = new PageAdmin();

	$page->setTpl("categories-products",[
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
});
# adicionar nas categorias
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();
	#carregando a categoria
	$category->get((int)$idcategory);
	#volta a página do site
	$page = new PageAdmin();
	
	$product = new Product();

	$product->get((int)$idproduct);
	#adiciona produto na categoria
	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;	
});
#remover nas categorias
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	#verifica se o usuário está logado
	User::verifyLogin();

	$category = new Category();
	#carregando a categoria
	$category->get((int)$idcategory);
	#volta a página do site
	$page = new PageAdmin();
	
	$product = new Product();

	$product->get((int)$idproduct);
	#adiciona produto na categoria
	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;	
});
?>