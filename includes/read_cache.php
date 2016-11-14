<?php

$cache_url = str_replace("/", "_", $url);
$cache_time = 300; // Время жизни кэша в секундах
$cache_file = CACHE . SITE ."_". $cache_url; // Файл будет находиться, например, в /cache/a.php.html
if (file_exists($cache_file)) {
	// Если файл с кэшем существует
	if ((time() - $cache_time) < filemtime($cache_file)) {
		// Если его время жизни ещё не прошло
		echo file_get_contents($cache_file); // Выводим содержимое файла
		// exit; // Завершаем скрипт, чтобы сэкономить время на дальнейшей обработке
	}
} else {
	ob_start(); // Открываем буфер для вывода, если кэша нет, или он устарел
