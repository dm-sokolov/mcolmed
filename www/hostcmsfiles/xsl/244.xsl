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
		
		<div class="title" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem">
			<xsl:value-of select="name"/>
		</div>
		
		<!-- Отображение записи информационной системы -->
		<xsl:apply-templates select="informationsystem_item"/>
		
		<div style="clear: both"></div>
		
		<xsl:if test="ОтображатьСсылкуНаАрхив=1">
			<a href="{url}">Все новости</a>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<strong><xsl:value-of disable-output-escaping="yes" select="date"/></strong>
		<dir class="news_title">
			<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="informationsystem_item"><xsl:value-of select="name"/></a>
		</dir>
		
		<xsl:if test="description != ''">
			<div hostcms:id="{@id}" hostcms:field="description" hostcms:entity="informationsystem_item" hostcms:type="wysiwyg">
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			<xsl:text> </xsl:text><a href="{url}"><img alt="" src="/images/site15/pim2.jpg" /></a>
			</div>
		</xsl:if>
		<br /><br />
	</xsl:template>
</xsl:stylesheet>