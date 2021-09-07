<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<xsl:template match="/shop ">
		<!-- Строка шага заказа -->
		<ul class="shop_navigation gray">
		<li class="shop_navigation_current"><span>Адрес доставки</span>→</li>
		<li><span>Способ доставки</span>→</li>
		<li><span>Форма оплаты</span>→</li>
		<li><span>Данные доставки</span></li>
		</ul>
		<form method="POST">
			<h1>Адрес доставки</h1>
			<p>
				<a href="{url}cart/">Корзина</a>
			</p>
			<table>
				<tr>
					<td>Страна:</td>
					<td>
						<select id="shop_country_id" style="width: 380px;" name="shop_country_id" onchange="$.loadLocations('{/shop/url}cart/', $(this).val())">
							<option value="0">..</option>
							<xsl:apply-templates select="shop_country"/>
						</select>
						<span class="red_star" style="position: relative; top: 4px;"> *</span>
					</td>
				</tr>
				
				<tr>
					<td>Область:</td>
					<td>
						<xsl:variable name="country_id" select="/locations//country[@select = 1]/@id" />
						
						<select name="shop_country_location_id" style="width: 380px;" id="shop_country_location_id" onchange="$.loadCities('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
							<!--<xsl:apply-templates select="location[@parent = $country_id]"/>-->
						</select>
						<span class="red_star" style="position: relative; top: 4px;"> *</span>
					</td>
				</tr>
				<tr>
					<td>Город:</td>
					<td>
						<select name="shop_country_location_city_id" style="width: 380px;" id="shop_country_location_city_id" onchange="$.loadCityAreas('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
							<!--<xsl:apply-templates select="city[@parent = location[@parent = $country_id]]"/>-->
						</select>
					</td>
				</tr>
				<tr>
					<td>Район города:</td>
					<td>
						<select name="shop_country_location_city_area_id" style="width: 380px;" id="shop_country_location_city_area_id">
							<option value="0">…</option>
						</select>
					</td>
				</tr>
				<tr>
					<td style="vertical-align: middle;">Индекс:</td>
					<td>
						<input type="text" size="5" class="large_input" style="width: 90px;" name="postcode" value="{/shop/siteuser/postcode}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;">Улица, дом, квартира:<br/>
					(город, район, если не выбраны)</td>
					<td>
						<input type="text" size="30" class="large_input" style="width: 390px;" name="address" value="{/shop/siteuser/address}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;">Фамилия, Имя, Отчество:</td>
					<td>
						<input type="text" size="30" class="large_input" style="width: 124px; margin-right: 5px;" name="surname" value="{/shop/siteuser/surname}"/>
						<input type="text" size="30" class="large_input" style="width: 124px; margin-right: 5px;" name="name" value="{/shop/siteuser/name}"/>
						<input type="text" size="30" class="large_input" style="width: 124px; margin-right: 0px;" name="patronymic" value="{/shop/siteuser/patronymic}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;">Компания:</td>
					<td>
						<input type="text" size="30" class="large_input" style="width: 390px;" name="company" value="{/shop/siteuser/company}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;">Телефон:</td>
					<td>
						<input type="text" size="30" class="large_input" style="width: 390px;" name="phone" value="{/shop/siteuser/phone}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;">Факс:</td>
					<td>
						<input type="text" size="30" class="large_input" style="width: 390px;" name="fax" value="{/shop/siteuser/fax}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;">E-mail:</td>
					<td>
						<input type="text" size="30" class="large_input" style="width: 390px;" name="email" value="{/shop/siteuser/email}"/>
					</td>
				</tr>
				<tr>
					<td  style="vertical-align: middle;" >Комментарий к заказу:</td>
					<td>
						<textarea rows="2" class="large_input" style="width: 390px;" name="description"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<div class="gray_button">
							<div>
								<input name="step" value="2" type="hidden" />
								<input value="Далее →" type="submit" class="cart_button" />
							</div>
						</div>
					</td>
				</tr>
			</table>
		</form>
		
		<!-- Заполняем все дочерние элементы страны -->
		<SCRIPT type="text/javascript" language="JavaScript">
			$(function() {
			$.loadLocations('<xsl:value-of select="/shop/url" />cart/', $('#shop_country_id').val());
			});
		</SCRIPT>
		
	</xsl:template>
	
	<!-- Шаблон заполняет options для стран -->
	<xsl:template match="shop_country">
		<option value="{@id}">
			<xsl:if test="/shop/shop_country_id = @id">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>
</xsl:stylesheet>