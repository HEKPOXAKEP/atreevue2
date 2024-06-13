-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.7.39 - MySQL Community Server (GPL)
-- Операционная система:         Win32
-- HeidiSQL Версия:              12.3.0.6589
-- --------------------------------------------------------
/*
  ВНИМАНИЕ!
  ---------
  У зарегистрированных пользоваетелей admin1 и админ2
  пароли qwerty1 и qwerty2 соответственною
*/
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Дамп структуры базы данных atreevue2
DROP DATABASE IF EXISTS `atreevue2`;
CREATE DATABASE IF NOT EXISTS `atreevue2` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `atreevue2`;

-- Дамп структуры для таблица atreevue2.datatree
CREATE TABLE IF NOT EXISTS `datatree` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `parentIdx` int(11) DEFAULT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `descr` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`idx`),
  KEY `FK_datatree_datatree` (`parentIdx`),
  CONSTRAINT `FK_datatree_datatree` FOREIGN KEY (`parentIdx`) REFERENCES `datatree` (`idx`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;

-- Дамп данных таблицы atreevue2.datatree: ~20 rows (приблизительно)
INSERT INTO `datatree` (`idx`, `parentIdx`, `title`, `descr`) VALUES
	(1, NULL, 'Root 1', 'Root 1 descr'),
	(2, 1, 'Node 1.1', 'Node 1.1 descr'),
	(3, 1, 'Node 1.2', 'Node 1.2 descr'),
	(4, 1, 'Node 1.3', 'Node 1.3 descr'),
	(5, 3, 'Node 1.2.1', 'Node 1.2.1 descr'),
	(6, 3, 'Node 1.2.2', 'Node 1.2.2 descr'),
	(7, 3, 'Node 1.2.3', 'Node 1.2.3 descr'),
	(8, 3, 'Node 1.2.4', 'Node 1.2.4 descr'),
	(9, 6, 'Node 1.2.2.1', 'Node 1.2.2.1 descr'),
	(10, 6, 'Node 1.2.2.2', 'Node 1.2.2.2 descr'),
	(11, NULL, 'Root 2', 'Root 2 descr'),
	(13, 15, 'Node 3.1', 'Node 3.1 descr'),
	(15, NULL, 'Корневой узел 3', 'Root 3 descr'),
	(16, 15, 'Объект 3.2', 'Описание объекта 3.2'),
	(17, 11, 'Объект 2.1', 'Описание объекта 2.1'),
	(18, 17, 'Объект 2.1.1', 'Описание объекта 2.1.1'),
	(19, 18, 'Объект 2.1.1.1', 'Описание объекта 2.1.1.1'),
	(20, 19, 'Объект 2.1.1.1.1', 'Описание объекта 2.1.1.1.1'),
	(21, 19, 'Объект 2.1.1.1.2', 'Описание объекта 2.1.1.1.2'),
	(22, 7, 'Объект 1.2.3.1', 'Описание объекта 1.2.3.1');

-- Дамп структуры для процедура atreevue2.getUsers
DELIMITER //
CREATE PROCEDURE `getUsers`(
	IN `aIdx` INT
)
BEGIN
  /*
    Возвращает список зарегистрированных юзеров
    или данные одного юзера по его idx.
  */
  if (aIdx is null) then
    -- возвращаем список
    select idx,login,passwd
    from users;
  else
    -- возвращаем данные одного юзера
    select idx,login,passwd
    from users
    where idx =aIdx;
  end if;
END//
DELIMITER ;

-- Дамп структуры для процедура atreevue2.registerUser
DELIMITER //
CREATE PROCEDURE `registerUser`(
	IN `aLogin` VARCHAR(50),
	IN `aPasswd` VARCHAR(250)
)
BEGIN
  /*
    Регистрация нового юзера.
  */
  declare i int;
  declare newIdx int;

  set i=(select count(*) from users where login =aLogin);
  
  if (i !=0) then
    -- юзер с таким логином уже есть
    select -1971 as err,concat('Login alreadt exists "',aLogin,'"') as msg;
  else
    insert into users(login,passwd)
    values(aLogin,sha2(aPasswd,224));
    
    select last_insert_id() into newIdx;
    
    select newIdx as idx,0 as err,concat('User "',aLogin,'" registered ok') as msg;
  end if;
END//
DELIMITER ;

-- Дамп структуры для таблица atreevue2.users
CREATE TABLE IF NOT EXISTS `users` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `passwd` varchar(250) NOT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `idxLogin` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Дамп данных таблицы atreevue2.users: ~2 rows (приблизительно)
INSERT INTO `users` (`idx`, `login`, `passwd`) VALUES
	(1, 'admin1', 'a3ed6082feb67d0b8561b6bd314e447d4b61c2371ef5b51e069a22bf'),
	(2, 'admin2', '6030ac83a0166e3ce073811a223d7a673ca3cfa67a4926fa57907f65');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
