<?php 

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

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
#Rota do layout do carrinho de compras
$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart",[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError() 
	]);

});
#rota para adicionar produto no carrinho
$app->get("/cart/:idproduct/add", function($idproduct){
	#instancia um novo produto
	$product = new Product();
	#carrega o id do produto
	$product->get((int)$idproduct);
	#recupera o carrinho da sessão ou cria um novo
	$cart = Cart::getFromSession();
	#se qtd existir vale a quantidade enviada, se não é 1
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
	#assim chama o método quantas vezes for necessário executa-lo
	for ($i=0; $i < $qtd; $i++) { 
		#adiciona os produtos no carrinho
		$cart->addProduct($product);
	}
	#redireciona para o carrinho
	header("Location: /cart");
	exit;
});
#rota para "remover" produtos no carrinho
$app->get("/cart/:idproduct/minus", function($idproduct){
	#instancia um novo produto
	$product = new Product();
	#carrega o id do produto
	$product->get((int)$idproduct);
	#recupera o carrinho da sessão ou cria um novo
	$cart = Cart::getFromSession();
	#remove uma unidade do produto no carrinho
	$cart->removeProduct($product);
	#redireciona para o carrinho
	header("Location: /cart");
	exit;
});
#rota para "remover" todos os produtos no carrinho
$app->get("/cart/:idproduct/remove", function($idproduct){
	#instancia um novo produto
	$product = new Product();
	#carrega o id do produto
	$product->get((int)$idproduct);
	#recupera o carrinho da sessão ou cria um novo
	$cart = Cart::getFromSession();
	#remove todos os  produtos do mesmo tipo do carrinho
	$cart->removeProduct($product, true);
	#redireciona para o carrinho
	header("Location: /cart");
	exit;
});
#rota para passar os valores totais dos produtos do carrinho para calculo do frete
$app->post("/cart/freight", function(){
	#recupera o carrinho da sessão ou cria um novo
	$cart = Cart::getFromSession();
	#chamando médoto pra passar o cep
	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});

?>