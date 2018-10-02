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
	}

}

?>