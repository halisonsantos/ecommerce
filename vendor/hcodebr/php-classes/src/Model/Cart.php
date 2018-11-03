<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;
use \Hcode\Model\Product;

class Cart extends Model{
	#criando uma sessão pro carrinho
	const SESSION = "Cart";
	#função para verificar se o carrinho existe ou não 
	public static function getFromSession()
	{
		#Cria um objeto Cart
		$cart = new Cart();
		#existir uma sessão do carrinho e também tem um id
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){
			#carregar o carinho
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		}else{
			#carregar o carrinho a partir do sessionID
			$cart->getFromSessionID();
			#se ele não conseguiu carregar deverá ser carregado um carrinho novo
			if(!(int)$cart->getidcart() > 0){
				#a primeira coisa que deverá ter é o sessionid
				$data = [
					'dessessionid'=>session_id()
				];
				#a verificação do login na rota que não é administrativa for verdadeiro, usuário está logado
				if(User::checkLogin(false)){
					#irá trazer o usuário
					$user = User::getFromSession();
					#para pro $data o id do usuário
					$data['iduser'] = $user->getiduser();
				
				}
				#colocar a variáve $data dentro do objeto $cart
				$cart->setData($data);
				#salva no banco
				$cart->save();
				#carrinho novo precisa botar na sessão
				$cart->setToSession();

			}
		}
		# retorna o carrinho
		return $cart;

	}
	#função para adicionar um carrinho na sessão
	public function setToSession()
	{
		#coloca o carrinho na sessão
		$_SESSION[Cart::SESSION] = $this->getValues();

	}
	#método para verificar se tem algum sessionid no banco
	public function getFromSessionID()
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
			':dessessionid'=>session_id()
		]);
		#para previnir caso venha vazio
		if (count($results) > 0){

			$this->setData($results[0]);

		}
	}
	#método para verificar se tem um carrinho no banco passando um número de id
	public function get(int $idcart)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[
			':idcart'=>$idcart
		]);
		#para previnir caso venha vazio
		if (count($results) > 0){

			$this->setData($results[0]);

		}

	}
	#função para salvar o carrinho
	public function save()
	{
		$sql = new Sql();
		#realizando o select da procedure
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",[
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);
		#setando no objeto o resultados na posição 0 do array
		$this->setData($results[0]);
	}	
	#adiciona produtos no carrinho
	public function addProduct(Product $product)
	{
		#instancia a classe sql
		$sql = new Sql();
		#realiza o insert
		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)",[
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);
	}
	#remove produtos do carrinho
	public function removeProduct(Product $product, $all = false)
	{
		#instancia a classe sql
		$sql = new Sql();
		#usuário pode "excluir" todos os produtos do mesmo tipo 
		if($all){
			#atualiza o banco com a data da remoção 
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL",[
				'idcart'=>$this->getidcart(),
				'idproduct'=>$product->getidproduct()
			]);
		}else{ #ou "excluir" apenas uma unidade
			#atualiza o banco com a data da remoção 
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1",[
				'idcart'=>$this->getidcart(),
				'idproduct'=>$product->getidproduct()
			]);
		}
	}
	#lista os produtos adicionados no carrinho
	public function getProducts()
	{
		#instancia o sql
		$sql = new Sql();
		#realiza a listagem 
		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
			FROM tb_cartsproducts a
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct
		",[
			':idcart'=>$this->getidcart()
		]);
		#tratamento das photos
		return Product::checkList($rows);


	}

}

?>