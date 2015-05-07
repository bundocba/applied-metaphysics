<?php
header("Content-type: text/css");

for ($i=30; $i<=900; $i++) {
	
	$sizes[$i] = $i;
	
}
foreach ($sizes as $size) {
	
	echo ".size_".$size." {width:".$size."px}\n";
	
}

?>  
