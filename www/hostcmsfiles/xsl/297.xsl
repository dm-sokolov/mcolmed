<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ЛицензииНаГлавнойNEW -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:template match="informationsystem">
		
		<xsl:if test="informationsystem_item">
			
			<h2 class="h2 text-left mb-4 font-weight-bold main-title license-title">Наши лицензии</h2>
			
			<div class="row justify-content-md-center">
				<div class="col col-lg-11">
					<div id="licenseSlider" class="owl-carousel owl-theme owl-arrow-black mb-4">
						<xsl:apply-templates select="informationsystem_item"/>
					</div>
				</div>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<div class="item">
			<a href="{dir}{image_large}" data-fancybox="gallery_{informationsystem_id}" title="{name}">
				<img class="owl-lazy" data-src="{dir}{image_small}" alt="{name}" />
			</a>
		</div>
	</xsl:template>
</xsl:stylesheet>