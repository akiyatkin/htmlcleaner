<html>
<body>

<?php
require(dirname(__FILE__).'/HtmlCleaner.php');

$allowed_tags = 'p[class],h1,h2,h3,a[href],table,tr,td,i';
$allow_href_js = false;

$cleaner = new HtmlCleaner($allowed_tags, $allow_href_js);

$html =  <<<EOD
<h1>Заголовок</h1>
<p><span>Какой-то span</span> внутри абзаца.</p>
<p class="red" onclick="alert('ok');" style="align:right;">Абзац с атрибутами</p>
<p><a onclick="alert('fail');return false;" href="http://example.com">Ссылка</a>  <strong>жирняшка</strong>.<br />
Новая строка</p>
<h6>Левый заголовок</h6>
<p>Еще одна <a style="position:absolute;" href="javascript:alert('href_js');">ссылка</a> </p>
EOD;

$cleaned_html = $cleaner->clean($html);
echo $cleaned_html;
?>

</body>
</html>
