<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокНовостейНаГлавной -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		<!-- Выводим название информационной системы -->
		<p class="h1" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem" style="float:left;">
			<a href="{url}"><xsl:value-of select="name"/></a>
		</p>
<div class="all-n"><a href="{url}" title="Все новости">Все новости</a><span> &#187;&#187;&#187;&#187;</span></div>
		<div class="clr"></div>
		<div class="hr"></div>
		<!-- Отображение записи информационной системы -->
		<xsl:if test="informationsystem_item">
			<ul class="ls-none">
				<xsl:apply-templates select="informationsystem_item"/>
			</ul>
		</xsl:if>
		<div class="clr"></div>
		
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<li>
			<!-- Дата время -->
			<div class="n-data">
				<xsl:value-of disable-output-escaping="yes" select="date"/>
			</div>
			<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item" class="n-title">
				<xsl:value-of select="name"/>
			</a>
			<p><xsl:value-of disable-output-escaping="yes" select="description"/></p>
			<a href="{url}" class="more">Читать далее...</a>
		</li>
		<xsl:if test="position() mod 2 = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;div class="clr"&gt;
				&lt;/div&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>