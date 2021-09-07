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
			<xsl:apply-templates select="informationsystem_item" />
		</ul>
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_item">
		<xsl:for-each select=". | following-sibling::informationsystem_group[position() &lt; $n]">
			<li>
				
				<a href="{url}">
					<xsl:if test="property_value[tag_name = 'red']/value = 1">
						<xsl:attribute name="style">color:#BB2121;background-position:left -29px;font-weight:bold;</xsl:attribute>
					</xsl:if>
					
					<xsl:value-of select="name"/></a>
				
			</li>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>