<?php
// Устанавливаем уровень оповещения ошибок
error_reporting(E_ALL); // Показывать все ошибки и предупреждения
session_start(); // Начинаем новую или открываем существующую сессию

// Проверяем существование параметров сессии - логина и шифрованного пароля
// Если параметров не существует, значит пересылаем на страницу входа adm.php
if (!((isset($_SESSION['idsess'])) && (isset($_SESSION['hashpasswd'])))) {
    header('Location: /adm.php'); // Пересылка на форму входа
    exit();
} else {
    $hello = "Добро пожаловать, ".$_SESSION['name']."!";
    $exitlink = "<a class=\"exitlink\" href=\"exitsess.php\">Выход с сайта</a>";
}

// Переменные
$dbname = 'book'; // Имя базы данных
$tblname = 'organization'; // Имя таблицы
$info = array(); // Массив для вывода информации
$servername = "localhost";
$username = $_SESSION['idsess'];
$password = $_SESSION['hashpasswd'];
if ($username == 'trueuser') {
    header('Location: /base.php'); // Пересылка на форму входа
    exit();
}
// Скрипт
$date = date("d.m.y"); // Текущая дата
$dn = date("l"); // Текущий день недели

// Соединяемся с сервером БД под сохраненными переменными сессии
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка на ошибки соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Устанавливаем кодировку
$conn->set_charset("utf8");

// Получаем список заголовков столбцов таблицы
$result = $conn->query("DESCRIBE $tblname");
if (!$result) {
    die("Ошибка запроса: " . $conn->error);
}
$fields = $result->fetch_all(MYSQLI_ASSOC);

// Получаем количество столбцов
$columns = count($fields);

// Обработка таблицы

// Добавление записи в таблицу
if (isset($_POST['name_organization_add'], $_POST['street_add'], $_POST['house_add'], $_POST['fid_city_add'], $_POST['fid_category_add'])) {
    $name_organization_add = htmlspecialchars(stripslashes($_POST['name_organization_add']));
    $street_add = htmlspecialchars(stripslashes($_POST['street_add']));
    $house_add = htmlspecialchars(stripslashes($_POST['house_add']));
    $fid_city_add = intval($_POST['fid_city_add']); // ID выбранной страны
    $fid_category_add = intval($_POST['fid_category_add']);

    if (!empty($name_organization_add) && !empty($street_add) && !empty($house_add) && !empty($fid_city_add) && !empty($fid_category_add)) {
            $query_any_repeat = "SELECT * FROM $tblname WHERE name_organization = ? AND street = ? AND house = ? AND fid_city = ? AND fid_category = ?";
            $stmt = $conn->prepare($query_any_repeat);
            $stmt->bind_param("sssii", $name_organization_add, $street_add, $house_add, $fid_city_add, $fid_category_add);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows != 0) {
                $info[] = 'Такая запись уже существует!';
            } else {
            $query_add = "INSERT INTO $tblname (name_organization, street, house, fid_city, fid_category) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query_add);
            $stmt->bind_param("sssii", $name_organization_add, $street_add, $house_add, $fid_city_add, $fid_category_add);
            $stmt->execute();
            }
    } else {
        $info[] = 'Не заполнены обязательные поля!';
    }   
}
// Изменение записи
if (isset($_POST['name_organization_upd'], $_POST['street_upd'], $_POST['house_upd'], $_POST['fid_city_upd'], $_POST['fid_category_upd'])) {
    $name_organization_upd = htmlspecialchars(stripslashes($_POST['name_organization_upd']));
    $street_upd = htmlspecialchars(stripslashes($_POST['street_upd']));
    $house_upd = htmlspecialchars(stripslashes($_POST['house_upd']));
    $fid_city_upd = intval($_POST['fid_city_upd']); // ID выбранной страны
    $fid_category_upd = intval($_POST['fid_category_upd']);

    if (!empty($name_organization_upd) && !empty($street_upd) && !empty($house_upd) && !empty($fid_city_upd) && !empty($fid_category_upd)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE name_organization = ? AND street = ? AND house = ? AND fid_city = ? AND fid_category = ? AND id_organization != ?";
        $stmt = $conn->prepare($query_any_repeat);
        $stmt->bind_param("sssiii", $name_organization_upd, $street_upd, $house_upd, $fid_city_upd, $fid_category_upd, $_SESSION['updateRow']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows != 0) {
            $info[] = 'Такая запись уже существует!';
        } else {
            $query_upd = "UPDATE $tblname SET name_organization = ?, street = ?, house = ?, fid_city = ?,  fid_category= ? WHERE id_organization = ?";
            $stmt = $conn->prepare($query_upd);
            $stmt->bind_param("sssiii", $name_organization_upd, $street_upd, $house_upd, $fid_city_upd, $fid_category_upd, $_SESSION['updateRow']);
            $stmt->execute();
        }
    } else {
        $info[] = 'Не заполнены обязательные поля!';
    }
}

