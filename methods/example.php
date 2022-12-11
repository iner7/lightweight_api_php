<?php

class API_example {
    protected int $user_id;

    public function __construct(int $tid) {
        $this->$user_id = get_user_id();
    }

    /**
    * Получить
    */
    public function get() {
        DB_connect(DBN_LOCAL);
        $dbr = DB_query(DBN_LOCAL, 'SELECT * FROM objects WHERE owner_id = :owner_id;',
                        [ 'owner_id' => $user_id ])['r'];
        return $dbr;
    }

    /**
    * Добавить
    *
    * @param string $caption Название
    */
    public function add(string $description = '') {
        DB_connect(DBN_LOCAL);
        $dbr = DB_query(DBN_LOCAL, 'INSERT INTO objects (description, owner_id) VALUES (:description, :owner_id);',
                        [ 'description' => $description, 'owner_id' => $user_id ], true, true)['l'];
        return $dbr;
    }

    /**
    * Удалить
    *
    * @param string $id ИД
    */
    public function delete(int $id) {
        DB_connect(DBN_LOCAL);
        DB_query(DBN_LOCAL, 'DELETE FROM objects WHERE id = :id;',
                [ 'id' => $id ], false, true);
    }
}
