<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 12.08.13
 * Time: 15:19
 * To change this template use File | Settings | File Templates.
 */
include_once('lib/helpers.php');
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="http://yandex.st/highlightjs/7.3/styles/default.min.css">
    <script src="http://yandex.st/highlightjs/7.3/highlight.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script>hljs.initHighlightingOnLoad();
        $(function() {
            $('.select').click(function() {
                $('textarea').select();
            });
        });
    </script>
</head>
<body>
Îòêðîéòå ôàéë <b>.htaccess</b>, ëåæàùèé â êîðíå âàøåãî ñàéòà è âñòàâüòå òóäà ýòîò áëîê:
</br>
<pre>
<code class="apache">#For opt_tng
RewriteCond %{REQUEST_URI} !=/opt_tng/opt_index.php
RewriteCond %{REQUEST_URI} !^(.*)?(js|css|jpg|png|gif|/opt_tng/|swf|bitrix)(.*)?$ [NC]
RewriteCond %{REMOTE_ADDR} !=<?= get_your_ip(); ?>

RewriteRule (.*) /opt_tng/opt_index.php [L,PT]
#/For opt_tng</code></pre>
</br>
ñðàçó ïîñëå ýòîé ñòðî÷êè:
</br>
<pre><code class="apache">RewriteEngine on</code></pre>
</br>
Óäîáíî ñêîïèðîâàòü ýòîò áëîê ìîæíî îòñþäà:
<button class='select'>Âûäåëèòü</button>
</br>
<textarea readonly="readonly" cols="100" rows="7">#For opt_tng
RewriteCond %{REQUEST_URI} !=/opt_tng/opt_index.php
RewriteCond %{REQUEST_URI} !^(.*)?(js|css|jpg|png|gif|/opt_tng/|swf|bitrix)(.*)?$ [NC]
RewriteCond %{REMOTE_ADDR} !=<?= get_your_ip(); ?>

RewriteRule (.*) /opt_tng/opt_index.php [L,PT]
#/For opt_tng</textarea>
</br>
<button class='select'>Âûäåëèòü</button>
</code>
</body>
</html>