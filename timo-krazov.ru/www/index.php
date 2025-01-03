<?php 
  
      
// Устанавливаем уровень оповещения ошибок    
error_reporting(E_ALL); // Показывать все ошибки и предупреждения 
  
session_start(); // Начинаем новую или открываем существующую сессию 
  
/* Проверяем существование параметров сессии - логина и шифрованного пароля, 
которые мы получим из другого скрипта – adm.php*/ 
if ((isset($_SESSION['idsess'])) && (isset($_SESSION['hashpasswd']))) 
{ 
$succsess = true; 
$hello = "Добро пожаловать, ".$_SESSION['name']."!"; 
// Это ссылка на скрипт, который удалит параметры сессии и еѐ идентификатор 
$exitlink = "<a class=\"exitlink\" href=\"exitsess.php\">Выход с сайта</a>"; 
} 
else 
 $succsess = false; 
  
//-----------------------------------------------------------   
//                        Скрипт 
//-----------------------------------------------------------  
      
$date = date("d.m.y"); // функция выдачи даты в формате "День, месяц, год"   
$dn = date("l");   // функция выдачи даты в формате дня недели 
           
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
    <div id="hello"> 
        <?php 
        if ($succsess == true) 
        { 
        /* Если вход осуществлен, то появляется приветствие 
    и ссылка для разлогинивания*/ 
        print $hello." -->".$exitlink."<--"; 
        } 
        ?> 
    </div> 
    </div> 
    <div id="menu"> 
    <div><a href="index.php">Главная</a></div> 
    <div><a href="adm.php" style="border-bottom: 7px solid 
    #000066">Функции</a></div> 
    </div> 
    <div id="content"> 
    <h1>Задание</h1> 
    <p> 
    В БД следует хранить информацию о номерах телефонов физических лиц (ФИО, адрес, номер 
телефона), а также адреса различных организаций городов. Город имеет телефонный код, название и 
находится на территории определенной страны. Организация характеризуется названием, адресом и 
телефоном. Каждый телефон принадлежит определенной рубрике: срочные услуги, 
общеобразовательные учреждения, ВУЗы, авиакомпании, аптеки, магазины, больницы, поликлиники и 
т.д. Организации могут иметь несколько телефонов. 
Необходимо предоставить возможность поиска информации в базе: для физических лиц – по 
номеру телефона, фамилии или адресу; для организаций – по названию или телефону. 
Необходимо также выдавать следующие выходные документы:  
    </p> 
    <ol> 
    <li>Телефонные коды городов, отсортированные по алфавиту. </li> 
    <li>Телефоны физических лиц города, отсортированные по фамилии. </li> 
    <li>Телефоны всех организаций городов, сгруппированные по городам и рубрикам.</li> 
    <li>Телефоны организаций города определенной рубрики. </li>
    </ol> 
    <br/> 
    </div> 
    </div> 
</body>  
</html> 