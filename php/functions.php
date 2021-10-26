<?php

/** Формат даты для логирования. */
const LOG_DATE_FORMAT = "d.m.y-H:i:s";

/**
 * Конвертирует временную метку от начала текущего года из промилле в UNIX.
 *
 * @param float|int $pm Промилле.
 * @return float|int UNIX.
 */
function pm2date($pm) {
    $dateYear = (int) date('Y');
    $dateYearBegin = (int) mktime(0, 0, 0, 1, 1, $dateYear);
    $dateYearEnd = (int) mktime(0, 0, 0, 1, 1, $dateYear + 1);
    return $dateYearBegin + ($pm * ($dateYearEnd - $dateYearBegin) / 1000);
}

/**
 * Конвертирует временную метку UNIX в промилле от начала текущего года.
 *
 * @param float|int $date UNIX.
 * @param int $precision Точность результата.
 * @return float Промилле.
 */
function date2pm($date, $precision = 1) {
    $dateYear = (int) date('Y', $date);
    $dateYearBegin = (int) mktime(0, 0, 0, 1, 1, $dateYear);
    $dateYearEnd = (int) mktime(0, 0, 0, 1, 1, $dateYear + 1);
    $resultPm = 1000 / ($dateYearEnd - $dateYearBegin) * ($date - $dateYearBegin);
    return round($resultPm, $precision, PHP_ROUND_HALF_DOWN);
}

function debug($message) {
    global $debugMode;
    if ($debugMode) {
        printLog("[DEBUG] $message");
    }
}

function info($message) {
    printLog("[INFO] $message");
}

function error($message) {
    printLog("[ERROR] $message");
}

function printLog($message) {
    $str = (string) date(LOG_DATE_FORMAT);
    $str .= ' ';
    $str .= $message;
    echo $str . PHP_EOL;
}
