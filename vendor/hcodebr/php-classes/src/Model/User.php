<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{
	#criando uma constante
	const SESSION = "User";

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

		if(
			#verifica se não foi definida a session com a constante session
			!isset($_SESSION[User::SESSION])
			||#ou se ela for falsa
			!$_SESSION[User::SESSION]
			||#ou se o id do usuário não for maior que 0
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			#precisa saber se é um admin
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		){
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

}


?>