<?php

/**
 * Вход
 */
function custom_auth() {
    $R_Username = get_required_parameter('username');
    $R_Password = get_required_parameter('password');
    $R_Password = hash('sha384', $R_Password);
    
    DB_connect(DBN_LOCAL);
    $dbr = DB_query(DBN_LOCAL, 'SELECT id FROM users WHERE username = :username AND password = :password;',
                    ['username' => $R_Username,
                     'password' => $R_Password
                    ])['r'];

    // Защита от атак перебора пароля
    // Значительно их замедляет
    if (count($dbr) == 0) {
        usleep( (300 + random_int(0, 800))*1000 );
        throw new ApiException(ERR_AUTH_INVCREDENTIALS);
    }

    $E_UserID = (int)$dbr[0]['id'];
    $E_Token  = rand().time().$R_Password.rand();
    $E_Token  = hash('sha512', $E_Token);

    DB_query(DBN_LOCAL, 'INSERT INTO sessions (user_id, token) VALUES (:user_id, :token);',
             ['user_id' => $E_UserID,
              'token'   => $E_Token
             ]);

    setcookie(AUTH_TOKEN_COOKIE_NAME, $E_Token, 0, '/');
    sendResponseAndDie(['access_token' => $E_Token]);
}

/**
 * Регистрация
 *
 * Для примера используется регистрация по электронной почте и паролю
 */
function custom_reg() {
    $R_EMail = get_required_parameter('email');
    $R_UserName = get_required_parameter('username');
    $R_Password = get_required_parameter('password');
    $R_Password = hash('sha384', $R_Password);
    if (!filter_var($R_EMail, FILTER_VALIDATE_EMAIL)) throw new ApiException(ERR_REG_INVALIDDATA);

    DB_connect(DBN_LOCAL);
    $dbr = DB_query(DBN_LOCAL, 'SELECT id FROM users WHERE email = :chk', ['chk' => $R_EMail])['r'];
    if (count($dbr) != 0) throw new ApiException(ERR_REG_ALREADYREGISTERED);

    DB_query(DBN_LOCAL, 'INSERT INTO users (email, password, username) VALUES (:email, :passwd, :username);',
             ['email'    => $R_EMail,
              'passwd'   => $R_Password,
              'username' => $R_UserName
             ]);

    sendResponseAndDie(['status' => true]);
}

/**
 * Получть ключ доступа (из cookies клиента)
 *
 * @return Ключ доступа
 */
function get_token() {
    if (!isset($_COOKIE[AUTH_TOKEN_COOKIE_NAME])) throw new ApiException(ERR_AUTH_NOSESSION);
    return $_COOKIE[AUTH_TOKEN_COOKIE_NAME];
}

/**
 * Завершение сессии пользователя
 *
 * @return Ключ доступа
 */
function custom_logout() {
    $E_Token = get_token();

    DB_connect(DBN_LOCAL);
    DB_query(DBN_LOCAL, 'UPDATE sessions SET state = 0 WHERE token = :token;', [ 'token' => $E_Token ]);

    setcookie(AUTH_TOKEN_COOKIE_NAME, null, -1);
    sendResponseAndDie(['response' => null]);
}

/**
 * Проверка и инициализация работы с сессией пользователя
 *
 * @return Ключ доступа
 */
function check_and_init_auth() {
    $E_Token = get_token();
    DB_connect(DBN_LOCAL);

    $dbr = DB_query(DBN_LOCAL, 'SELECT user_id FROM sessions WHERE token = :token AND state = 1;', [ 'token' => $E_Token ])['r'];
    if (count($dbr) == 0) throw new ApiException(ERR_AUTH_INVSESSION);

    $GLOBALS['user_id'] = $dbr[0]['user_id'];
}

/**
 * Получить идентификатор пользователя
 *
 * @return Идентификатор пользователя
 */
function get_user_id() {
    return $GLOBALS['user_id'];
}
