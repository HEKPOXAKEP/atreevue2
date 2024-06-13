<?php
/*
  Авторизация пользователя
*/

require_once('bootstrap.php');
require_once('Auth.php');

exit(json_encode(auth()->doAuth()));
?>
