<?php
/*
  Получение части (для Пользователя) или всего (для Админа) дерева
*/

require_once('bootstrap.php');
require_once('Auth.php');
require_once('TheTree.php');

if (auth()->isAuth())
  exit(json_encode(tree()->getAdminTree()));
else
  exit(json_encode(tree()->getUserSubtree()));
?>
