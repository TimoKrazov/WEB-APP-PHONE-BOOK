<?php 

// Устанавливаем уровень оповещения ошибок    
error_reporting(E_ALL); // Показывать все ошибки и предупреждения 
session_start(); // Начинаем новую или открываем существующую сессию 
/* Проверяем существование параметров сессии - логина и шифрованного пароля 
Если вход уже был осуществлен, то пересылаем сразу на страницу редактирования */ 
if ((isset($_SESSION['idsess'])) && (isset($_SESSION['hashpasswd'])))  
{ 
    header('Location: /base.php'); // Пересылка на редактирование базы 
    exit(); 
} 
//-----------------------------------------------------------   
//                        Переменные 
//-----------------------------------------------------------   
// Проверяем, заполнены ли были поля Логин и Пароль 
$login = !empty($_POST['login']) ? $_POST['login'] : null;    
$passwd = !empty($_POST['passwd']) ? $_POST['passwd'] : null;   
// Объявляем массив info для сборки информации и дальнейшего вывода пользователю 
$info = array(); 
//-----------------------------------------------------------   
//                        Скрипт 
//-----------------------------------------------------------  
$date = date("d.m.y"); // функция выдачи даты в формате "День, месяц, год"   
$dn = date("l"); // функция выдачи даты в формате дня недели 
if (!empty($_POST['ok'])) // Если кнопка Отправить была нажата 
{ 
if(!$login)    
$info[] = 'Нет имени пользователя.';  
if(!$passwd)    
$info[] = 'Не введен пароль.'; 
if (count($info) == 0) // Если замечаний нет и все поля заполнены 
{ 
/* Осуществляем удаление HTML-тегов и обратных слешей, если они есть. 
Это необходимо для защиты от SQL-инъекций и вредоносного кода. */ 
   $login = substr($login,0,50); 
   $login = htmlspecialchars(stripslashes($login)); 
   $passwd = substr($passwd,0,50); 
   $passwd = htmlspecialchars(stripslashes($passwd)); 
    
  /* Создаем содинение с базой данных MySQL userInfo с таблицей user_autentificate 
 Cделан доступ для любого пользователя (user@%), с ораничением прав (только SELECT) */ 
    $host = 'localhost';
    $username = 'user'; // Замените на вашего пользователя БД
    $password = ''; // Пароль пользователя
    $database = 'userinfo'; // Имя базы данных

    $link = new mysqli($host, $username, $password, $database);

    // Проверяем соединение
    if ($link->connect_error) {
        die('Connection failed: ' . $link->connect_error);
    }

    $link->set_charset("utf8"); // Устанавливаем кодировку UTF-8

    // Шифруем введенный пароль
    $hash_val = md5($passwd);

    // Подготавливаем запрос для проверки логина и пароля
    $stmt = $link->prepare("SELECT u.userid, r.name_role FROM user_autentificate u JOIN roles r ON u.role = r.id_roles WHERE u.username = ? AND u.password = ?");
    $stmt->bind_param("ss", $login, $hash_val);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) { // Если совпадения не найдены
        $info[] = 'Доступ запрещен.';
    } else { // Если пользователь найден
        $row = $result->fetch_assoc();
        $role = $row['name_role'];

        // Заносим логин и пароль пользователя в суперглобальный массив _SESSION
        $_SESSION['idsess'] = $role;  
        $_SESSION['hashpasswd'] = $hash_val; 
        $_SESSION['name'] = $login;
        header('Location: /base.php'); 
        exit();
    }

    // Закрываем соединение
    $stmt->close();
    $link->close();
  } 
 } 
//-----------------------------------------------------------   
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
    </div> 
    <div id="menu"> 
    <div><a href="index.php">Главная</a></div> 
    <div><a href="adm.php" style="border-bottom: 7px solid 
    #000066">Функции</a></div> 
    </div> 
    <div id="content"> 
    <h1>Вход</h1> 
    <p class="centr"> 
    Введите следующие данные (Логин для user: юзер Пароль для user: true): 
    </p> 
    <!-- Начало формы ввода пользовательских данных --> 
    <form method="post" action="">   
    <table border="0" align="center"> 
    <tr> 
        <td align="right">Логин:</td> 
        <!-- Текстовое поле ввода --> 
        <td><input type="text" pattern="^\S.*\S$|^\S$" title="запрещены пробелы в начале и в конце" size="30" maxlength="255" name="login"/></td>  
    </tr> 
    <tr> 
        <td align="right">Пароль:</td> 
        <!-- Поле ввода для паролей --> 
        <td><input type="password" size="30" maxlength="255" name="passwd"/></td> 
    </tr> 
    </table> 
        <br/> 
    <p class="centr"> 
    <!-- Кнопка типа submit, по еѐ нажатию все данные 
    из всех полей, входящие в form, отправляются в 
    указанный в атрибуте action файл. Если атрибут пустой, 
    то данные отправляются в текущий файл--> 
    <input type="submit" value="Войти!" name="ok"/> 
    <!-- Кнопка для очистки всех полей --> 
    <input type="reset" value="Очистить"/> 
    </p> 
    <!-- Конец формы ввода пользовательских данных --> 
    </form> 
    <p class="error"> 
    <!-- Вывод информации об ошибках --> 
    <?php 
    // Функция implode перечисляет элементы массива через любой разделитель 
    echo implode('<br/>', $info)."\n"; 
    ?> 
    </p>  
    <br/> 
    </div> 

    </div> 
</body>  
</html> 