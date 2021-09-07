<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ПопулярныеУслугиФутерNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		
		<div class="footer-menu">
			<div class="h5 mb-3">Популярные услуги</div>
			
			<xsl:if test=".//informationsystem_group[parent_id=$group]">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" />
			</xsl:if>
		</div>
	</xsl:template>
	
	<xsl:variable name="current_group" select="/informationsystem/current_group"/>
	
	<xsl:template match="informationsystem_group">
		
		<a class="footer-menu-item d-block" href="{url}">
			<xsl:value-of select="name"/>
		</a>
		
		<!--<xsl:if test="$current_group = @id">
			<xsl:attribute name="class">kras</xsl:attribute>
		</xsl:if>-->
	</xsl:template>
</xsl:stylesheet>