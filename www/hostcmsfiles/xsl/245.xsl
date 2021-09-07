<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/site">
		<p class="men">
			<!-- Выбираем узлы структуры первого уровня -->
			<xsl:apply-templates select="structure[show=1]"/>
		</p>
	</xsl:template>
	
	<xsl:template match="structure">
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
		<a href="{$link}" title="{name}"><xsl:value-of select="name"/></a>
		
		<xsl:if test="position() != last()">
			&#xA0;&#xA0;&#xA0;|&#xA0;&#xA0;&#xA0;
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>