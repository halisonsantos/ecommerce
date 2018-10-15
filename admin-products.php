<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;
#lista os produtos
$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products",[
		"products"=>$products
	]);
});
#tela de criar os produtos
$app->get("/admin/products/create", function(){
	#verifica login
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");
});
#cria o produto
$app->post("/admin/products/create", function(){
	#verifica login
	User::verifyLogin();
	#cria um novo produto
	$product = new Product();
	#setar as informações
	$product->setData($_POST);
	#método save
	$product->save();
	#redireciona para a lista de produtos
	header("Location: /admin/products");
	exit;
});
#tela para alterar o produto
$app->get("/admin/products/:idproduct", function($idproduct){
	#verifica login
	User::verifyLogin();
	#cria um novo produto
	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();
	#passar os dados para o template
	$page->setTpl("products-update",[
		'product'=>$product->getValues()
]);
#altera as informações do produto
$app->post("/admin/products/:idproduct", function($idproduct){
	#verifica login
	User::verifyLogin();
	#cria um novo produto
	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();
	#fazer o upload do arquivo
	$product->setPhoto($_FILES["name"]);
	#redireciona para a tela de produtos
	header('Location: /admin/products');
	exit;

});


?>