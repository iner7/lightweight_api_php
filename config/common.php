<?php

// Флаг отображения отладочной информации при ошибках.
// Отключите на проде!!!
const FLAG_DEBUG = true;

// Требовать POST-запрос при HTTP-запросе
// Можно отключить для удобства отладки
const FLAG_REQUIRE_POST = false;

// Название cookies, которая будет хранить ключ доступа
const AUTH_TOKEN_COOKIE_NAME = 'access_token';

// Файл для журналирования ошибок
const LOG_ERRORS_FILENAME = 'logs/errors.txt';
