<?php

// Глобальное хранение подключений к LDAP
$GEN_LdapConnects = [];

/**
 * Экранировать строку для использования в LDAP
 * 
 * @param string $str Строка
 * @return Возвращаемая строка
 */
function LDAPESC(string $str) {
    return ldap_escape($str, '', 0);
}

/**
 * Привести результат поиска в LDAP в более-менее приемлемый вид для интерпретирования
 * 
 * @param Array $dnx Строка
 * @return Возвращаемая строка
 */
function LDAPVTOARR($dnx) {
    if ($dnx == null) return [];
    $a = [];
    for ($i = 0; $i < $dnx['count']; $i++) $a[] = $dnx[ (string)$i ];
    return $a;
}

/**
 * Хешировать пароль
 * 
 * @param string $password Хешируемый пароль
 * @return Хешированный пароль
 */
function LDAPHASHPW($password) {
    $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 16)), 0, 16);
    return '{SSHA}'.base64_encode(sha1($password.$salt, TRUE).$salt);
}

/**
 * Подключение к источнику данных LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_connect(string $ds, bool $genexception = true) {
    global $CONFIG_LdapSources, $GEN_LdapConnects;

    if ( isset($GEN_LdapConnects[$ds]) ) return true;

    try {
        $ldapconn = ldap_connect($CONFIG_LdapSources[$ds]['host']);
        if (!$ldapconn) throw new Exception('LDAP ERROR1: '.ldap_error($ldapconn));
        
        $ldapoperation = ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 10);
        if (!$ldapoperation) throw new Exception('LDAP ERROR2: '.ldap_error($ldapconn));

        $ldapbind = ldap_bind($ldapconn, $CONFIG_LdapSources[$ds]['dn'], $CONFIG_LdapSources[$ds]['pass']);
        if (!$ldapbind) throw new Exception('LDAP ERROR3: '.ldap_error($ldapconn));
        $GEN_LdapConnects[$ds] = $ldapconn;
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }

    return true;
}

/**
 * Поиск записей в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $base DN корневой записи, от которой вести поиск
 * @param string $filter Фильтр поиска
 * @param Array $attributes Какие атрибуты вернуть? Если пустой массив - возвращается всё
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return Технический массив или ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_search(string $ds, string $base, string $filter, Array $attributes = [], bool $genexception = true) {
    global $GEN_LdapConnects;
    $rst = false;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        $results = ldap_search($ldapconn, $base, $filter, $attributes);
        if (!$results) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
        $rst = ldap_get_entries($ldapconn, $results);
        ldap_free_result($results);
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return $rst;
}

/**
 * Создание записи в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $dn DN записи
 * @param Array $parameters Атрибуты записи. Обязательно должен быть objectclass
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_add_entry(string $ds, string $dn, Array $parameters, bool $genexception = true) {
    global $GEN_LdapConnects;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        $ldapoperation = ldap_add($ldapconn, $dn, $parameters);
        if (!$ldapoperation) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}

/**
 * Удаление записи в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $dn DN записи
 * @param bool $recursive    Удалять подзаписи рекурсивно?
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_delete_entry(string $ds, string $dn, bool $recursive = false, bool $genexception = true) {
    global $GEN_LdapConnects;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        if ($recursive) {
            $sr = ldap_list($ldapconn, $dn, 'objectclass=*', ['']);
            $info = ldap_get_entries($ldapconn, $sr);
            for ($i=0; $i < $info['count']; $i++) {
                $result = DSLDAP_delete_entry($ds, $info[$i]['dn'], $recursive, $genexception);
                if(!$result) {
                    throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
                }
            }
        }
        $ldapoperation = ldap_delete($ldapconn, $dn);
        if (!$ldapoperation) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}

/**
 * Переименование и/или перемещение записи в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $dn Текущий DN записи
 * @param string $new_rdn Новый DN записи (неполный: только ключевой параметр)
 * @param string $new_parent Новый DN родительской записи
 * @param string $delete_old_rdn Удалять старую RDN?
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_rename_entry(string $ds, string $dn, string $new_rdn, string $new_parent, bool $delete_old_rdn = true, bool $genexception = true) {
    global $GEN_LdapConnects;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        $ldapoperation = ldap_rename($ldapconn, $dn, $new_rdn, $new_parent, $delete_old_rdn);
        if (!$ldapoperation) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}

/**
 * Изменение параметров записи в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $dn DN записи
 * @param Array $parameters Параметры
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_attr_modify(string $ds, string $dn, Array $parameters, bool $genexception = true) {
    global $GEN_LdapConnects;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        $ldapoperation = ldap_mod_replace($ldapconn, $dn, $parameters);
        if (!$ldapoperation) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}

/**
 * Добавление параметров записи в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $dn DN записи
 * @param Array $parameters Параметры
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_attr_add(string $ds, string $dn, Array $parameters, bool $genexception = true) {
    global $GEN_LdapConnects;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        $ldapoperation = ldap_mod_add($ldapconn, $dn, $parameters);
        if (!$ldapoperation) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}

/**
 * Удаление параметров записи в LDAP
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_LdapSources)
 * @param string $dn DN записи
 * @param Array $parameters Параметры
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DSLDAP_attr_delete(string $ds, string $dn, Array $parameters, bool $genexception = true) {
    global $GEN_LdapConnects;
    try {
        $ldapconn = $GEN_LdapConnects[$ds];
        $ldapoperation = ldap_mod_del($ldapconn, $dn, $parameters);
        if (!$ldapoperation) throw new Exception('LDAP ERROR: '.ldap_error($ldapconn));
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}
