<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокЭлементовИнфосистемыNEW -->
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
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		
		<!-- Если в находимся корне - выводим название информационной системы -->
		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1><xsl:value-of select="name"/><xsl:value-of select="$city" /></h1>
				
				<!-- Описание выводится при отсутствии фильтрации по тэгам -->
				<xsl:if test="count(tag) = 0 and page = 0 and description != ''">
					<div><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<h1>
					<xsl:value-of select="$current_group/name"/><xsl:value-of select="$city" />
				</h1>
				
				<!-- Описание выводим только на первой странице -->
				<xsl:if test="page = 0 and $current_group/description != ''">
					<div><xsl:value-of disable-output-escaping="yes" select="$current_group/description"/></div>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		
		<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<div class="group_list">
				<ul><xsl:apply-templates select=".//informationsystem_group[parent_id=$group][position() mod $n = 1]" /></ul>
			</div>
		</xsl:if>
		
		<div>
			<xsl:apply-templates select="informationsystem_item"/>
		</div>
		
		<!-- Строка ссылок на другие страницы информационной системы -->
		<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы=1">
			
			<!-- Ссылка, для которой дописываются суффиксы page-XX/ -->
			<xsl:variable name="link">
				<xsl:value-of select="/informationsystem/url"/>
				<xsl:if test="$group != 0">
					<xsl:value-of select="/informationsystem//informationsystem_group[@id = $group]/url"/>
				</xsl:if>
			</xsl:variable>
			
			<xsl:if test="total &gt; 0 and limit &gt; 0">
				
				<xsl:variable name="count_pages" select="ceiling(total div limit)"/>
				
				<xsl:variable name="visible_pages" select="5"/>
				
				<xsl:variable name="real_visible_pages"><xsl:choose>
						<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
						<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<!-- Считаем количество выводимых ссылок перед текущим элементом -->
				<xsl:variable name="pre_count_page"><xsl:choose>
						<xsl:when test="page - (floor($real_visible_pages div 2)) &lt; 0">
							<xsl:value-of select="page"/>
						</xsl:when>
						<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - ($count_pages - page - 1) - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="round($real_visible_pages div 2) = $real_visible_pages div 2">
									<xsl:value-of select="floor($real_visible_pages div 2) - 1"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="floor($real_visible_pages div 2)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<!-- Считаем количество выводимых ссылок после текущего элемента -->
				<xsl:variable name="post_count_page"><xsl:choose>
						<xsl:when test="0 &gt; page - (floor($real_visible_pages div 2) - 1)">
							<xsl:value-of select="$real_visible_pages - page - 1"/>
						</xsl:when>
						<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<xsl:variable name="i"><xsl:choose>
						<xsl:when test="page + 1 = $count_pages"><xsl:value-of select="page - $real_visible_pages + 1"/></xsl:when>
						<xsl:when test="page - $pre_count_page &gt; 0"><xsl:value-of select="page - $pre_count_page"/></xsl:when>
						<xsl:otherwise>0</xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<nav aria-label="Page navigation example">
					<ul class="pagination">
						<xsl:call-template name="for">
							<xsl:with-param name="limit" select="limit"/>
							<xsl:with-param name="page" select="page"/>
							<xsl:with-param name="items_count" select="total"/>
							<xsl:with-param name="i" select="$i"/>
							<xsl:with-param name="post_count_page" select="$post_count_page"/>
							<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
							<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
						</xsl:call-template>
					</ul>
				</nav>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_group">
		<li>
			<xsl:if test="image_small!=''">
				<a href="{url}" target="_blank">
					<img src="{dir}{image_small}" align="middle"/>
		</a><xsl:text> </xsl:text></xsl:if>
	<a href="{url}"><xsl:value-of select="name"/></a><xsl:text> </xsl:text><span class="count">(<xsl:value-of select="items_total_count"/>)</span>
		</li>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<div class="media mb-4 d-flex flex-column flex-md-row">
			<xsl:choose>
				<xsl:when test="image_small!=''">
					<a href="{url}" title="{name}">
						<img src="{dir}{image_small}" class="mr-3 mb-4" alt="{name}" />
					</a>
				</xsl:when>
				<xsl:otherwise>
					<a href="{url}" title="{name}">
						<img src="/img/noimage.png" class="mr-3" alt="{name}" />
					</a>
				</xsl:otherwise>
			</xsl:choose>
			
			<div class="media-body">
				<div class="h5 mt-0">
					<a href="{url}">
						<xsl:value-of select="name"/>
					</a>
				</div>
				
				<div class="mb-2 text-secondary">
					<xsl:value-of disable-output-escaping="yes" select="date"/>
				</div>
				
				<xsl:if test="description != ''">
					<div class="mb-2"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				</xsl:if>
				
				<a href="{url}" title="{name}">Читать далее...</a>
				
			</div>
		</div>
	</xsl:template>
	
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
				<li class="page-item">
				<a href="{$group_link}{$tag_path}" class="page-link"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a>
				</li>
			</xsl:if>
			
			<!-- Ставим ссылку на страницу-->
			<xsl:if test="$i != $page">
				<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
					<li class="page-item">
						<a href="{$group_link}{$number_link}{$tag_path}" class="page-link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</li>
				</xsl:if>
				
				<!-- Выводим ссылку на последнюю страницу -->
				<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
					<li class="page-item">
					<a href="{$group_link}page-{$n}/{$tag_path}" class="page-link" title="Последняя страница {$n}"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a>
					</li>
				</xsl:if>
			</xsl:if>
			
			<!-- Не ставим ссылку на страницу-->
			<xsl:if test="$i = $page">
				<li class="page-item active">
					<span class="page-link">
						<xsl:value-of select="$i+1"/>
					</span>
				</li>
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
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12
				or $last_digit = 3 and $last_two_digits != 13
				or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>