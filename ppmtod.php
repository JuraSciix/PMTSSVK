<?php

$stdin = fopen("php://stdin", "r");

while (true) {
	$input = trim(fgets($stdin));

	if (!is_numeric($input)) {
		echo "Invalid number.\n";
		continue;
	}
	echo date("d.m.Y H:i:s", ppmtod($input)) . "\n";
}

function ppmtod(float $ppm): int {
	$year = (int) date("Y");
	return $ppm * (($year % 4 ? 365 : 366) * 86400) / 1000 + mktime(0, 0, 0, 1, 1, $year);
}