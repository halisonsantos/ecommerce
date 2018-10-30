<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{
	#criando uma constante
	const SESSION = "User";
	#chave de criptografia 
	const SECRET = "HcodePhp7_Secret";
	#método pra carregar o usuário pro carrinho
	public static function getFromSession()
	{
		#cria um objeto
		$user = new User();
		#verificar se a sessão existe
		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){
			#carrega o usuário
			$user->setData($_SESSION[User::SESSION]);
		} 
		#retorna o usuário
		return $user;
	}
	#checar se está logado
	public function checkLogin($inadmin = true)
	{
		if(
			#verifica se não foi definida a session com a constante session
			!isset($_SESSION[User::SESSION])
			||#ou se ela for falsa
			!$_SESSION[User::SESSION]
			||#ou se o id do usuário não for maior que 0
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		){	
			#não está logado
			return false;
		}else{
			#verifica se está na rota da adm e se é um adm
			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){

				return true;
			#ele está logado mas não é necessáriamente um adm
			}else if($inadmin === false){

				return true;
			#se algo não saiu como previsto o usuário não está logado
			}else{

				return false;

			}

		}
	}

	#esse método vai verificar se o login e senha existe no banco 
	public static function login($login, $password){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
				":LOGIN"=>$login
			));
			#verificando se não encontrou o login
			if (count($results) === 0)
			{
				throw new \Exception("Usuário inexistente ou senha inválida.");
			}
			#registro encontrado e armazenado 
			$data = $results[0];
			#verificando a senha do usuário
			if (password_verify($password, $data["despassword"]) === true)
			{
				$user = new User();
				#set é dinâmico e passa o array inteiro
				$user->setData($data);
				#os dados precisa de uma sessão e foi definido numa constante na propria classe
				$_SESSION[User::SESSION] = $user->getValues();

				#retornar o objeto
				return $user;
			
			} else{
				throw new \Exception("Usuário inexistente ou senha inválida.");
			}

	}

	public static function verifyLogin($inadmin = true)
	{
		#verifica realiza as validações do login
		if(User::checkLogin($inadmin)){
			#redirecionar para a tela de login
			header("Location: /admin/login");
			exit;
		}

	}
	#criando método para destruir a sessão (deslogar)
	public static function logout()
	{
		#limpar a session, pois podemos manter outras funções que estejam funcionando.. poderíamos também utilizar o session_unset e passar a sessão
		$_SESSION[User::SESSION] = NULL;

	}
	#criando o método de listar
	public static function listAll()
	{

		$sql = new Sql();
		#virá os dados das duas tabelas ordenadas pelo nome das pessoas e retornar pra rota
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}
	#método cadastrar
	public function save()
	{
		#executar a query
		$sql = new Sql();
		#chamar uma procedure que vai inserir uma pessoa, precisamos saber o id dessa pessoa para poder inserir na tabela de usuários, pegar o id do usuário q retornou fazer um select com os dados juntar tudo e trazer de volta
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()

		));
		#só interessa o primeiro results
		$this->setData($results[0]);

	}

	#busca as informações de um usuário que é chamada para atualizar o mesmo  
	public function get($iduser)
	{
	 
	 $sql = new Sql();
	 
	 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
	 ":iduser"=>$iduser
	 ));
	 $this->setData($results[0]);
	 #$data = $results[0];
	 
	 #$this->setData($data);
	 
	 }

	 public function update()
	 {
	 	#executar a query
		$sql = new Sql();
		#chamar uma procedure que vai inserir uma pessoa, precisamos saber o id dessa pessoa para poder inserir na tabela de usuários, pegar o id do usuário q retornou fazer um select com os dados juntar tudo e trazer de volta
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()

		));
		#só interessa o primeiro results
		$this->setData($results[0]);

	 }
	 #deletar
	 public function delete()
	 {

	 	$sql = new Sql();

	 	$sql->query("CALL sp_users_delete(:iduser)", array(
	 		":iduser"=>$this->getiduser()
	 	));

	 }

	#método statico forgot
	public static function getForgot($email)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array(
			":email"=>$email
		));

		#valida se encontrou o email
		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha!");
		}else
		{
			#guarda o id do usuário em $data
			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"] #tras o ip
			));
			#verificar se criou o results
			if (count($results2) === 0) 
			{
				throw new \Exception("Não foi possível recuperar a senha!");
						
			}else
			{
				$dataRecovery = $results2[0];
				#começa a criptografia da senha, primeiro vamos transformar em base 64
				#caompos do mcrypt_encrypt (tipo de criptografia, chave de criptografia, dados que no caso encripta a chave primaria da tabela de recovry, tipo de criptografia vai usar)
				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));
				#montar o link que será o endereço que vai receber o código
				$link = "http://www.hcodecommerce.com.br:8080/admin/forgot/reset?code=$code";
				#criando o email				
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da Hcode Story", "forgot", array(
					"name"=> $data["desperson"],
					"link"=> $link
				));
				#enviar email
				$mailer->send();
				#retornando $data com os dados do usuário que foi recuperado, pra caso o método preciso pra alguma coisa
				return $data;

			}

		}

	} 
	#Método para validar o código e recuperar de qual usuário pertence o código
	public static function validForgotDecrypt($code)
	{
		#decriptando o code
		$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
		
		$sql = new Sql();
		#select do banco já fazendo as validações
		$results = $sql->select("
			SELECT * 
			FROM tb_userspasswordsrecoveries a 
			INNER JOIN tb_users b USING(iduser) 
			INNER JOIN tb_persons c USING(idperson) 
			WHERE 
			a.idrecovery = :idrecovery
			AND 
			a.dtrecovery IS NULL
			AND
			DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
					":idrecovery"=>$idrecovery
				));
				#se trazer um resultado igual a 0 não resupera a senha
				if (count($results) === 0)
				{
					throw new \Exception("Não foi possível recuperar a senha.");
					
				}else #retorna os resultados da posição 0
				{

					return $results[0];

				}

	}
	#método que informa que o processo de recuperação de senha já foi usado
	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}
	#método para atualizar a nova senha da recuperação de senha
	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()#busca do atributo
		));

	}


}


?>