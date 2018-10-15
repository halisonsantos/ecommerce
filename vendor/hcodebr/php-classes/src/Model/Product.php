<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model{
	#criando o método de listar
	public static function listAll()
	{

		$sql = new Sql();
		#virá os dados das duas tabelas ordenadas pelo nome das pessoas e retornar pra rota
		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}
	#método salvar
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));
		#retorna uma linha de resultados e coloca nesse setData
		$this->setData($results[0]);

	}
	#
	public function get($idproduct)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",[
			':idproduct'=>$idproduct
		]);
		#o resultado será aplicado no indice 0
		$this->setData($results[0]);

	}
	#delete
	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct",[
			':idproduct'=>$this->getidproduct()
		]);

	}
	#checa se o produto tem uma foto
	public function checkPhoto()
	{
		#se a imagem exitir sendo o nome o número do id retorna ela, se não retorna uma imagem padrão
		if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg")){

			$url =  "/res/site/img/products/".$this->getidproduct().".jpg";
		}else{

			$url =  "/res/site/img/product.jpg";
		}
		#setar a foto
		return $this->setdesphoto($url);


	}

	#reescrever o método getValues()
	public function getValues()
	{
		#chama o método para checar se exite photo
		$this->checkPhoto();

		$values = parent::getValues();

		return $values;

	}
	#método para setar a foto
	public function setPhoto($file)
	{
		#transforma em um array e o ponto serve pra separar
		$extension = explode('.',$file['name']);
		#a extenção é a ultima posição do array
		$extension = end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;
			
			case 'gif':
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

			case 'png':
			$image = imagecreatefrompng($file["tmp_name"]);
			break;
			
		}
		#variável de destino
		$dist = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."res".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."products".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg";
		#quero que seja um jpg
		imagejpeg($image, $dist);#variável, destino
		#
		imagedestroy($image);
		#para o dado ficar carregado e já ir para a memória do desphoto
		$this->checkPhoto();

	}

}

?>