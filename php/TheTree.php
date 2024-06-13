<?php
/*
  Класс работы с деревом
*/
require_once('bootstrap.php');
require_once('db.php');
require_once('Auth.php');

class TheTree
{

  /*
    Возвращает поддерево для Пользователя
  */
  public function getUserSubtree() {
    if (is_null($_POST['parentIdx']) || (strtolower($_POST['parentIdx']) =='null'))
      $pidx = null;
    else
      $pidx = $_POST['parentIdx'];

    if (is_null($pidx)) {
      // выбираем корневые узлы
      $stmt=dbh()->prepare(
        'select idx,null as parentIdx,title,descr,0 as lvl from datatree where parentIdx is null'
      );
      $stmt->execute();
    } else {
      // выбираем узлы родителя $pidx
      $stmt = dbh()->prepare(
        'select idx,parentIdx,title,descr,-1 as lvl from datatree where parentIdx=:aParentIdx'
      );
      $stmt->execute(['aParentIdx' => $pidx]);
    }

    $rez=[];

    while ($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
      // вычисляем уровень узла, если он не корневой
      if ($row['lvl'] ===-1)
        $row['lvl']=$this->calcNodeLevel($row['idx']);

      // считаем к-во детей
      $row['childrenCnt']=$this->hasChildren($row['idx']);

      $rez[]=$row;
    }

    $stmt->closeCursor();

    return($rez);
  }

  /*
    Возвращает всё дерево для Админа
  */
  public function getAdminTree() {
    $pidx=null; // начинаем с корневых узлов
    $rez=[];    // результирующий массив дерева

    $this->treeRecursion($pidx,$rez);
    return($rez);
  }

  /*
    Рекурсия для обхода всего дерева
  */
  private function treeRecursion($pidx,&$rez) {
    if (is_null($pidx)) {
      // выбираем корневые узлы
      $stmt = dbh()->prepare(
        'select idx,null as parentIdx,title,descr,0 as lvl from datatree where parentIdx is null'
      );
      $stmt->execute();
    } else {
      // выбираем узлы родителя $pidx
      $stmt = dbh()->prepare(
        'select idx,parentIdx,title,descr,-1 as lvl from datatree where parentIdx=:aParentIdx'
      );
      $stmt->execute(['aParentIdx'=>$pidx]);
    }

    $a=$stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    foreach($a as $row) {
      // вычисляем уровень узла, если он не корневой
      if ($row['lvl'] ===-1)
        $row['lvl']=$this->calcNodeLevel($row['idx']);

      // считаем к-во детей
      $row['childrenCnt']=$this->hasChildren($row['idx']);

      // добвляем этот узел в результирующий массив
      $rez[]=$row;

      // если есть дети
      if ($row['childrenCnt'] >0) {
        $this->treeRecursion($row['idx'],$rez);
      }
    }
  }

  /*
    Вычисляет уровень узла, начиная с 0
  */
  protected function calcNodeLevel($idx) {
    $lvl=0;
    $this->calcLvlRecursion($idx,$lvl);

    return($lvl);
  }

  /*
    Рекрусия для вычисления уровня узла.
  */
  private function calcLvlRecursion($idx,&$lvl) {
    $lvl++;

    $stmt = dbh()->prepare('select parentIdx as pidx from datatree where idx=:aIdx');
    $stmt->execute(['aIdx' => $idx]);
    $a = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!is_null($a['pidx']))
      $this->calcLvlRecursion($a['pidx'],$lvl);
  }

  /*
    Возвращает к-во детей узла
  */
  protected function hasChildren($pidx) {
    $stmt=dbh()->prepare('select count(*) as cnt from datatree where parentIdx=:pidx');
    $stmt->execute(['pidx'=>$pidx]);
    $a=$stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    return($a['cnt']);
  }

  /*
    Создание, редактирование, удаление узла дерева.

    opCode: 1 - создать, 2 - редактировать, 3 - удалить
  */
  public function editTreeNode() {
    if (!auth()->isAuth()) {
      exit(json_encode(array('err' => -1971, 'msg' => 'У вас нет прав для выполнения этой операции.')));
    }

    $opCode=isset($_POST['opCode']) ? (strtolower($_POST['opCode']) ==='null' ? null : $_POST['opCode']) : null;

    $pidx=isset($_POST['parentIdx']) ? (strtolower($_POST['parentIdx']) ==='null' ? null : $_POST['parentIdx']) : null;
    $idx=isset($_POST['idx']) ? (strtolower($_POST['idx']) ==='null' ? null : $_POST['idx']) : null;
    $title=trim(isset($_POST['title']) ? $_POST['title'] : '-none-');
    $descr=trim(isset($_POST['descr']) ? $_POST['descr'] : '');

    switch($opCode) {
      case 1:
        return($this->newNode($pidx,$title,$descr));
        break;
      case 2:
        return($this->updNode($idx,$pidx,$title,$descr));
        break;
      case 3:
        return($this->delNode($idx));
        break;
      default:
        return(['err'=>-1971,'msg'=>'Неверный код операции: '.($opCode ===null) ? 'NULL' : $opCode]);
    }
  }

  /*
    Создаём новый узел
  */
  public function newNode($pidx,$title,$descr) {
    $stmt=dbh()->prepare(
      'insert into datatree(parentIdx,title,descr) values(:aParentIdx,:aTitle,:aDescr);'
    );
    $stmt->execute([
      'aParentIdx'=>$pidx,
      'aTitle'=>$title,
      'aDescr'=>$descr
    ]);
    $newIdx=dbh()->lastInsertId();
    $stmt->closeCursor();

    return(['err'=>0,'msg'=>"Создан узел idx={$newIdx}"]);
  }

  /*
    Изменяем параметры узла
  */
  public function updNode($idx, $pidx, $title, $descr) {
    if ($idx ==$pidx)
      return(['err'=>-1971,'msg'=>'Нельзя переместить узел под самомго себя']);

    $stmt=dbh()->prepare(
      'update datatree set parentIdx=:aParentIdx,title=:aTitle,descr=:aDescr where idx=:aIdx'
    );
    $stmt->execute([
      'aIdx'=>$idx,
      'aParentIdx'=>$pidx,
      'aTitle'=>$title,
      'aDescr'=>$descr
    ]);
    $stmt->closeCursor();

    return(['err'=>0,'msg'=>"Узел idx={$idx} обновлён"]);
  }

  /*
    Удаляем узел и всех его детей
  */
  public function delNode($idx) {
    $stmt=dbh()->prepare('delete from datatree where idx=:aIdx');
    $stmt->execute(['aIdx'=>$idx]);
    $stmt->closeCursor();

    return(['err'=>0,'msg'=>"Узел idx={$idx} и все его потомки удалёны"]);
  }
}  /// --- class TheTree

/*
  Фабрикует экземпляр класса TheTree
*/
function tree() {
  static $t=null;

  if (is_null($t))
    $t=new TheTree();

  return($t);
}
?>
