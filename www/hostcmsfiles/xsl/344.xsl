<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- УслугиНаГлавнойNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:variable name="count" select="count(/informationsystem//informationsystem_group[parent_id=$group])"/>
	<xsl:variable name="n" select="ceiling($count div 3)"/>
	
	<xsl:template match="informationsystem">
		<div class="h1 text-center mb-4">Услуги медицинского центра</div>
		
		<xsl:if test=".//informationsystem_group[parent_id=$group]">
			<div class="row mb-3">
				<div class="col-sm-4">
					<div class="service-box">
						<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" />
					</div>
				</div>
			</div>
		</xsl:if>
	</xsl:template>
	
	<xsl:variable name="current_group" select="/informationsystem/current_group"/>
	
	<xsl:template match="informationsystem_group">
		
		<div class="mb-1">
			<a href="{url}">
				<xsl:if test="$current_group = @id or property_value[tag_name = 'red']/value = 1">
					<xsl:attribute name="class">active</xsl:attribute>
				</xsl:if>
				<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
				<xsl:text> </xsl:text>
				<xsl:value-of select="name"/>
			</a>
		</div>
		
		<xsl:if test="position() mod $n = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/div&gt;
				&lt;/div&gt;
				&lt;div class="col-sm-4"&gt;
				&lt;div class="service-box"&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>