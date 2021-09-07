<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ОтзывыНаГлавнойNEW -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		
		<xsl:if test="informationsystem_item">
			<div class="reviews-box">
				<h2 class="h2 text-left main-title reviews-title font-weight-bold">
					Отзывы клиентов
				</h2>
				<div class="review-slider mb-md-4">
					<div id="reviewSlider" class="owl-carousel owl-theme mb-3">
						<xsl:apply-templates select="informationsystem_item"/>
					</div>
					
				</div>
				
			</div>
			<div class="reviews-box__controls d-flex flex-column flex-sm-row ">
				<div class="text-left">
				<button type="submit" class="btn btn-primary" onclick="$.showXslTemplate('{/informationsystem/url}', {/informationsystem/@id}, 321); return false;">Оставить отзыв </button> </div>
				<a class="mt-3 mt-sm-0 reviews-box__btn" href="{url}">Читать все отзывы</a>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<div class="item">
			<div class="h4"><xsl:value-of select="property_value[tag_name='author']/value"/></div>
			<div class="mb-1 review-text"><xsl:value-of disable-output-escaping="yes" select="description"  /></div>
			<xsl:if test="description_real/node()">
				<div class="mb-1">
					<a href="{url}">Читать полностью</a>
				</div>
			</xsl:if>
			<div class="mb-1 item-date">
				<xsl:value-of select="substring-before(date, '.')"/>
				<xsl:variable name="month_year" select="substring-after(date, '.')"/>
				<xsl:variable name="month" select="substring-before($month_year, '.')"/>
				<xsl:choose>
					<xsl:when test="$month = 1"> января </xsl:when>
					<xsl:when test="$month = 2"> февраля </xsl:when>
					<xsl:when test="$month = 3"> марта </xsl:when>
					<xsl:when test="$month = 4"> апреля </xsl:when>
					<xsl:when test="$month = 5"> мая </xsl:when>
					<xsl:when test="$month = 6"> июня </xsl:when>
					<xsl:when test="$month = 7"> июля </xsl:when>
					<xsl:when test="$month = 8"> августа </xsl:when>
					<xsl:when test="$month = 9"> сентября </xsl:when>
					<xsl:when test="$month = 10"> октября </xsl:when>
					<xsl:when test="$month = 11"> ноября </xsl:when>
					<xsl:otherwise> декабря </xsl:otherwise>
				</xsl:choose>
				<xsl:value-of select="substring-after($month_year, '.')"/><xsl:text> г.</xsl:text>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>