// Отображение
echo chr(239).chr(187).chr(191);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
        <div><a href="adm.php" style="border-bottom: 7px solid #000066">Функции</a></div>
    </div>
    <div id="content">
        <h1>Таблица "Организации"</h1>
        <?php
        // Получаем все страны для выпадающего списка
        // Вывод форм для редактирования
        $query_city = "SELECT c.id_city, CONCAT('Страна: ', cn.name_country, ' Город: ', c.name_city) AS FullCity
            FROM city c
            JOIN country cn ON c.fid_country = cn.id_country 
            ORDER BY name_country";
            $result_city = $conn->query($query_city);
        $query_cat = "SELECT id_category, name_category
            FROM category
            ORDER BY name_category";
            $result_cat = $conn->query($query_cat);
        if (isset($_POST['input']) && $_POST['input'] == 'add') { // Добавление
            echo "<form method=\"post\" action=\"\">";
            echo "<table align=\"center\">";
            echo "<tr><td>Выбор города:</td><td><select name=\"fid_city_add\">";
            while ($row_city = $result_city->fetch_assoc()) {
                echo "<option value=\"".$row_city['id_city'] . "\">" 
                . $row_city['FullCity'] . "</option>";
            }
            echo "</select></td></tr>";
            echo "<tr><td>Выбор рубрики:</td><td><select name=\"fid_category_add\">";
            while ($row_cat = $result_cat->fetch_assoc()) {
                echo "<option value=\"".$row_cat['id_category'] . "\">" 
                . $row_cat['name_category'] . "</option>";
            }
            echo "<tr><td>Введи название организации: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"name_organization_add\"/></td></tr>";
            echo "<tr><td>Введи название улицы: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"street_add\"/></td></tr>";
            echo "<tr><td>Введи название дома: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" name=\"house_add\"/></td></tr>";
            echo "</select></td></tr>";
            echo "</table>";
            echo "<p class=\"centr\">";
            echo "<br/>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Отправить\"/>";
            echo "<input type=\"reset\" name=\"reset\" value=\"Очистить\"/>";
            echo "</p>";
            echo "</form><br/>";
        } if (isset($_POST['input']) && $_POST['input'] == 'edit') { // Изменение
            if (!isset($_POST['chooseRow'])) {
                $info[] = 'Укажите запись!';
            } else {
                $_SESSION['updateRow'] = $_POST['chooseRow']; // Запоминаем ряд
                $query_form_upd = "SELECT * FROM $tblname WHERE id_organization = ?";
                $stmt = $conn->prepare($query_form_upd);
                $stmt->bind_param("i", $_POST['chooseRow']);
                $stmt->execute();
                $result_form_upd = $stmt->get_result();
                
                echo "<form method=\"post\" action=\"\">";
                echo "<table align=\"center\">";
                $line_content = $result_form_upd->fetch_array(MYSQLI_NUM);
                echo "<tr><td>Выбор города:</td><td><select name=\"fid_city_upd\">";
                while ($row_city = $result_city->fetch_assoc()) {
                    $selected = ($row_city['id_city'] == $line_content[1]) ? 'selected' : '';
                    echo "<option value=\"".$row_city['id_city'] . "\" $selected>" 
                    . $row_city['FullCity'] . "</option>";
                }
                echo "</select></td></tr>";
                echo "<tr><td>Выбор рубрики:</td><td><select name=\"fid_category_upd\">";
                while ($row_cat = $result_cat->fetch_assoc()) {
                    $selected = ($row_cat['id_category'] == $line_content[2]) ? 'selected' : '';
                    echo "<option value=\"".$row_cat['id_category'] . "\" $selected>" 
                    . $row_cat['name_category'] . "</option>";
                }
                echo "<tr><td>Введи название организации: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"name_organization_upd\" value=\"" . $line_content[3] . "\"/></td></tr>";
                echo "<tr><td>Введи название улицы: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"street_upd\" value=\"" . $line_content[4] . "\"/></td></tr>";
                echo "<tr><td>Введи название дома: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" name=\"house_upd\" value=\"" . $line_content[5] . "\"/></td></tr>";
                echo "</table>";
                echo "<p class=\"centr\">";
                echo "<br/>";
                echo "<input type=\"submit\" name=\"submit\" value=\"Отправить\"/>";
                echo "<input type=\"reset\" name=\"reset\" value=\"Очистить\"/>";
                echo "</p>";
                echo "</form><br/>";
                $result_form_upd->free();
            }
        } if (isset($_POST['input']) && $_POST['input'] == 'del') {
            if (!isset($_POST['chooseRow'])) {
                $info[] = 'Укажите запись!';
            } else {
                $organizationID = $_POST['chooseRow'];
    
                // Проверка наличия городов, связанных с этой страной
                $query_check_org = "SELECT COUNT(*) FROM number_number_organization WHERE fid_organization = ?";
                $stmt_check = $conn->prepare($query_check_org);
                $stmt_check->bind_param("i", $organizationID);
                $stmt_check->execute();
                $stmt_check->bind_result($number_org);
                $stmt_check->fetch();
                $stmt_check->close();

                
                
                if (($number_org > 0)) {
                    $info[] = 'Невозможно удалить запись!';
                } else {
                    // Удаляем страну, если нет связанных городов
                    $query_del = "DELETE FROM $tblname WHERE id_organization = ?";
                    $stmt = $conn->prepare($query_del);
                    $stmt->bind_param("i", $organizationID);
                    $stmt->execute();
                    $info[] = 'Запись успешно удалена!';
                }
            }
        }
        
        ?>
        <!-- Панель для выбора действия над таблицей -->
        <form method="post" action="">
            <table border="0" align="center">
                <tr>
                    <td><input type="submit" name="input" value="add" /></td>
                    <td><input type="submit" name="input" value="edit" /></td>
                    <td><input type="submit" name="input" value="del" /></td>
                </tr>
            </table>
            <br/>
            <!-- Отрисовка таблицы -->
            <?php
            // Выполняем SQL-запросы для отображения содержимого таблицы
            $query_content = "
            SELECT o.id_organization, o.name_organization, o.street, o.house, o.fid_city, c.name_city, o.fid_category, cat.name_category
            FROM $tblname o
            JOIN city c ON o.fid_city = c.id_city 
            JOIN category cat ON o.fid_category = cat.id_category
            ORDER BY o.fid_city, o.fid_category";
            $result_content = $conn->query($query_content);

            echo "<table class=\"tbl\" align=\"center\">";
            echo "<tr class=\"header\">";
            echo "<th>&nbsp;</th>";
            echo "<th>ID_организации</th>";
            echo "<th>Наименование организации</th>";
            echo "<th>Улица</th>";
            echo "<th>Дом</th>";
            echo "<th>FID Города</th>";
            echo "<th>Наименование города</th>";
            echo "<th>FID рубрики</th>";
            echo "<th>Наименование рубрики</th>";
            echo "</tr>";

            while ($line_content = $result_content->fetch_array(MYSQLI_NUM)) {
            echo "<tr>";
            echo "<td><input type=\"radio\" name=\"chooseRow\" value=\"".$line_content[0]."\"/></td>";
            foreach ($line_content as $col_value) {
                echo "<td>".$col_value."</td>";
            }
            echo "</tr>";
            }
            echo "</table>";
            $result_content->free();
            ?>
        </form>
        <br/>
        <p class="error">
            <!-- Вывод информации об ошибках -->
            <?php
            echo implode('<br/>', $info)."\n";
            ?>
        </p>
        <p class="centr">
            <a href="/base.php">Вернуться к таблицам...</a>
        </p>
        <br/>
    </div>
</div>
</body>
</html>
<?php
// Закрываем соединение
$conn->close();
?>
