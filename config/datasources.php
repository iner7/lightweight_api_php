<?php

// Константы для переключения между базами данных
const DBN_LOCAL = 'local';

// Источники (они же) базы данных
// Работа с ними производится с помощью функций из lib/db.php
// с использованием PDO

// Пример записи:
// 'название_источника_данных' =>
//          [ 'host' => 'драйвер_pdo:параметры_подключения',
//            'user' => 'пользователь_БД',
//            'pass' => 'П@rоLЪ' ]
$CONFIG_DataSources = [
    'local' => [ 'host' => 'mysql:host=localhost;dbname=example',
                 'user' => 'user1',
                 'pass' => 'password1' ],
];

// Источники (они же) записей LDAP
// Работа с ними производится с помощью функций из lib/ldap.php

// Пример записи:
// 'название_источника_данных' =>
//               [ 'host' => 'адрес LDAP-сервера',
//                 'dn'   => 'DN-запись',
//                 'pass' => 'П@rоLЪ' ],
$CONFIG_LdapSources = [
    'local' => [ 'host' => 'localhost',
                 'dn'   => 'cn=admin,ou=example,ou=org',
                 'pass' => 'password1' ],
];
