<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

#rota 1
$app->get('/', function() {
    #listando os produtos 
    $products = Product::listAll();

	$page = new Page();

	$page->setTpl("index",[
		'products'=>Product::checkList($products)
	]);
});
#rotas das categorias da página inicial ao clicar
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();
	#carregando a categoria
	$category->get((int)$idcategory);
	#volta a página do site
	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())

	]);
});

?>