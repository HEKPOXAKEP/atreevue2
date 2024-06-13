<?php

// параметры подключения к БД
const
  DBC=[
    'host'=>'localhost',
    'dbname'=>'aTreeVue2',
    'user'=>'root',
    'pwd'=>'',
    'encoding'=>'utf8'
  ];

/*
 Возвращает объект PDO соединения с БД
*/
function dbh() {
  static $dbh=null;

  if (is_null($dbh)) {
    $dsn='mysql:host='.DBC['host'].';dbname='.DBC['dbname'].';charset='.DBC['encoding'];

    $dbh=new PDO($dsn,DBC['user'],DBC['pwd']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  return($dbh);
}
?>
