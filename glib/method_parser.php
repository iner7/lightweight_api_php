<?php

/**
 * Получить разбиение метода (*.*), перед эти проверив его на допустимые символы и на соответствие шаблону (*.*)
 *
 * @param string  $method  Строковое название метода
 *
 * @return array Массив [ название класса ; название метода ]
 */
function get_method_parm($method) {
    $method_parm = explode('.', $method, 2);

    // Если метод не соответствует нотации *.*, отвергнуть
    if (count($method_parm) != 2) throw new ApiException(ERR_NOTFOUND);
    // Если название метода содержит неразрешённые символы, отвергнуть
    if ( !SC_S($method_parm[0]) || !SC_S($method_parm[1]) ) throw new ApiException(ERR_NOTFOUND);

    return $method_parm;
}
