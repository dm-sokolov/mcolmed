<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	
	<!-- Шаблон для типов доставки -->
	<xsl:template match="/shop">
		
		<!-- Строка шага заказа -->
		<ul class="shop_navigation gray">
		<li><span>Адрес доставки</span>→</li>
		<li class="shop_navigation_current"><span>Способ доставки</span>→</li>
		<li><span>Форма оплаты</span>→</li>
		<li><span>Данные доставки</span></li>
		</ul>
		
		<h1>Способ доставки</h1>
		
		<form method="post">
			<!-- Проверяем количество способов доставки -->
			<xsl:choose>
				<xsl:when test="count(shop_delivery) = 0">
					<p>По выбранным Вами условиям доставка не возможна, заказ будет оформлен без доставки.</p>
					<p>Уточнить данные о доставке Вы можете, связавшись с представителем нашей компании.</p>
					<input type="hidden" name="shop_delivery_condition_id" value="0"/>
				</xsl:when>
				<xsl:otherwise>
					
					<table class="shop_cart">
						<tr class="shop_cart_table_title">
							<th>Способ доставки</th>
							<th>Описание</th>
							<th>Цена доставки</th>
							<th>Стоимость товаров</th>
							<th>Итого</th>
						</tr>
						<xsl:apply-templates select="shop_delivery"/>
					</table>
				</xsl:otherwise>
			</xsl:choose>
			<div class="gray_button">
				<div>
					<input name="step" value="3" type="hidden" />
					<input value="Далее →" type="submit" class="cart_button" />
				</div>
			</div>
		</form>
	</xsl:template>
	
	<xsl:template match="shop_delivery">
		<tr>
			<td>
				<label>
					<input type="radio" value="{shop_delivery_condition/@id}" id="shop_delivery_condition_id" name="shop_delivery_condition_id" style="border: 0px">
						<xsl:if test="position() = 1">
							<xsl:attribute name="checked"></xsl:attribute>
						</xsl:if>
			</input><xsl:text> </xsl:text><span class="caption"><xsl:value-of select="name"/></span>
				</label>
			</td>
			<td>
				<!-- Описание доставки -->
				<p>
					<xsl:value-of disable-output-escaping="yes" select="description"/>
				</p>
			</td>
			<td>
				<xsl:value-of select="format-number(shop_delivery_condition/price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name"/>
			</td>
			<td>
				<xsl:value-of select="format-number(/shop/total_amount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name"/>
			</td>
			<td style="font-weight: bold">
				<xsl:value-of select="format-number(/shop/total_amount + shop_delivery_condition/price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>