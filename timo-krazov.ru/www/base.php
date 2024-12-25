<?php 
// Страница выбора таблицы для редактирования приложения "База данных "Школа""  
//----------------------------------------------------------- 
// Устанавливаем уровень оповещения ошибок    
error_reporting(E_ALL); // Показывать все ошибки и предупреждения 
session_start(); // Начинаем новую или открываем существующую сессию 
/* Проверяем существование параметров сессии - логина и шифрованного пароля 
Если параметров не существует, значит пересылаем на страницу входа adm.php */ 
if ((!isset($_SESSION['idsess'])) || (!isset($_SESSION['hashpasswd'])))  
{ 
header('Location: /adm.php'); // Пересылка на форму входа 
exit(); 
} 
else 
{  
$hello = "Добро пожаловать, ".$_SESSION['name']."!"; 
$exitlink = "<a class=\"exitlink\" href=\"exitsess.php\">Выход с сайта</a>"; 
} 
//-----------------------------------------------------------   
//                        Переменные 
//-----------------------------------------------------------   
// Массив для хранения списка таблиц 
$list = array(); 
// Объявляем имя основной базы данных 
$dbname = 'book'; 
//-----------------------------------------------------------   
//                        

//-----------------------------------------------------------  
$date = date("d.m.y"); // функция выдачи даты в формате "День, месяц, год"   
$dn = date("l"); // функция выдачи даты в формате дня недели 
// Соединяемся с сервером БД под сохраненными перемеными сессии 
$host = 'localhost';
$username = $_SESSION['idsess']; // Замените на вашего пользователя БД
$password = $_SESSION['hashpasswd']; // Пароль пользователя
$database = 'book'; // Имя базы данных

$link = new mysqli($host, $username, $password, $database);

    // Проверяем соединение
if ($link->connect_error) {
    die('Connection failed: ' . $link->connect_error);
}

$link->set_charset("utf8"); // Устанавливаем кодировку UTF-8

// Функция mysql_list_tables($dbname) возвращает имена таблиц в базе 
$sql = "SHOW TABLES";
$result = $link->query($sql);

if (!$result) {
    die("Ошибка базы данных, не удалось получить список таблиц: " . $link->error);
}

// Сохраняем список таблиц в массив
$list = array();
while ($row = $result->fetch_array(MYSQLI_NUM)) {
    $list[] = $row[0];
}

// Освобождаем память результата
$result->free();

// Закрываем соединение
$link->close();//-----------------------   
//                      Отображение 
//-----------------------------------------------------------   
// Вывод BOM (для браузера) 
echo chr(239).chr(187).chr(191); 
?>   
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"  
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">  
<!--!DOCTYPE необходим, для того, чтобы браузер правильно понимал тип документа 
В данном случае мы используем XHTML 1.0--> 
<html xmlns="http://www.w3.org/1999/xhtml">  
<head>  
<title>База данных "Телефонный справочник"</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="description" content="База данных 'Телефонный справочник'"/>
    <meta name="keywords" content="База данных, телефонный справочник, PHP, MySQL, web-программирование"/>
    <link rel="stylesheet" type="text/css" href="my-style.css"/>
</head>  
<body> 
    <div id="container"> 
    <div id="other"> 
    <div id="daydata"> 
    <?php 
    echo ("Сегодня ".$date." ".$dn."\n"); // вывод даты и дня недели 
    ?> 
    </div> 
    <div id="hello"> 
        <?php 
    // Если вход осуществлен, то появляется приветствие и ссылка для разлогинивания 
        print $hello." -->".$exitlink."<--"; 
        ?> 
    </div> 
    </div> 
    <div id="menu"> 
    <div><a href="index.php">Главная</a></div> 
    <div><a href="adm.php" style="border-bottom: 7px solid 
    #000066">Функции</a></div> 
    </div> 
    <div id="content"> 
    <h1>Таблицы и отчеты</h1> 
    <p class="centr"> 
    Выберите таблицу или отчет: 
    </p> 
    <table class="tbl" align="center"> 
    <?php 
    if ($_SESSION['name'] == "admin") {
        foreach ($list as $col_value) { 
            if ($col_value == "category") {
                $name = "Рубрики";
            }
            if ($col_value == "city") {
                $name = "Города";
            }
            if ($col_value == "country") {
                $name = "Страны";
            }
            if ($col_value == "natural_person") {
                $name = "Физические лица";
            }
            if ($col_value == "number_number_organization") {
                $name = "Телефоны организаций";
            }
            if ($col_value == "number_number_person") {
                $name = "Телефоны физических лиц";
            }
            if ($col_value == "organization") {
                $name = "Организации";
            }
            print "\t\t<tr>\n"; 
            // Формируем ссылку и еѐ название 
            print "\t\t<td><a 
        href=\"".$col_value.".php\">".$name."</a></td>\n"; 
            print "\t\t</tr>\n"; 
        } 
    } else {
        print "\t\t<td><a 
        href=\"req1.php\">Показать физические лица</a></td>\n"; 
        print "\t\t</tr>\n"; 
        print "\t\t<td><a 
        href=\"req2.php\">Показать организации</a></td>\n"; 
        print "\t\t</tr>\n"; 
        print "\t\t<td><a 
        href=\"req3.php\">Список телефонных кодов городов</a></td>\n"; 
        print "\t\t</tr>\n"; 
        print "\t\t<td><a 
        href=\"req4.php\">Список телефонов всех организаций</a></td>\n"; 
        print "\t\t</tr>\n"; 
        print "\t\t<td><a 
        href=\"req5.php\">Список телефонов физических лиц города</a></td>\n"; 
        print "\t\t</tr>\n"; 
        print "\t\t<td><a 
        href=\"req6.php\">Список телефонов организации по рубрике и городу</a></td>\n"; 
        print "\t\t</tr>\n"; 
        
    }
    ?> 
    </table> 
        <br/>  
    </div> 
    </div> 
</body>  
</html> 
