<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/shop"/>
	</xsl:template>
	
	<!-- Шаблон для магазина -->
	<xsl:template match="/shop">
		<!-- Выводим группы магазина -->
		<ul>
			<xsl:apply-templates select="shop_group" />
		</ul>
	</xsl:template>
	
	<!-- Шаблон для групп товара -->
	<xsl:template match="shop_group">
		<xsl:if test="9 > position()">
			<li>
				<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group"><xsl:value-of select="name"/></a>
			</li>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>