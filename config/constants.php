<?php
// Общие генеральные исключения
const ERR_UNKNOWN  = [10000, 'Неизвестная ошибка'];
const ERR_INTERNAL = [10001, 'Внутренняя ошибка'];
const ERR_NOTPOST  = [10002, 'Не POST-запрос'];
const ERR_NOTFOUND = [10003, 'Метод не найден'];
const ERR_PARAM_NF = [10004, 'Параметр не найден'];

// Авторизация
const ERR_AUTH_INVCREDENTIALS = [20001, 'Неправильные данные'];
const ERR_AUTH_NOSESSION      = [20002, 'Не передан ключ доступа'];
const ERR_AUTH_INVSESSION     = [20003, 'Ключ доступа некорректный'];

// Регистрация
const ERR_REG_INVALIDDATA       = [30001, 'Некорректные данные'];
const ERR_REG_ALREADYREGISTERED = [30002, 'Пользователь уже зарегистрирован'];
