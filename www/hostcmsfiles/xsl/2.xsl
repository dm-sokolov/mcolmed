<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/site">
		<ul class="ls-none">
			<!-- Выбираем узлы структуры первого уровня -->
			<xsl:apply-templates select="structure[show=1]" />
		</ul>
	</xsl:template>
	
	<!-- Запишем в константу ID структуры, данные для которой будут выводиться пользователю -->
	<xsl:variable name="current_structure_id" select="/site/current_structure_id"/>
	
	<xsl:template match="structure">
		
		<xsl:variable name="empty">
			<xsl:if test="count(structure) = 0">empty</xsl:if>
		</xsl:variable>
		<xsl:variable name="current">
			<xsl:if test="$current_structure_id = @id or count(.//structure[@id=$current_structure_id]) = 1">current</xsl:if>
		</xsl:variable>
		<xsl:variable name="last">
			<xsl:if test="position() = last()">last</xsl:if>
		</xsl:variable>
		
		<li class="{$current} {$empty} {$last}">
			<!-- Определяем адрес ссылки -->
			<xsl:variable name="link">
				<xsl:choose>
					<!-- Если внешняя ссылка -->
					<xsl:when test="url != ''">
						<xsl:value-of disable-output-escaping="yes" select="url"/>
					</xsl:when>
					<!-- Иначе если внутренняя ссылка -->
					<xsl:otherwise>
						<xsl:value-of disable-output-escaping="yes" select="link"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			
			<!-- Ссылка на пункт меню -->
		<a href="{$link}" title="{name}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="structure"><xsl:value-of select="name"/></a><span></span>
			<xsl:if test="count(structure) &gt; 0">
				<ul class="ls-none submenu">
					<xsl:apply-templates select="structure[show=1]" />
				</ul>
			</xsl:if>
		</li>
	</xsl:template>
</xsl:stylesheet>