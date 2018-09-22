<?php 

namespace Hcode;

class Model{
	#terá todos os valores do campo do objeto
	private $values = [];
	#verifiar os métodos chamados
	public function __call($name, $args)
	{
		#saber se é um metodo get ou set
		$method = substr($name, 0, 3);
		#qual é o nome do campo que foi chamado
		$fieldName = substr($name, 3, strlen($name));

		switch ($method) {
			#se encontrar retorna alguma coisa
			case 'get':
				return $this->values[$fieldName];		
			break;
			
			case 'set':
				#procurar e aplicar o valor 
				$this->values[$fieldName] = $args[0];
			break;
			
		}
	}
	#método set dinâmico
	public function setData($data = array())
	{

		foreach ($data as $key => $value) {
			#concateando o nome set com o valor da chave e na frente o valor
			$this->{"set".$key}($value);

		}

	}
	#método get dinâmico
	public function getValues()
	{

		return $this->values;

	}

}

?>