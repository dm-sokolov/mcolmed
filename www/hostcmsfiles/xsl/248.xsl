<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:decimal-format name="my" decimal-separator="." grouping-separator=""/>
	
	<!-- МагазинКаталогТоваровНаГлавнойСпецПред -->
	<xsl:template match="/">
		<xsl:apply-templates select="/shop"/>
	</xsl:template>
	
	<!-- Шаблон для магазина -->
	<xsl:template match="/shop">
		<!-- Есть товары -->
		<xsl:if test="shop_item">
			<h1>Горячие предложения</h1>
			<!-- Выводим товары магазина -->
			<div class="column p_l">
				<xsl:apply-templates select="shop_item"/>
			</div>
			<div style="clear: both;"></div>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для товара -->
	<xsl:template match="shop_item">
		<div class="good_block">
			<!-- Указана малое изображение -->
			<xsl:if test="image_small != ''">
				<a href="{url}">
					<img src="{dir}{image_small}" alt="{name}" title="{name}" style="margin-top: 5%; margin-bottom: 3px;"/>
				</a>
			</xsl:if>
			<div class="index_item_title">
				<a href="{url}" title="{name}">
					<xsl:value-of select="name"/>
				</a>
			</div>
		</div>
		
		<div class="dcc">
			<div class="deteils">
				<div class="inner">
					<xsl:value-of select="price"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="currency"/>
				</div>
			</div>
			<div class="in_cart">
				<div class="inner">
					<a href="{/shop/url}cart/?add={@id}" onclick="return $.addIntoCart('{/shop/url}cart/', {@id}, 1)">Купить</a>
				</div>
			</div>
			<div style="clear: both" />
		</div>
		
		<xsl:if test="position() = round(/shop/limit div 2)">
			<xsl:text disable-output-escaping="yes">
				&lt;/div&gt;
				&lt;div class="right p_r"&gt;
			</xsl:text>
		</xsl:if>
		
	</xsl:template>
</xsl:stylesheet>