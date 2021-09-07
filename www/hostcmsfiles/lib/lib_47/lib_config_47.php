<?php

$text = strval(Core_Array::getGet('text'));
if ($text)
{
	Core_Page::instance()->title('Поиск: ' . $text);
}