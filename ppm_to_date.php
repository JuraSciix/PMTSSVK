<?php

$stdin = fopen("php://stdin", "r");

while (true) {
	$input = fgets($stdin);
	
	echo date("d.m.Y H:i:s\n", from_ppm($input));
}

function from_ppm(int $ppm): string{
	return $ppm * (366 * 86400) / 1000 + mktime(0, 0, 0, 1, 1, 2020);
}