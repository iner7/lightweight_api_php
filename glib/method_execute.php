<?php

/**
 * Извлечь параметры в соответствии с методом
 *
 * @param array  $method_parm  Простой массив [ название класса ; название метода ]
 * @return any Результат исполнения метода
 */
function parse_parameters_reflection($refmeth) {
    $method_args = [];

    foreach ($refmeth->getParameters() as $i => $param) {
        $argname = $param->getName();
        $argnull = $param->allowsNull();
        $argopt = $param->isOptional();

        // обязательный параметр
        if (!isset($_REQUEST[$argname]) && $argopt != 1) throw new ApiException(ERR_PARAM_NF);

        // необязательный параметр
        if (isset($_REQUEST[$argname])) $method_args[$argname] = $_REQUEST[$argname];
        else $method_args[$argname] = $param->getDefaultValue();
    }

    return $method_args;
}

/**
 * Выполнить метод
 *
 * @param array  $method_parm  Простой массив [ название класса ; название метода ]
 * @return any Результат исполнения метода
 */
function execute_method($method_parm) {
    // Импорт
    $libfilename = 'methods/'.$method_parm[0].'.php';
    if (!file_exists($libfilename)) throw new ApiException(ERR_NOTFOUND);
    require_once $libfilename;

    // Инициация
    $class_name = 'API_'.$method_parm[0];
    $class_reflection = new ReflectionClass($class_name);
    $constructor = $class_reflection->getConstructor();
    $method_class = NULL;
    $method_name = $method_parm[1];

    // Проверка и выполнение конструктора
    if ($constructor != NULL) {
        $method_args = parse_parameters_reflection($constructor);
        $method_class = $class_reflection->newInstanceArgs($method_args);
    } else {
        $method_class = new $class_name();
    }

    // Поиск метода
    if (!method_exists($method_class, $method_name)) throw new ApiException(ERR_NOTFOUND);

    // Проверка на публичность
    $reflect = new ReflectionMethod($method_class, $method_name);
    if (!$reflect->isPublic()) throw new ApiException(ERR_PARAM_NF);

    // Парсинг параметров
    $method_args = parse_parameters_reflection($reflect);

    // Выполнение
    return call_user_func_array([$method_class, $method_name], $method_args);
}
