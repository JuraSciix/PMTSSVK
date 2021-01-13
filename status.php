<?php

const METHOD_URL = "https://api.vk.com/method/status.set?access_token=%s&v=%s&text=%s";
const ACCESS_TOKEN = "";
const API_VERSION = "5.900";

const SLEEP_PERIOD = 60; // минимум 60 секунд, дальше капча
const STATUS_TEXT = "%d год прошел на %s‰";

while (true) {
	status_update();
	sleep(SLEEP_PERIOD);
}

function status_update(): void{
	$year = (int) date("Y");
	$text = sprintf(STATUS_TEXT, $year, dtoppm($year));
	$response = file_get_contents(sprintf(METHOD_URL, ACCESS_TOKEN, API_VERSION, urlencode($text)));
	
	if (!$response) {
		return;
	}
	if ($err = @(json_decode($response, true)['error'])) {
		console("Failed status update [{$err['error_code']}]: {$err['error_msg']}");
	} else {
		console("status updated: $text");
	}
}

function dtoppm(int $year): float {
	return 1000 / (($year % 4 ? 365 : 366) * 86400) * (time() - mktime(0, 0, 0, 1, 1, $year));
}

function console(string $message): void{
	echo "[" . date("d.m.Y H:i:s") . "] $message\n";
}