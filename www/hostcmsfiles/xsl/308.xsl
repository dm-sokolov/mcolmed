<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокКартинокNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	<xsl:variable name="current_group" select="/informationsystem//informationsystem_group[@id=$group]"/>
	<xsl:variable name="city">
		<xsl:if test="/informationsystem/site_id = 1"> в Екатеринбурге</xsl:if>
		<xsl:if test="/informationsystem/site_id = 8"> в Нижней Туре</xsl:if>
		<xsl:if test="/informationsystem/site_id = 7"> в Североуральске</xsl:if>
		<xsl:if test="/informationsystem/site_id = 6"> в Краснотурьинске</xsl:if>
		<xsl:if test="/informationsystem/site_id = 5"> в Серове</xsl:if>
		<xsl:if test="/informationsystem/site_id = 4"> в Нижнем Тагиле</xsl:if>
		<xsl:if test="/informationsystem/site_id = 13"> в Асбесте</xsl:if>
	</xsl:variable>
	
	<xsl:template match="/">
		<link rel="stylesheet" type="text/css" href="/css/slick.css"/>
		<link rel="stylesheet" type="text/css" href="/css/slick-theme.css"/>
		<script type="text/javascript" src="/js/slick.min.js"></script>
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		<script type="text/javascript">
			$(function() {
			$('.slider-for').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			fade: true,
			asNavFor: '.slider-nav'
			});
			$('.slider-nav').slick({
			slidesToShow: 4,
			slidesToScroll: 1,
			asNavFor: '.slider-for',
			dots: true,
			centerMode: true,
			focusOnSelect: true
			});
			});
		</script>
		
		<!-- Если в находимся корне - выводим название информационной системы -->
		<xsl:if test="$group = 0">
			<h1><xsl:value-of select="name"/><xsl:value-of select="$city" /></h1>
			
			<!-- Описание выводится при отсутствии фильтрации по тэгам -->
			<xsl:if test="count(tag) = 0">
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</xsl:if>
		</xsl:if>
		
		<!-- Если в находимся в группе - выводим название группы -->
		<xsl:if test="$group != 0">
			<h1>
				<xsl:value-of select="$current_group/name"/><xsl:value-of select="$city" />
			</h1>
			
			<!-- Описание выводим только на первой странице -->
			<xsl:if test="page = 0">
				<xsl:value-of disable-output-escaping="yes" select="$current_group/description"/>
			</xsl:if>
		</xsl:if>
		
		<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<div class="row">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" />
			</div>
		</xsl:if>
		
		<xsl:if test="count(informationsystem_item) > 0">
			<div class="slider-for mb-4">
				<xsl:apply-templates select="informationsystem_item" mode="for" />
			</div>
			<div class="slider-nav">
				<xsl:apply-templates select="informationsystem_item" mode="nav" />
			</div>
		</xsl:if>
		
	</xsl:template>
	
	<xsl:template match="informationsystem_group">
		<div class="col-sm-4 mb-4">
			<xsl:choose>
				<xsl:when test="image_small != ''">
					<div class="text-center mb-2">
						<a href="{url}" title="{name}"><img class="img-thumbnail img-fluid" src="{dir}{image_small}" alt="{name}" /></a>
					</div>
					<div class="text-center">
						<a href="{url}" title="{name}"><xsl:value-of select="name"/></a>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<div class="text-center mb-2">
						<a href="{url}" title="{name}"><img src="/images/no-image.png" /></a>
					</div>
					<div class="text-center">
						<a href="{url}" title="{name}"><xsl:value-of select="name"/></a>
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>
	
	<xsl:template match="informationsystem_item" mode="for">
		<div class="text-center">
			<img data-lazy="{dir}{image_large}" alt="{name}" class="img-thumbnail img-fluid d-inline-block" />
		</div>
	</xsl:template>
	
	<xsl:template match="informationsystem_item" mode="nav">
		<div class="p-2 text-center">
			<img data-lazy="{dir}{image_small}" alt="{name}" class="img-fluid d-inline-block" />
		</div>
	</xsl:template>
	
	<!-- /// Метки для информационного элемента /// -->
	<xsl:template match="tag">
		<a href="{/informationsystem/url}tag/{urlencode}/" class="tag">
			<xsl:value-of select="name"/>
		</a>
