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
		
		<!-- Получаем ID родительской группы и записываем в переменную $group -->
		<xsl:variable name="group" select="group"/>
		
		<xsl:if test="group!=0">
			<xsl:if test="count(informationsystem_group[@id = $group]/informationsystem_group) &gt; 0 or count(informationsystem_item) &gt; 0">
				<div class="nav">
					<ul class="ls-none">
						<div>
							<xsl:value-of disable-output-escaping="yes" select="//informationsystem_group[@id = $group]/name"/>
						</div>
						<xsl:if test="informationsystem_group[@id = $group]">
							<xsl:apply-templates select="informationsystem_group/informationsystem_group" />
						</xsl:if>
						<xsl:apply-templates select="informationsystem_item" />
					</ul>
					<div class="clr"></div>
				</div>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_group">
		<li>
			<a href="{url}"><xsl:value-of select="name"/></a>
		</li>
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_item">
		<li>
			<a href="{url}"><xsl:value-of select="name"/></a>
		</li>
	</xsl:template>
</xsl:stylesheet>