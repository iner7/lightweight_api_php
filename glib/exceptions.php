<?php

/**
 * Исключения со стороны API
 */
class ApiException extends Exception {
    public function __construct($content = ERR_UNKNOWN, Throwable $previous = null) {
        parent::__construct($content[1], $content[0], $previous);
    }
    public function __toString() {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }
}

/**
 * Обработка фатальных исключений
 */
function CUSTOM_FatalErrorHandle() {
    $error = error_get_last();
    if ($error != null) sendResponseAndDie( generateResponseError(ERR_INTERNAL[0], ERR_INTERNAL[1], $error) );
}

/**
 * Обработка исключений
 *
 * @param Throwable $e Исключение
 */
function CUSTOM_ExceptionHandler(Throwable $e) {
    error_clear_last();
    if (get_class($e) == 'ApiException') {
        sendResponseAndDie(generateResponseError($e->getCode(), $e->getMessage()));
    } else {
        sendResponseAndDie(generateResponseError(ERR_INTERNAL[0], ERR_INTERNAL[1],
            ['ExceptionCode' => $e->getCode(), 'ExceptionMessage' => $e->getMessage()]
        ));
    }
}

/**
 * Журналирование исключения
 *
 * @param string $context Контекст
 * @param Exception $e Исключение
 */
function LogException($context, $e) {
    file_put_contents(LOG_ERRORS_FILENAME, '['.date('Y-m-d H:i:s', time()).'] '.'['.$_SERVER['DOCUMENT_URI'].'] '.'['.$context.'] '.$e->getMessage()."\n", FILE_APPEND);
}

register_shutdown_function('CUSTOM_FatalErrorHandle');
set_exception_handler('CUSTOM_ExceptionHandler');
