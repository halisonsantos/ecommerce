<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{
	#criando o método de listar
	public static function listAll()
	{

		$sql = new Sql();
		#virá os dados das duas tabelas ordenadas pelo nome das pessoas e retornar pra rota
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}
	#método salvar
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));
		#retorna uma linha de resultados e coloca nesse setData
		$this->setData($results[0]);
		#Atualizando as categorias no site principal
		Category::updateFile();

	}
	#
	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[
			':idcategory'=>$idcategory
		]);
		#o resultado será aplicado no indice 0
		$this->setData($results[0]);

	}
	#delete
	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",[
			':idcategory'=>$this->getidcategory()
		]);
		#Atualizando as categorias no site principal
		Category::updateFile();
	}
	#método que atualiza as categorias do site
	public static function updateFile()
	{
		#quais as categorias que tem no banco de dados
		$categories = Category::listAll();
		#montar o html
		$html = [];
		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		#salvando o arquivo
		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('', $html));
	}
	#método que trás todos os produtos que por padrão estão relacionado
	public function getProducts($related = true)
	{

		$sql = new Sql();
		#produtos relacionados com a categoria
		if ($related === true) {
			
			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
				    FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			",[
				':idcategory'=>$this->getidcategory()
			]);

		}else{
		#produtos que não estão relacionados com a categoria
			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
				    FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory
				);
			",[
				':idcategory'=>$this->getidcategory()
			]);

		}

	}
	#função para retornar produtos das categorias por paginação
	public function getProductsPage($page = 1, $itemsPerPage = 3)
	{
		#calculo para a paginação
		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();
		#resultados dos produtos
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start, $itemsPerPage;
		",[
			':idcategory'=>$this->getidcategory()
		]);
		#resultado da quantidade de produtos
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
		#retorna produtos, total de produtos e quantidade de páginas
		return[
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
	}
	#função para adicionar produto na categoria
	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",[
			':idcategory'=> $this->getidcategory(),
			':idproduct'=> $product->getidproduct()
		]);

	}
	#função para remover o produto da categoria
	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",[
			':idcategory'=> $this->getidcategory(),
			':idproduct'=> $product->getidproduct()
		]);

	}


}

?>