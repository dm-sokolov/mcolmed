<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ПреимуществаНаГлавной -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:variable name="count" select="count(/informationsystem//informationsystem_group[parent_id=$group])"/>
	<xsl:variable name="n" select="ceiling($count div 3)"/>
	
	<xsl:template match="informationsystem">
		
		<xsl:if test="informationsystem_item">
			<div class="h2 font-weight-bold main-title advantages-title">
				<xsl:choose>
					<xsl:when test="other_name/node()">
						<xsl:value-of disable-output-escaping="yes" select="other_name" />
					</xsl:when>
					<xsl:otherwise>Почему клиенты довольны нашей работой</xsl:otherwise>
				</xsl:choose>
			</div>
			<div class="">
				<div class="advantages-box">
					<xsl:apply-templates select="informationsystem_item" />
				</div>
			</div>
		</xsl:if>
		
	</xsl:template>
	
	<xsl:template match="informationsystem_item">
		
		<div class="mb-4">
			<div class="advantage-desc" style="background-image: url({dir}{image_small});">
				<div class="h4 text-dark advantages-subtitle"><xsl:value-of select="name"/></div>
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</div>
		</div>
		
		<xsl:if test="position() mod 2 = 0 and position() != last()">
			<div class="w-100"></div>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>