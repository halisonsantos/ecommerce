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

}

?>