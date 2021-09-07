<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- УслугиСправаNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		<xsl:if test=".//informationsystem_group[parent_id=$group]">
			<div class="navbar">
				<div class="navbar-nav">
					
					<div class="h4 text-left">Наши услуги</div>
					<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" />
				</div>
			</div>
		</xsl:if>
	</xsl:template>
	
	<xsl:variable name="current_group" select="/informationsystem/current_group"/>
	
	<xsl:template match="informationsystem_group">
		<li class="nav-item text-left">
			<a href="{url}" class="nav-link">
				<xsl:if test="$current_group = @id or property_value[tag_name = 'red']/value = 1">
					<xsl:attribute name="class">list-group-item active</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="name"/>
			</a>
		</li>
	</xsl:template>
</xsl:stylesheet>