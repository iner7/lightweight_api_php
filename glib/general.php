<?php

/**
 * Проверить на POST-запрос и в случае несоответствия генерировать исключение
 */
function check_post() {
    if (!FLAG_REQUIRE_POST) return;
    if ($_SERVER['REQUEST_METHOD'] != 'POST') throw new ApiException(ERR_NOTPOST);
}

/**
 * Получить API-путь (без префикса)
 *
 * @return string Чистый API-путь
 */
function getPath() {
    $x = $_SERVER['DOCUMENT_URI'];
    $y = dirname($x);
    $r = substr($x, strlen($y), strlen($x));
    if ($r[0] == '/') $r = substr($r, 1, strlen($r));
    return $r;
}
