<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокСпециалистовNEW-->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		<div class="bg-light p-4 mb-4">
			<div class="h3 text-center text-dark mb-3">
				<xsl:choose>
					<xsl:when test="other_name/node()">
						<xsl:value-of disable-output-escaping="yes" select="other_name" />
					</xsl:when>
					<xsl:otherwise>Наши врачи</xsl:otherwise>
				</xsl:choose>
			</div>
			<div class="row">
				<xsl:apply-templates select="informationsystem_item"/>
			</div>
		</div>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<div class="col-sm-6 mb-4">
			<div class="row">
				<div class="col-sm-5">
					<xsl:if test="image_small!=''">
						<a class="specialist-img d-block rounded" href="{url}" style="background-image:url('{dir}{image_small}');"></a>
					</xsl:if>
				</div>
				<div class="col-sm-7">
					<div class="h5">
						<a href="{url}"><xsl:value-of select="name"/></a>
					</div>
					<xsl:if test="description != ''">
						<div class="mb-3 font-italic"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
					</xsl:if>
					<button class="btn btn-primary" onclick="$.showXslTemplate('/callback/', 3, 298); return false;">Задать вопрос</button>
				</div>
			</div>
		</div>
		<xsl:if test="position() mod 2 = 0 and position() != last()">
			<div class="w-100"></div>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>