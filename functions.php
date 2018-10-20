<?php 
#formata os preços 
function formatPrice(float $vlprice)
{

	return number_format($vlprice, 2, ",",".");

}

?>