<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if></xsl:template>
	
	<!-- Цикл для вывода строк ссылок -->
	<xsl:template name="for">
		
		<xsl:param name="limit"/>
		<xsl:param name="page"/>
		<xsl:param name="pre_count_page"/>
		<xsl:param name="post_count_page"/>
		<xsl:param name="i" select="0"/>
		<xsl:param name="items_count"/>
		<xsl:param name="visible_pages"/>
		
		<xsl:variable name="n" select="ceiling($items_count div $limit)"/>
		
		<xsl:variable name="start_page"><xsl:choose>
				<xsl:when test="$page + 1 = $n"><xsl:value-of select="$page - $visible_pages + 1"/></xsl:when>
				<xsl:when test="$page - $pre_count_page &gt; 0"><xsl:value-of select="$page - $pre_count_page"/></xsl:when>
				<xsl:otherwise>0</xsl:otherwise>
		</xsl:choose></xsl:variable>
		
		<xsl:if test="$i = $start_page and $page != 0">
			<span class="ctrl">
				← Ctrl
			</span>
		</xsl:if>
		
		<xsl:if test="$i = ($page + $post_count_page + 1) and $n != ($page+1)">
			<span class="ctrl">
				Ctrl →
			</span>
		</xsl:if>
		
		<xsl:if test="$items_count &gt; $limit and ($page + $post_count_page + 1) &gt; $i">
			<!-- Заносим в переменную $group идентификатор текущей группы -->
			<xsl:variable name="group" select="/informationsystem/group"/>
			
			<!-- Путь для тэга -->
			<xsl:variable name="tag_path">
				<xsl:choose>
					<!-- Если не нулевой уровень -->
					<xsl:when test="count(/informationsystem/tag) != 0">tag/<xsl:value-of select="/informationsystem/tag/urlencode"/>/</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Определяем группу для формирования адреса ссылки -->
			<xsl:variable name="group_link">
				<xsl:choose>
					<!-- Если группа не корневая (!=0) -->
					<xsl:when test="$group != 0">
						<xsl:value-of select="/informationsystem//informationsystem_group[@id=$group]/url"/>
					</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					<xsl:otherwise><xsl:value-of select="/informationsystem/url"/></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Определяем адрес ссылки -->
			<xsl:variable name="number_link">
				<xsl:choose>
					<!-- Если не нулевой уровень -->
					<xsl:when test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:when>
					<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Выводим ссылку на первую страницу -->
			<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
				<a href="{$group_link}{$tag_path}" class="page_link" style="text-decoration: none;">←</a>
			</xsl:if>
			
			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<!-- Выводим ссылки на видимые страницы -->
					<a href="{$group_link}{$number_link}{$tag_path}" class="page_link">
						<xsl:value-of select="$i + 1"/>
					</a>
				</xsl:if>
				
				<!-- Выводим ссылку на последнюю страницу -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<!-- Выводим ссылку на последнюю страницу -->
					<a href="{$group_link}page-{$n}/{$tag_path}" class="page_link" style="text-decoration: none;">→</a>
				</xsl:if>
			</xsl:if>
			
			<!-- Ссылка на предыдущую страницу для Ctrl + влево -->
			<xsl:if test="$page != 0 and $i = $page">
				<xsl:variable name="prev_number_link">
					<xsl:choose>
						<!-- Если не нулевой уровень -->
						<xsl:when test="($page) != 0">page-<xsl:value-of select="$i"/>/</xsl:when>
						<!-- Иначе если нулевой уровень - просто ссылка на страницу со списком элементов -->
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<a href="{$group_link}{$prev_number_link}{$tag_path}" id="id_prev"></a>
			</xsl:if>
			
			<!-- Ссылка на следующую страницу для Ctrl + вправо -->
			<xsl:if test="($n - 1) > $page and $i = $page">
				<a href="{$group_link}page-{$page+2}/{$tag_path}" id="id_next"></a>
			</xsl:if>
			
			<!-- Не ставим ссылку на страницу-->
			<xsl:if test="$i = $page">
				<span class="current">
					<xsl:value-of select="$i+1"/>
				</span>
			</xsl:if>
			
			<!-- Рекурсивный вызов шаблона. НЕОБХОДИМО ПЕРЕДАВАТЬ ВСЕ НЕОБХОДИМЫЕ ПАРАМЕТРЫ! -->
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="limit" select="$limit"/>
				<xsl:with-param name="page" select="$page"/>
				<xsl:with-param name="items_count" select="$items_count"/>
				<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
				<xsl:with-param name="post_count_page" select="$post_count_page"/>
				<xsl:with-param name="visible_pages" select="$visible_pages"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Склонение после числительных -->
	<xsl:template name="declension">
		
		<xsl:param name="number" select="number"/>
		
		<!-- Именительный падеж -->
		<xsl:variable name="nominative">
			<xsl:text>комментарий</xsl:text>
		</xsl:variable>
		
		<!-- Родительный падеж, единственное число -->
		<xsl:variable name="genitive_singular">
			<xsl:text>комментария</xsl:text>
		</xsl:variable>
		
		
		<xsl:variable name="genitive_plural">
			<xsl:text>комментариев</xsl:text>
		</xsl:variable>
		
		<xsl:variable name="last_digit">
			<xsl:value-of select="$number mod 10"/>
		</xsl:variable>
		
		<xsl:variable name="last_two_digits">
			<xsl:value-of select="$number mod 100"/>
		</xsl:variable>
		
		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12    or $last_digit = 3 and $last_two_digits != 13    or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>