<?php 

use \Hcode\Page;

#rota 1
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

?>