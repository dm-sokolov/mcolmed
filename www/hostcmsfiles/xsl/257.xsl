<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- Шаблон для платежных систем -->
	<xsl:template match="/shop">
		
		<!-- Строка шага заказа -->
		<ul class="shop_navigation gray">
		<li><span>Адрес доставки</span>&#x2192;</li>
		<li><span>Способ доставки</span>&#x2192;</li>
		<li class="shop_navigation_current"><span>Форма оплаты</span>&#x2192;</li>
		<li><span>Данные доставки</span></li>
		</ul>
		
		<h1>Выбор формы оплаты</h1>
		
		<form method="post">
			
			<xsl:choose>
				<xsl:when test="count(shop_payment_system) = 0">
					<p>
						<b>В данный момент нет доступных платежных систем!</b>
					</p>
					<p>Оформление заказа невозможно, свяжитесь с администрацией Интернет-магазина.</p>
				</xsl:when>
				<xsl:otherwise>
					<table cellspacing="0" cellpadding="0" border="0" class="shop_cart_table">
						<tr class="shop_cart_table_title">
							<th>Форма оплаты</th>
							<th>Описание</th>
						</tr>
						<xsl:apply-templates select="shop_payment_system"/>
					</table>
					<div class="gray_button">
						<div>
							<input name="step" value="4" type="hidden" />
							<input value="Далее →" type="submit" class="cart_button" />
						</div>
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</form>
	</xsl:template>
	
	<xsl:template match="shop_payment_system">
		<tr>
			<td width="40%">
				
				<input type="radio" class="input_radio" name="shop_payment_system_id" value="{@id}" id="system_of_pay_{@id}" style="border: 0px">
					<xsl:if test="position() = 1">
						<xsl:attribute name="checked"></xsl:attribute>
					</xsl:if>
				</input>
				&#xA0;
				<label for="system_of_pay_{@id}">
					<b>
						<xsl:value-of select="name"/>
					</b>
				</label>
			</td>
			<td width="60%">
				<!-- Описание платежной системы -->
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>