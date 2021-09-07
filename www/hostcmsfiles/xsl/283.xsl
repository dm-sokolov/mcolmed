<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокЭлементовИнфосистемы -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		<div>Варикозная болезнь</div>
		<ul class="ls-none">
		<li><a href="/services/209/210/1536/">Диагностика варикоза</a></li>
		<li><a href="/services/209/210/1567/">Лечение варикоза</a></li>
		<li><a href="/services/209/210/1699/">Лечение сосудистых звездочек</a></li>
		<li><a href="/services/209/210/1568/">Склеротерапия</a></li>
		<li><a href="/services/209/210/1573/">Эндовазальная лазерная коагуляция</a></li>
		<li><a href="/services/209/210/1575/">Лечение без операции</a></li>
		</ul>
	</xsl:template>
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<!-- <xsl:apply-templates select="informationsystem_item" />
	
	<xsl:template match="informationsystem_item">
		
		<li>
			
			<a href="{url}">
				<xsl:value-of select="name"/></a>
			
		</li>
		
	</xsl:template>	-->
	
</xsl:stylesheet>