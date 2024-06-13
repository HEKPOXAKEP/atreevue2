<?php
/*
  Создание, редактирование, удаление узла дерева.

  opCode: 1 - создать, 2 - редактировать, 3 - удалить
*/
require_once('bootstrap.php');
require_once('TheTree.php');

exit(json_encode(tree()->editTreeNode()));
?>
