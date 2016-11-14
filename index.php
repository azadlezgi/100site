<?php

// header("HTTP/1.0 404 Not Found");
require_once "includes/maincore.php";

$component_url = "";
if (isset($_GET['row_url'])) { $fusion_uri = "/". stripinput( $_GET['row_url'] ); }
else { $fusion_uri = FUSION_URI; }
$fusion_uri = explode("?", $fusion_uri);
$fusion_uri = $fusion_uri[0];


if ($fusion_uri=="/". $settings['opening_page']) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /");
    exit();
}


if ($fusion_uri=="/") {
    $url = $settings['opening_page'];
    $url = explode("?", $url);
    $url = $url[0];
} else {
    $url = $fusion_uri;
    $url = substr($url, 1);
    $url = explode("?", $url);
    $url = $url[0];
}

if (file_exists(COMPONENTS . $url)) {
    $component_url = COMPONENTS . $url;
} else {

    $viewseourl = viewseourl($url, "url");
    $component_id = (isset($viewseourl['seourl_component']) ? $viewseourl['seourl_component'] : 0);
    $filedid = (isset($viewseourl['seourl_filedid']) ? $viewseourl['seourl_filedid'] : 0);
    $viewcompanent = viewcompanent($component_id, "id");
    $component = (isset($viewcompanent['components_name']) ? $viewcompanent['components_name'] : "");

    if ($component) {
        $component_url = COMPONENTS . $component .".php";
    }


    $page_404_url = "";
    if ((!$component_id) && (!$filedid)) {
        $component_url = COMPONENTS . "404.php";
        $page_404_url = "page_404";
    }

} // Yesli URL Companent



if (FUSION_URI=="/") {
//    echo FUSION_URI;
    $cache_time = 86400; // Время жизни кэша в секундах
} else {
    $cache_time = 0; // Время жизни кэша в секундах
}
$cache_url = FUSION_URI;
$cache_url = substr($cache_url, 1);
if ( ($cache_url=="robots.txt") || ($cache_url=="sitemap.xml") || ($cache_url=="yandex_wdgt.xhtml") ) {
    $cache_url = $cache_url;
    $cache_file = CACHE ."/". $cache_url; // Файл будет находиться, например, в /cache/a.php.html
} else {
    $cache_url = str_replace("/", "_", $cache_url);
    $cache_url = str_replace(".", "_", $cache_url);
    $cache_url = autocrateseourls( (FUSION_URI=="/" ? "index" : (!empty($page_404_url) ? 'page_404' : $cache_url)) );
    $cache_file = CACHE . LOCALESHORT ."_". $cache_url; // Файл будет находиться, например, в /cache/a.php.html
}
if ( (file_exists($cache_file)) && ((time() - $cache_time) < filemtime($cache_file)) && (!iADMIN) && (!isset($_POST)) ) {

    if ($cache_url=="sitemap.xml") { header("Content-type: text/xml"); }
    else if ($cache_url=="yandex_wdgt.xhtml") { header("Content-type: text/xhtml"); }
    else if ($cache_url=="robots.txt") { header("Content-type: text/plain"); }

    echo file_get_contents($cache_file); // Выводим содержимое файла
} else {
    ob_start(); // Открываем буфер для вывода, если кэша нет, или он устарел


    if ( ($url!="robots.txt") && ($url!="sitemap.xml") && ($url!="yandex_wdgt.xhtml") && (!isset($_POST['json'])) ) { require_once THEMES ."templates/header.php"; }
    require_once $component_url;
    if ( ($url!="robots.txt") && ($url!="sitemap.xml") && ($url!="yandex_wdgt.xhtml") && (!isset($_POST['json'])) ) { require_once THEMES ."templates/footer.php"; }


    if (!iADMIN && $cache_time>0) {
        /*write_cache*/
        $handle = fopen($cache_file, 'w'); // Открываем файл для записи и стираем его содержимое
        fwrite($handle, ob_get_contents()); // Сохраняем всё содержимое буфера в файл
        fclose($handle); // Закрываем файл
        /*//write_cache*/
    }

    ob_end_flush(); // Выводим страницу в браузере

} // Yesli Yest cache_file

?>