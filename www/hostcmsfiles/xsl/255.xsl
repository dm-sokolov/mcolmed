<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:exsl="http://exslt.org/common" extension-element-prefixes="exsl">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	
	<!-- Шаблон для корзины -->
	<xsl:template match="/shop">
		<xsl:choose>
			<xsl:when test="count(shop_cart) = 0">
				<!-- В корзине нет ни одного элемента -->
				<p class="title">В корзине нет ни одного товара.</p>
				<p>
					<xsl:choose>
						<!-- Пользователь авторизован или модуль пользователей сайта отсутствует -->
						<xsl:when test="siteuser_id > 0 or siteuser_id = ''">Для оформления заказа добавьте товар в корзину.</xsl:when>
						<xsl:otherwise>Вы не авторизированы. Если Вы зарегистрированный пользователь, данные Вашей корзины станут видны после авторизации.</xsl:otherwise>
					</xsl:choose>
				</p>
			</xsl:when>
			<xsl:otherwise>
				<!-- Вывод корзины -->
				<h1>Моя корзина</h1>
				
				<p>Для оформления заказа, нажмите &#171;Оформить заказ&#187;.</p>
				
				<form action="{/shop/url}cart/" name="address" method="post">
					<!-- Если есть товары -->
					<xsl:if test="count(shop_cart[postpone = 0]) > 0">
						<table cellspacing="0" cellpadding="0" border="0" class="shop_cart_table">
							<xsl:call-template name="tableHeader"/>
							<xsl:apply-templates select="shop_cart[postpone = 0]"/>					<xsl:call-template name="tableFooter">
								<xsl:with-param name="nodes" select="shop_cart[postpone = 0]"/>
							</xsl:call-template>
							<!--<xsl:apply-templates select="itemincart[flag_postpone = 0]"/>-->
							
							<!-- Скидки -->
							<xsl:if test="count(shop_purchase_discount)">
								<xsl:apply-templates select="shop_purchase_discount"/>
								<tr class="shop_cart_table">
									<td>Всего:</td>
									<td></td>
									<td></td>
									<td>
										<xsl:value-of select="format-number(total_amount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_currency/name"/>
									</td>
									<td></td>
									<xsl:if test="count(/shop/shop_warehouse)">
										<td></td>
									</xsl:if>
									<td></td>
									<td></td>
								</tr>
							</xsl:if>
							
							
							
							<!--
							<xsl:if test="total_sum_without_discount &gt; totalsum">
								<tr class="shop_cart_table">
									<td style="border-bottom: thin dashed #DADADA">
										<b>Скидка:</b>
									</td>
									<td style="border-bottom: thin dashed #DADADA">&#xA0;</td>
									<td style="border-bottom: thin dashed #DADADA">&#xA0;</td>
									<td style="border-bottom: thin dashed #DADADA; white-space: nowrap; font-weight: bold">
										<b>
											<xsl:value-of disable-output-escaping="yes" select="format-number(total_sum_without_discount - totalsum, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="shop/shop_currency/shop_currency_name"/></b>
									</td>
									<xsl:if test="/cart/shop/warehouses/node()">
										<td style="border-bottom: thin dashed #DADADA">&#xA0;</td>
									</xsl:if>
									<td style="border-bottom: thin dashed #DADADA">&#xA0;</td>
									<td style="border-bottom: thin dashed #DADADA">&#xA0;</td>
								</tr>
								<tr class="shop_cart_table">
									<td style="border-bottom: none">
										<b>Всего:</b>
									</td>
									<td style="border-bottom: none">&#xA0;</td>
									<td style="border-bottom: none">&#xA0;</td>
									<td style="border-bottom: none; white-space: nowrap; font-weight: bold">
										<xsl:value-of disable-output-escaping="yes" select="format-number(totalsum, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="shop/shop_currency/shop_currency_name"/>
									</td>
									<td style="border-bottom: none">&#xA0;</td>
									<xsl:if test="/cart/shop/warehouses/node()">
										<td style="border-bottom: none">&#xA0;</td>
									</xsl:if>
									<td style="border-bottom: none">&#xA0;</td>
									<td style="border-bottom: none">&#xA0;</td>
								</tr>
							</xsl:if>-->
						</table>
						
						<div style="clear: both;"></div>
					</xsl:if>
					
					<!-- Если есть отложенные товары -->
					<xsl:if test="count(shop_cart[postpone = 1]) > 0">
						<h2>Отложенные товары</h2>
						<div class="gray">
							<table cellspacing="0" cellpadding="0" border="0" class="shop_cart_table">
								<xsl:call-template name="tableHeader"/>
								<xsl:apply-templates select="shop_cart[postpone = 1]"/>
								<xsl:call-template name="tableFooter">
									<xsl:with-param name="nodes" select="shop_cart[postpone = 1]"/>
								</xsl:call-template>
							</table>
						</div>
						
						
						<!--
						<h2>Отложенные товары</h2>
						<div class="gray">
							<table cellspacing="0" cellpadding="0" border="0" class="shop_cart_table">
								<tr>
									<th>Товар</th>
									<th width="70">Кол-во</th>
									<th width="100">Цена</th>
									<th width="100">Сумма</th>
									<th>Отложить</th>
									<th>Действия</th>
								</tr>
								<xsl:apply-templates select="itemincart[flag_postpone = 1]"/>
								<tr class="shop_cart_table">
									<td>
										<b>Итого:</b>
									</td>
									<td>
										<b>
											<xsl:value-of disable-output-escaping="yes" select="totalquantity_postpone_item"/>
										</b>
									</td>
									<td>&#xA0;</td>
									<td style="white-space: nowrap">
										<xsl:value-of disable-output-escaping="yes" select="format-number(totalsum_postpone_item,'### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="shop/shop_currency/shop_currency_name"/>
									</td>
									<td>&#xA0;</td>
									<td>&#xA0;</td>
								</tr>
							</table>
						</div>
						-->
					</xsl:if>
					
					<!-- Купон -->
					<div style="padding: 15px 0px 5px 0px;">
						Купон:&#xA0;
						<input name="coupon_text" type="text" value="{coupon_text}" style="margin-right: 20px"/>
					</div>
					
					<!-- Кнопки -->
					<div class="gray_button" style="float: left;">
						<div>
							<input name="recount" value="Пересчитать" class="cart_button" type="submit" />
						</div>
					</div>
					<div style="clear: both;"></div>
					
					<xsl:if test="siteusers_class_exists = 1">
						<h1>Данные о заказчике</h1>
						
						<!-- Выводим сообщение -->
						<xsl:if test="/shop/message/node()">
							<div id="message">
								<xsl:value-of disable-output-escaping="yes" select="/shop/message"/>
							</div>
						</xsl:if>
						
						<p style="color: #707070">
						Поля, отмеченные <span class="red_star" style="position: relative; top: 6px;"> *</span>, обязательны для заполнения.
						</p>
						
						<!-- В случае если отключен модуль пользователей сайта, запрашиваем информацию
						о пользователе сайта здесь -->
						<table cellspacing="0" cellpadding="0" border="0" class="shop_cart_table">
							<tr>
								<td>Фамилия:</td>
								<td>
									<input name="siteusers_surname" type="text" value="{siteusers_surname}" size="40"/>
								</td>
								<td class="red_star"> *</td>
							</tr>
							<tr>
								<td>Имя:</td>
								<td>
									<input name="siteusers_name" type="text" value="{siteusers_name}" size="40"/>
								</td>
								<td class="red_star"> *</td>
							</tr>
							<tr>
								<td>Отчество:</td>
								<td>
									<input name="siteusers_patronymic" type="text" value="{siteusers_patronymic}" size="40"/>
								</td>
							</tr>
							<tr>
								<td>Компания:</td>
								<td>
									<input name="siteusers_company" type="text" value="{siteusers_company}" size="40"/>
								</td>
							</tr>
							<tr>
								<td>E-mail:</td>
								<td>
									<input name="siteusers_email" type="text" value="{siteusers_email}" size="40"/>
								</td>
								<td class="red_star"> *</td>
							</tr>
							<tr>
								<td>Телефон:</td>
								<td>
									<input name="siteusers_phone" type="text" value="{siteusers_phone}" size="40"/>
								</td>
							</tr>
							<tr>
								<td>Факс:</td>
								<td>
									<input name="siteusers_fax" type="text" value="{siteusers_fax}" size="40"/>
								</td>
							</tr>
							<tr>
								<td>Адрес:</td>
								<td>
									<input name="siteusers_address" type="text" value="{siteusers_address}" size="40"/>
								</td>
							</tr>
						</table>
						
						<!-- Добавляем скрытое поле с указанием подшага -->
						<input name="step_1_1a" type="hidden" value="1"/>
					</xsl:if>
					
					<xsl:if test="count(shop_cart[postpone = 0]) and siteuser_id > 0 or siteuser_id = ''">
						<div class="gray_button" style="float: right;">
							<div>
								<input name="step" value="1" type="hidden" />
								<input value="Оформить заказ" type="submit" class="cart_button" style="font-weight: bold"/>
								
								<!--<input name="step_1" value="Оформить заказ" type="submit" class="cart_button" style="font-weight: bold"/>-->
							</div>
						</div>
					</xsl:if>
				</form>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<!-- Заголовок таблицы -->
	<xsl:template name="tableHeader">
		<tr>
			<th>Товар</th>
			<th width="70">Кол-во</th>
			<th width="100">Цена</th>
			<th width="100">Сумма</th>
			<xsl:if test="count(/shop/shop_warehouse)">
				<th width="100">Склад</th>
			</xsl:if>
			<th>Отложить</th>
			<th>Действия</th>
		</tr>
	</xsl:template>
	
	
	<!-- Итоговая строка таблицы -->
	<xsl:template name="tableFooter">
		<xsl:param name="nodes"/>
		
		<tr class="total">
		<td><b>Итого:</b></td>
		<td><b><xsl:value-of select="sum($nodes/quantity)"/></b></td>
		<td><xsl:text> </xsl:text></td>
			<td>
				<xsl:variable name="subTotals">
					<xsl:for-each select="$nodes">
						<sum><xsl:value-of select="shop_item/price * quantity"/></sum>
					</xsl:for-each>
				</xsl:variable>
				
			<b><xsl:value-of select="format-number(sum(exsl:node-set($subTotals)/sum), '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="/shop/shop_currency/name"/></b>
			</td>
			
			<xsl:if test="/cart/shop/warehouses/node()">
			<td><xsl:text> </xsl:text></td>
			</xsl:if>
			
		<td><xsl:text> </xsl:text></td>
		<td><xsl:text> </xsl:text></td>
		</tr>
	</xsl:template>
	
	<!-- Шаблон для скидки от суммы заказа -->
	<xsl:template match="shop_purchase_discount">
		<tr class="shop_cart_table">
			<td>
				<xsl:value-of select="name"/>
			</td>
			<td></td>
			<td></td>
			<td>
				<!-- Сумма -->
				<xsl:value-of select="format-number(discount_amount * -1, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name" disable-output-escaping="yes"/>
			</td>
			<xsl:if test="count(/shop/shop_warehouse)">
				<td></td>
			</xsl:if>
			<td></td>
			<td></td>
		</tr>
	</xsl:template>
	
	<!-- Шаблон для товара в корзине -->
	<xsl:template match="shop_cart">
		<tr class="shop_cart_table">
			
			<td style="font-size: 120%;">
				<a href="{shop_item/url}">
					<xsl:value-of disable-output-escaping="yes" select="shop_item/name"/>
				</a>
				<div style="clear: both"></div>
			</td>
			<td style="white-space: nowrap">
				<input type="text" size="3" name="quantity_{shop_item/@id}" id="quantity_{shop_item/@id}" value="{quantity}" class="input_count_items"/>
				
				<!--
				<img src="/images/map_intocart.gif" width="12" height="21" border="0" usemap="#mapInToCart{shop_item/@id}" style="margin: 0 0 -6px 1px;"/>
				<map name="mapInToCart{shop_item/@id}">
					<area shape="rect" coords="0,0,12,10"  onclick="set_count_mod('count_{shop_item/@id}', 1);" nohref="nohref" />
					<area shape="rect" coords="0,11,12,21" onclick="set_count_mod('count_{shop_item/@id}', -1);" nohref="nohref" />
				</map>-->
				
			</td>
			<td style="white-space: nowrap">
				<!-- Цена -->
				<xsl:value-of disable-output-escaping="yes" select="format-number(shop_item/price, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of select="shop_item/currency" disable-output-escaping="yes"/></td>
			
			<td style="white-space: nowrap">
				<!-- Сумма -->
				<xsl:value-of disable-output-escaping="yes" select="format-number(shop_item/price * quantity, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="shop_item/currency"/></td>
			
			<xsl:if test="count(/shop/shop_warehouse)">
				<td width="100">
					<xsl:choose>
						<xsl:when test="sum(shop_item/shop_warehouse_item/count) > 0">
							<select name="warehouse_{shop_item/@id}">
								<xsl:apply-templates select="shop_item/shop_warehouse_item"/>
							</select>
						</xsl:when>
						<xsl:otherwise>&#8212;</xsl:otherwise>
					</xsl:choose>
				</td>
			</xsl:if>
			<td align="center">
				<!-- Отложенные товары -->
				<input type="checkbox" name="postpone_{shop_item/@id}">
					<xsl:if test="postpone = 1">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
				</input>
			</td>
			
		<td align="center"><a href="?delete={shop_item/@id}" onclick="return confirm('Вы уверены, что хотите удалить?')" title="Удалить товар из корзины" alt="Удалить товар из корзины">Удалить</a></td>
		</tr>
	</xsl:template>
	
	<!-- option для склада -->
	<xsl:template match="shop_warehouse_item">
		<!-- Если есть остаток на складе -->
		<xsl:if test="count != 0">
			<xsl:variable name="shop_warehouse_id" select="shop_warehouse_id" />
			<option value="{$shop_warehouse_id}">
				<xsl:if test="../../shop_warehouse_id = $shop_warehouse_id">
					<xsl:attribute name="selected">selected</xsl:attribute>
				</xsl:if>
				
				<xsl:value-of select="/shop/shop_warehouse[@id=$shop_warehouse_id]/name"/> (<xsl:value-of select="count"/>)
			</option>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>