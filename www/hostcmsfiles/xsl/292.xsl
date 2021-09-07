<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокНовостейНаГлавной -->
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		<xsl:if test="informationsystem_item">
			<div id="mainSlider" class="owl-carousel owl-theme">
				<xsl:apply-templates select="informationsystem_item"/>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<a class="item">
			<xsl:choose>
				<xsl:when test="property_value[tag_name = 'link_page']/value != ''">
					<xsl:attribute name="href"><xsl:value-of select="property_value[tag_name = 'link_page']/value"/></xsl:attribute>
				</xsl:when>
				<xsl:when test="property_value[tag_name = 'link_news']/informationsystem_item != ''">
					<xsl:attribute name="href"><xsl:value-of select="property_value[tag_name ='link_news']/informationsystem_item/url"/></xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<div class="row d-flex justify-content-center">
				<div class="col-5 col-md-5">
					<h4 class="mb-4"><xsl:value-of select="property_value[tag_name = 'title']/value"/></h4>
					<h5 class="mb-6"><xsl:value-of select="property_value[tag_name = 'full_description']/value"/></h5>
					<button class="btn slider-button"><xsl:value-of select="property_value[tag_name = 'dop_description']/value"/></button>
				</div>
				<div class="col-md-4 slider-info col" style="background-image: url({dir}{image_small})">
				</div>
			</div>
			
			<xsl:if test="description!=''">
				<div class="main-text"><xsl:value-of disable-output-escaping="yes" select="description" /></div>
			</xsl:if>
			
			<xsl:if test="text!=''">
				<div class="sub-text"><xsl:value-of disable-output-escaping="yes" select="text" /></div>
			</xsl:if>
		</a>
	</xsl:template>
</xsl:stylesheet>