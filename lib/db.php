<?php

// Глобальное хранение подключений к базам данных
$GEN_DsConnects = [];

/**
 * Подключение к источнику базы данных
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_DataSources)
 * @param bool $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return ИСТИНА, если успешно; ЛОЖЬ, если произошла ошибка
 */
function DB_connect(string $ds, bool $genexception = true) {
    global $CONFIG_DataSources, $GEN_DsConnects;

    if ( isset($GEN_DsConnects[$ds]) ) return true;

    try {
        if ( isset($CONFIG_DataSources[$ds]['user']) && isset($CONFIG_DataSources[$ds]['pass']) )
            $DB_c = new PDO($CONFIG_DataSources[$ds]['host'],
                            $CONFIG_DataSources[$ds]['user'],
                            $CONFIG_DataSources[$ds]['pass']);
        else {
            $DB_c = new PDO($CONFIG_DataSources[$ds]['host']);
        }
        $DB_c->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $GEN_DsConnects[$ds] = $DB_c;
    } catch (Exception $e) {
        if ($genexception) throw $e;
        else return false;
    }
    return true;
}

/**
 * Запрос к источнику базы данных
 * 
 * @param string $ds Название источника данных (см. значение переменной $CONFIG_DataSources)
 * @param string $query Содержание запроса
 * @param Array  $params Параметры запроса (экранируются автоматом)
 * @param bool   $lastid Возвращать идентификатор добавленной записи?
 * @param bool   $genexception Генерировать исключение? Если нет, то вернётся ЛОЖЬ
 * 
 * @return Результат выполнения, если успешно, в виде ассоциативного массива: "r" - результат, "l" - идентификатор добавленной записи; Содержание ошибки в виде ассоциативного массива с ключом "e"
 */
function DB_query(string $ds, string $query, Array $params, bool $lastid = false, bool $genexception = true) {
    global $GEN_DsConnects;
    
    try {
        $DB_c = $GEN_DsConnects[$ds];
        $DB_q = $DB_c->prepare($query);
        $DB_q->execute($params);
        
        if ($DB_q->errorCode() != 0) {
            throw new Exception($DB_q->errorInfo()[2]);
        } else {
            return [
                'r' => $DB_q->fetchAll(PDO::FETCH_ASSOC),
                'l' => ( $lastid ?  $DB_c->lastInsertId() : null)
            ];
        }
    } catch (Exception $e) {
        if ($genexception) throw new Exception($e->getMessage());
        else return [ 'e' => $e->getMessage() ];
    }
}
