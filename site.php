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
	#se o usuário clicar na página será definido o número da página se não o parão é 1
	$page = (isset($_GET['page']))? (int)$_GET['page'] : 1;

	$category = new Category();
	#carregando a categoria
	$category->get((int)$idcategory);
	#várias informações dentro do array e recebe a página definida
	$pagination = $category->getProductsPage($page);
	#criando o array pages
	$pages = [];
	#o laço vai até o total de páginas com o link da página e o número da página
	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}
	#volta a página do site
	$page = new Page();

	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
});
#rota dos detalhes do produto
$app->get("/products/:desurl",function($desurl){
	#instanciando produto
	$product = new Product();
	#
	$product->getFromURL($desurl);
	#instanciando uma nova pagina 
	$page = new Page();
	#tamplate sendo chamado
	$page->setTpl("product-detail",[
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);
});

?>