<?php

/**
 * Получить IP-адрес клиента
 *
 * @return string IP-адрес клиента
 */
function get_client_ipaddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))            $ip = $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else                                               $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}

/**
 * Проверить строку на допустимые символы (0-9, a-z, A-Z, _).
 *
 * @param string  $str Проверяемая строка
 *
 * @return bool Истина, если не содержит недопустимые символы
 */
function SC_S($str) {
    return preg_match('/^[0-9a-zA-Z_]*$/', $str);
}

/**
 * Получить обязательный параметр
 *
 * @param string  $str Название параметра
 *
 * @return string Значение параметра
 */
function get_required_parameter($paramname) {
    if (!isset($_REQUEST[$paramname])) throw new ApiException(ERR_PARAM_NF);
    return $_REQUEST[$paramname];
}
