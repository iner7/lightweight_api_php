<?php

/**
 * Оформить результат неудачного выполнения команды
 * 
 * @param int     $errcode            Код ошибки
 * @param string  $errmsg             Сообщение об ошибке
 * @param any     $debugInformation   Отладочная информация
 * 
 * @return string Возвращаемая строка
 */
function generateResponseError(int $errcode, string $errmsg, $debugInformation = null) {
    $rsp = [ 'code' => $errcode, 'message' => $errmsg ];
    if (FLAG_DEBUG) $rsp['debug'] = $debugInformation;
    return [ 'error' => $rsp ];
}

/**
 * Отправить результат и ЗАВЕРШИТЬ выполнение скрипта
 * 
 * @param array   $response  Отправляемый ответ
 */
function sendResponseAndDie(array $response) {
    header('Content-Type: application/json; charset=utf-8');
    die( json_encode($response, JSON_UNESCAPED_UNICODE) );
}
