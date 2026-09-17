<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рапорт трубы</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-family: Arial, sans-serif;
        }

        th, td {
            border: 3px solid #ddd;
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #ddd;
        }

        h1 {
            font-family: Arial, sans-serif;
            font-size: 35px;
        }
		
		h2 {
			text-align: left;
			margin: 10px 10px 20px 10px;
		}
				
		label {
			font-size: 20px;
			font-family: Arial, sans-serif;
		}
		
		input, button {
			background-color: #fff;
			border: 2px solid #999797fc;
			font-size: 15px;
			font-family: Arial, sans-serif;
		}
		
		#back {	
			display: inline-block;
			color: white;
			text-decoration: none;
			padding: .2em 2em;
			outline: none;
			border-width: 2px 0;
			border-style: solid none;
			border-color: #FDBE33 #000 #D77206;
			border-radius: 6px;
			background: linear-gradient(#F3AE0F, #E38916) #E38916;
			width: 170px;
			height: 28px;
		
		}
		
    </style>
</head>
<body>
<button id="back" style="width: 150px; height: 30px;">Обратно</button>
<h1 align="center">Рапорт замеров трубы</h1>
<h2>
    <button id="graf" type="submit" >Открыть график</button>
</h2>
<form id="dateForm">
    <label for="datePicker">Выберите дату:</label>
    <input type="date" id="datePicker" name="datePicker" max="<?php echo date('Y-m-d'); ?>">
    <button type="submit">Показать рапорт
	</button>
</form>

<table id="dataTable">
    <thead>
        <tr>
            <th>Диаметр по X</th>
            <th>Диаметр по Y</th>
            <th>Диаметр по Z</th>
            <th>Диаметр Общий</th>
            <th>Длина трубы (м)</th>
            <th>Толщина (мм)</th>
            <th>Скорость (м/мин)</th>
            <th>Дата</th>
        </tr>
    </thead>
    <tbody>
	
        <?php
		$data_t=date("Y-m-d");
        // Параметры подключения к базе данных
        $servername = "10.10.3.41"; // Адрес сервера базы данных
        $username = "vadim"; // Имя пользователя базы данных
        $password = "1q2w3e4r5t6y"; // Пароль пользователя базы данных
        $dbname = "TUBOG"; // Имя базы данных

        try {
            // Создаем подключение
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Проверяем подключение
            if ($conn->connect_error) {
                throw new Exception("Ошибка подключения: " . $conn->connect_error);
            }

            // Проверяем, есть ли параметр даты в GET-запросе
            if (isset($_GET['date'])) {
                $date = $_GET['date'];

                // SQL-запрос для выборки средних значений за выбранную дату, раз в час
                $sql = "SELECT 
                            AVG(X_diameter) AS avg_X_diameter, 
                            AVG(Y_diameter) AS avg_Y_diameter, 
                            AVG(Z_diameter) AS avg_Z_diameter, 
                            AVG(Diameter) AS avg_Diameter, 
                            AVG(Dlina) AS avg_Dlina, 
                            AVG(Speed) AS avg_Speed, 
                            AVG(Tolshina) AS avg_Tolshina, 
                            DATE_FORMAT(Data, '%Y-%m-%d %H:00:00') AS hour 
                        FROM Zameri_Trubi 
                        WHERE DATE(Data) = ? 
                        GROUP BY DATE_FORMAT(Data, '%Y-%m-%d %H:00:00') 
                        ORDER BY hour";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $date);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Выводим данные в таблицу
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . round($row["avg_X_diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Y_diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Z_diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Dlina"], 2) . "</td>";
                        echo "<td>" . formatTolshina($row["avg_Tolshina"]) . "</td>";
                        echo "<td>" . round($row["avg_Speed"], 0) . "</td>";
                        echo "<td>" . $row["hour"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>За ${date} данные отсутствуют</td></tr>";
                }

                // Закрываем подключение
                $stmt->close();
            } else {
                // SQL-запрос для выборки средних значений за текущую дату, один раз за час
                $sql = "SELECT 
                            AVG(X_diameter) AS avg_X_diameter, 
                            AVG(Y_diameter) AS avg_Y_diameter, 
                            AVG(Z_diameter) AS avg_Z_diameter, 
                            AVG(Diameter) AS avg_Diameter, 
                            AVG(Dlina) AS avg_Dlina, 
                            AVG(Speed) AS avg_Speed, 
                            AVG(Tolshina) AS avg_Tolshina,
                            DATE_FORMAT(Data, '%Y-%m-%d %H:00:00') AS hour 
                        FROM Zameri_Trubi 
                        WHERE DATE(Data) = CURDATE() 
                        GROUP BY DATE_FORMAT(Data, '%Y-%m-%d %H:00:00') 
                        ORDER BY hour";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    // Выводим данные в таблицу
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . round($row["avg_X_diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Y_diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Z_diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Diameter"], 2) . "</td>";
                        echo "<td>" . round($row["avg_Dlina"], 2) . "</td>";
                        echo "<td>" . formatTolshina($row["avg_Tolshina"]) . "</td>";
                        echo "<td>" . round($row["avg_Speed"], 0) . "</td>";
                        echo "<td>" . $row["hour"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>Данные на текущий день отсутствуют</td></tr>";
                }
            }

            $conn->close();
        } catch (Exception $e) {
            echo "<tr><td colspan='10'>Ошибка: " . $e->getMessage() . "</td></tr>";  // Выводим сообщение об ошибке 
        }

        function formatTolshina($value) {
            // Умножаем значение на 1000, чтобы отображать в миллиметрах
            $valueInMillimeters = $value * 1000;
            // Округляем до двух знаков после запятой
            return round($valueInMillimeters, 2);
        }
        ?>
    </tbody>
</table>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedDate = urlParams.get('date');

        if (selectedDate) {
            document.getElementById('datePicker').value = selectedDate;
        }

        document.getElementById('dateForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const selectedDate = document.getElementById('datePicker').value;
            fetchData(selectedDate);
        });

        function fetchData(date) {
            const url = window.location.href.split('?')[0] + '?date=' + encodeURIComponent(date);
            window.location.href = url;
        }
		
		// Выполняем переход на страницу Zamer_trubi_temp по нажатию кнопки "Открыть график"
		document.getElementById('graf').addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = 'http://10.10.3.41/rifar/TUBOG/Zamer_trubi_temp.php';
        });
		
		// Выполняем переход на страницу Remont по нажатию кнопки "Рапорт ремонта"
		document.getElementById('back').addEventListener('click', function(event) {
        event.preventDefault();
        window.location.href = 'http://10.10.3.41/rifar/TUBOG/Uchet/Raport/index.php';
    });
    });
</script>
 
 
</body>
</html>