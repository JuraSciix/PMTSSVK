<?php

const ACCESS_TOKEN = "";
const API_VERSION  = "5.700";
const SCRIPT_URL   = "https://api.vk.com/method/status.set?access_token=%s&v=%s&text=%s";

const UPDATE_PERIOD = 60; // минимум 60 секунд, дальше капча
const STATUS_TEXT = "2020 год прошел на %s‰";

while (true) {
	status_set(sprintf(STATUS_TEXT, get_2020_ppm()));
	sleep(UPDATE_PERIOD);
}

function get_2020_ppm(): float{
	return 1000 / (366 * 86400) * (time() - mktime(0, 0, 0, 1, 1, 2020));
}

function status_set(string $text): void{
	$response = json_decode(file_get_contents(sprintf(SCRIPT_URL, ACCESS_TOKEN, API_VERSION, urlencode($text))), true);
	
	if (!$response) {
		return;
	}
	if ($error = @$response['error']) {
		console("status.set failed: [{$error['error_code']}] {$error['error_msg']}");
	} else {
		console("status.set: $text");
	}
}

function console(string $message): void{
	echo "[" . date("d.m.Y H:i:s") . "] $message\n";
}