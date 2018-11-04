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
	#criando uma sessão para msg de erro do webservice
	const SESSION_ERROR = "CartError";
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
	#função que retorna as medidas totais dos produtos do carrinho 
	public function getProductsTotals()
	{
		#instancia o sql
		$sql = new Sql();
		#salva em results o array retornado 
		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL
		",[
			':idcart'=>$this->getidcart()
		]);
		# se retornar algum resultado mostra o $results se não retorna um array vazio para não estourar um erro
		if (count($results) > 0) {
			return $results[0];
		}else{
			return [];
		}

	}
	#função para enviar os dados para calculo de frete no webservice
	public function setFreight($nrzipcode)
	{
		#tirar o ífem caso ele esja digitado
		$nrzipcode = str_replace('-', '', $nrzipcode);
		# retorna as medidas totais dos produtos do carrinho
		$totals = $this->getProductsTotals();
		#se retornar quantidade maior que 0 será realizado o if
		if ($totals['nrqtd'] > 0) {
			#para não dar erro referente o tamanho total do carrinho
			if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if($totals['vllength'] < 16) $totals['vllength'] = 16;
			#guardando as variáveis da query string em um array
			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);
			#função para ler xml pois o webservice vai retornar no formato xml
			$xml = simplexml_load_file(utf8_encode("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs));
			#acessar o objeto 
			$result = $xml->Servicos->cServico;
			#Caso mostre alguma mensagem de erro vamos tratar
			if ($result->MsgErro != '') {
				#definir uma msg de erro
				Cart::setMsgError($result->MsgErro);

			}else{
				#limpar a sessão de erro
				Cart::clearMsgError();
			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);
			#salvar as informações no banco
			$this->save();
			# caso precise pegar alguma informação depois fora desse método
			return $result;
		}else{



		}
	}
	#tratar o valor retornado pelo webservice e salvar no banco
	public static function formatValueToDecimal($value):float
	{
		#onde tiver ponto coloca vazio
		$value = str_replace('.', '', $value);
		#onde tiver vírgula cologa ponto
		return str_replace(',', '.', $value);

	}
	#função inserir erro que for retornado
	public static function setMsgError($msg)
	{

		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}
	#função para pegar o erro da sessao error
	public static function getMsgError()
	{
		#pegamos a menssagem da sessão
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
		#limpamos a sessão neste momento
		Cart::clearMsgError();
		#retornamos a variável, assim o erro não vai ficar lá pra sempre
		return $msg;
	}
	#função para limpar a sessão de erro
	public static function clearMsgError()
	{
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}


}

?>