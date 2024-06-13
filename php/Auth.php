<?php
/*
  Класс работы с аворизацией
*/

require_once('bootstrap.php');
require_once('db.php');

class Auth
{
  protected $login=null;
  protected $passwd=null;

  public function __construct()
  {
    $this->login=isset($_POST['ed-login']) ? (strtolower($_POST['ed-login']) ==='null' ? null : $_POST['ed-login']) : null;
    $this->passwd=isset($_POST['ed-passwd']) ? (strtolower($_POST['ed-passwd']) ==='null' ? null : $_POST['ed-passwd']) : null;
  }

  /*
    Проверка, авторизован ли пользователь
  */
  public function isAuth() {
    return(isset($_SESSION['user_id']));
  }

  /*
    Проверяет, авторизирован ли пользователь и если нет,
    пытается авторизировать.
  */
  public function doAuth() {
    if ($this->isAuth()) {
      return(array('user_id'=>$_SESSION['user_id'],'err'=>0,'msg'=>'Пользователь уже авторизован'));
    }

    if (empty($this->login))
      return(array('err'=>1,'msg'=>'Не заполнено поле "Логин"'));
    if (empty($this->passwd))
      return(array('err'=>2,'msg'=>'Не заполнено поле "Пароль"'));

    $stmt=dbh()->prepare('select idx from users where (login =:aLogin) and (passwd =sha2(:aPasswd,224))');
    $stmt->execute([
      'aLogin'=>$this->login,
      'aPasswd'=>$this->passwd
    ]);

    if (!$stmt->rowCount())
      return(array('err'=>-1971,'msg'=>'Пользователь с такими данными не зарегистрирован'));
    else {
      $row=$stmt->fetch(PDO::FETCH_ASSOC);
      $_SESSION['user_id']=$row['idx'];
      return (array('err' => 0, 'msg' => 'Вы прошли авторизацию'));
    }
  }

  /*
    Разлогининг
  */
  public function doLogout() {
    $_SESSION['user_id']=null;
    return(array('err'=>0,'msg'=>'Вы вышли из приложения'));
  }
}  /// --- class Auth

/*
  Фабрикует статический экземпляр классса авторизации Auth
*/
function auth() {
  static $a=null;

  if (is_null($a))
    $a=new Auth();

  return($a);
}
?>
