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
		<div class="mb-3 service-box-wrap owl-carousel owl-theme owl-drag">
			<xsl:apply-templates select="informationsystem"/>
		</div>
	</xsl:template>
	<xsl:variable name="count" select="count(/informationsystem//informationsystem_group[parent_id=$group and name != 'Акции' and name != 'Вакансии' and name != 'Поликлиника']) + 1"/>
	<xsl:variable name="n" select="ceiling($count div 3)"/>
	<xsl:template match="informationsystem">
		<xsl:if test=".//informationsystem_group[parent_id=$group]">
			<div class="service-box">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" />
				<xsl:if test="($count - 1) mod 3  != 0">
				<!--div class="mb-1"><a href="/services/gift-certificate/"> <xsl:value-of select="$count"/>Подарочный сертификат</a></div-->
			</xsl:if>
		</div>
		<!--xsl:if test="($count - 1) mod 3  = 0">
		<div class="service-box">
		<div class="mb-1"><a href="/services/gift-certificate/"><xsl:value-of select="$count"/>Подарочный сертификат</a></div>
		</div>
	</xsl:if-->
</xsl:if>
</xsl:template>

<xsl:variable name="current_group" select="/informationsystem/current_group"/>

<xsl:template match="informationsystem_group">
<xsl:if test="name != 'Акции' and name != 'Вакансии' and name != 'Поликлиника'">
	<div class="mb-1">
		<a href="{url}">
			<xsl:if test="$current_group = @id or property_value[tag_name = 'red']/value = 1">
				<xsl:attribute name="class">active</xsl:attribute>
			</xsl:if>
			
			<xsl:text> </xsl:text>
			<xsl:value-of select="name"/>
		</a>
	</div>
	<xsl:if test="position() mod 3  = 0 and position() != last()">
		<xsl:text disable-output-escaping="yes">
			&lt;/div&gt;
			&lt;div class="service-box"&gt;
		</xsl:text>
	</xsl:if>
</xsl:if>
</xsl:template>
</xsl:stylesheet>