<?php 

use \Hcode\Page;
use \Hcode\Model\Product;

#rota 1
$app->get('/', function() {
    #listando os produtos 
    $products = Product::listAll();
    
	$page = new Page();

	$page->setTpl("index",[
		'products'=>Product::checkList($products)
	]);

});

?>