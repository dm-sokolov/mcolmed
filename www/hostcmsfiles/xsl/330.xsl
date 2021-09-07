<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:hostcms="http://www.hostcms.ru/"
				exclude-result-prefixes="hostcms">

	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>

	<!-- СписокСотрудниковNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	<xsl:variable name="city"> в Нижней Туре</xsl:variable>

	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>

	<xsl:template match="informationsystem">
		<div class="worker">
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
					<xsl:if test="other_name/node()">
						<h2><xsl:value-of disable-output-escaping="yes" select="other_name" /></h2>
					</xsl:if>
					<xsl:if test="not(other_name/node())">
						<h1><xsl:value-of select=".//informationsystem_group[@id=$group]/name"/><xsl:value-of select="$city" /></h1>
					</xsl:if>
					<!-- Описание выводим только на первой странице -->
					<xsl:if test="page = 0 and .//informationsystem_group[@id=$group]/description != ''">
						<div><xsl:value-of disable-output-escaping="yes" select=".//informationsystem_group[@id=$group]/description"/></div>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>

			<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
			<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
				<div class="list-group list-specialist">
					<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" />
				</div>
			</xsl:if>

			<!-- Отображение записи информационной системы -->
			<ul>
				<xsl:apply-templates select="informationsystem_item"/>
			</ul>

			<!-- Строка ссылок на другие страницы информационной системы -->
			<xsl:if test="ОтображатьСсылкиНаСледующиеСтраницы=1">
				<div>
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

						<p>
							<xsl:call-template name="for">
								<xsl:with-param name="limit" select="limit"/>
								<xsl:with-param name="page" select="page"/>
								<xsl:with-param name="items_count" select="total"/>
								<xsl:with-param name="i" select="$i"/>
								<xsl:with-param name="post_count_page" select="$post_count_page"/>
								<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
								<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
							</xsl:call-template>
						</p>
						<div style="clear: both"></div>
					</xsl:if>
				</div>
			</xsl:if>
		</div>
	</xsl:template>

	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_group">
		<div class="h4">
			<a href="{url}"><xsl:value-of select="name"/></a>
			<!--xsl:text> </xsl:text>
		<small class="text-secondary">(<xsl:value-of select="items_total_count"/>)</small-->
		</div>
	</xsl:template>

	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<li>
			<div class="worker__left">
				<xsl:if test="image_small!=''">
					<div class="worker__image" style="background-image: url('{dir}{image_small}')"></div>
				</xsl:if>
			</div>
			<div class="worker__right">
				<h3>
					<a href="{url}"><xsl:value-of select="name"/></a>
				</h3>
				<xsl:if test="description != ''">
					<div class="worker__description"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				</xsl:if>
				<div class="worker__button">
					<xsl:variable name="doctor" select="name"></xsl:variable>
					<button onclick="$.showXslTemplate('/callback/', 86, 357, ' ', '{$doctor}'); return false;"><span>Запись к врачу</span></button>
				</div>
			</div>
		</li>
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

</xsl:stylesheet>