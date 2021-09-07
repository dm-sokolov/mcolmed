<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/form"/>
	</xsl:template>
	
	<xsl:template match="/form">
		
	<p><b><xsl:value-of select="name"/></b></p>
		<p>Заполнена форма № <xsl:value-of select="form_fill/@id"/></p>
		<xsl:apply-templates select="form_field[name!='pole']"/>
		<p>
			<!--br/>Медицинский центр «ОЛМЕД», лечение варикозной болезни современными методами
		<br/>
		<a href="https://www.mcolmed.ru">wwws.mcolmed.ru</a-->
		<xsl:value-of select="email_subject" /><br/>
		<xsl:if test="other_name != ' ' ">
			<xsl:value-of select="other_name" />
		</xsl:if>
	</p>
</xsl:template>

<xsl:template match="form_field">
	<p>
		<b>
			<xsl:value-of select="caption" />
		</b>:
		
		<xsl:choose>
			<xsl:when test="values/node()">
				<xsl:apply-templates select="values/value"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="value" />
			</xsl:otherwise>
		</xsl:choose>
	</p>
</xsl:template>

<xsl:template match="values/value"><xsl:variable name="currentValue" select="." /><xsl:value-of select="../../list/list_item[@id=$currentValue]/value"/><xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if></xsl:template>

</xsl:stylesheet>