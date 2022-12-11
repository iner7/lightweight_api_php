<?php

// Параметры
require_once 'config/common.php';
require_once 'config/constants.php';
require_once 'config/datasources.php';

// Библиотеки
require_once 'lib/db.php';
require_once 'lib/ldap.php';

// Генеральные функции
require_once 'glib/exceptions.php';
require_once 'glib/misc.php';
require_once 'glib/general.php';
require_once 'glib/method_parser.php';
require_once 'glib/method_execute.php';
require_once 'glib/responder.php';

// Модуль авторизаций
require_once 'auth.php';

// Проверить на предмет POST-запроса
// Аналогичное можно проверять с помощью nginx, не нагружая PHP-FPM
check_post();

// Получить название метода
$method = getPath();

// Исключения для работы со входом и регистрацией
if ($method == 'auth')     custom_auth();
if ($method == 'logout')   custom_logout();
if ($method == 'register') custom_reg();

// Инициировать сессию
check_and_init_auth();

// Получить подразделение и название метода
$method_parm = get_method_parm($method);

// Исполнить метод
$Response = execute_method($method_parm);

// Отправить ответ
sendResponseAndDie(['response' => $Response]);
