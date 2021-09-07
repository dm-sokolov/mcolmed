<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокНовостейНаГлавной -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem">
		<!-- Отображение записи информационной системы -->
		<xsl:if test="informationsystem_item">
			<div class="main-slides">
				<ul class="slides">
					<xsl:apply-templates select="informationsystem_item"/>
				</ul>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<li>
			<div class="big-pict" style="background:url({dir}{image_small}) no-repeat {property_value[tag_name = 'x']/value}% {property_value[tag_name = 'y']/value}%;">
				<xsl:choose>
					<xsl:when test="property_value[tag_name = 'link_page']/value != ''">
						<a href="{property_value[tag_name = 'link_page']/value}" title="" class="link-news"></a>
					</xsl:when>
					<xsl:when test="property_value[tag_name = 'link_news']/informationsystem_item != ''">
						<a href="{property_value[tag_name ='link_news']/informationsystem_item/url}" title="{property_value[tag_name ='link_news']/informationsystem_item/name}" class="link-news"></a>
					</xsl:when>
					<xsl:otherwise></xsl:otherwise>
				</xsl:choose>
				<div class="main-text"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				<div class="sub-text"><xsl:value-of disable-output-escaping="yes" select="text"/></div>
			</div>
		</li>
	</xsl:template>
</xsl:stylesheet>