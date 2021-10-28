<?php

require "vendor/autoload.php";
require "functions.php";

use DigitalStar\vk_api\vk_api;
use DigitalStar\vk_api\VkApiException;

/** Токены от страниц. Можно указать при запуске параметром '-t' через запятую. */
const ACCESS_TOKENS = [];

/** Версия VK API. */
const VERSION = "6.999";

/**
 * Текст статуса.
 *
 * {year} - текущий год.
 * {pm} - значение промилле.
 */
const STATUS_UPDATE_TEXT = "🎉 {year} прошел на {pm}‰!";

// === ENTRY POINT ===

global $debugMode;

$opts = getopt("dct:");
$debugMode = isset($opts['d']);

if (isset($opts['c'])) {
    console();
    return;
}
$rawTokens = ACCESS_TOKENS;
if (isset($opts['t'])) {
    $rawTokens = $opts['t'];
}
status($rawTokens);

// === EXIT POINT ===

function console() {
    $stdin = fopen('php://stdin', 'r');
    info("Консольный режим запущен. Мини-инструктаж:");
    info("- Вы вводите число и получаете дату.");
    info("- Вы просто нажимаете ENTER и получаете текущее значение промилле.");
    info("- Вы вводите 'stop' и скрипт завершается.");

    while (true) {
        $input = trim(fgets($stdin));

        if (empty($input)) {
            info("Now => " . date2pm(time()) . "‰");
            continue;
        }
        if (is_numeric($input) === false) {
            if (strcasecmp($input, "stop") === 0) {
                break;
            }
            error("Ожидается целое или дробное число.");
            continue;
        }
        $result = pm2date($input);
        info("$input => " . date("F j, Y, H:i:s", $result));
    }
}

function status($rawTokens) {
    $tokens = create_tokens($rawTokens);
    info("Скрипт запущен.");

    while (($count = count($tokens)) > 0) {
        $time = time();
        $year = (int) date('Y', $time);
        $pm = date2pm($time);
        $text = status_prepare_text($year, $pm);
        debug("Обновление статусов ($count): $text");

        foreach ($tokens as $short => $token) {
            try {
                $token->request("status.set", [
                    "text" => $text
                ]);
            } catch (VkApiException $e) {
                if ($e->getCode() === 5 || $e->getCode() === 3610) {
                    $text = "ошибка авторизации";
                    unset($tokens[$short]);
                } else {
                    $text = "непредвиденная ошибка VK API";
                }
                error("$short: $text: $e");
            }
        }
        $next = pm2date($pm + 0.1);
        $nextSeconds = round($next - $time, 6);
        debug("Статусы обновлены. Следующее обновление через $nextSeconds сек...");

        if (time_sleep_until($next) === false) {
            break;
        }
    }
}

function create_tokens($rawTokens) {
    if (is_array($rawTokens) === false) {
        $rawTokens = explode(",", $rawTokens);
    }
    $tokens = [];

    foreach ($rawTokens as $token) {
        $key = substr($token, 0, 8) . '...'; // 4bf94b2b...
        $tokens[$key] = vk_api::create(trim($token), VERSION);
    }
    return $tokens;
}

function status_prepare_text($year, $pm) {
    $search = ["{year}", "{pm}"];
    $replacement = [$year, $pm];
    return str_replace($search, $replacement, STATUS_UPDATE_TEXT);
}
