<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<!-- МагазинАдресДоставки -->
	
	<xsl:template match="/shop">
		<ul class="shop_navigation">
		<li class="shop_navigation_current"><span>Адрес доставки</span>→</li>
		<li><span>Способ доставки</span>→</li>
		<li><span>Форма оплаты</span>→</li>
		<li><span>Данные доставки</span></li>
		</ul>
		
		<form method="POST">
			<h1>Адрес доставки</h1>
			
			<div class="comment shop_address">
				
				<div class="row">
					<div class="caption">Страна:</div>
					<div class="field">
						<select id="shop_country_id" name="shop_country_id" onchange="$.loadLocations('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
							<xsl:apply-templates select="shop_country" />
						</select>
						<span class="redSup"> *</span>
					</div>
				</div>
				
				<div class="row">
					<div class="caption">Область:</div>
					<div class="field">
						<select name="shop_country_location_id" id="shop_country_location_id" onchange="$.loadCities('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
						</select>
						<span class="redSup"> *</span>
					</div>
				</div>
				<div class="row">
					<div class="caption">Город:</div>
					<div class="field">
						<select name="shop_country_location_city_id" id="shop_country_location_city_id" onchange="$.loadCityAreas('{/shop/url}cart/', $(this).val())">
							<option value="0">…</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="caption">Район города:</div>
					<div class="field">
						<select name="shop_country_location_city_area_id" id="shop_country_location_city_area_id">
							<option value="0">…</option>
						</select>
					</div>
				</div>
				<div class="row">
					<div class="caption">Индекс:</div>
					<div class="field">
						<input type="text" size="15" class="width1" name="postcode" value="{/shop/siteuser/postcode}" />
					</div>
				</div>
				<div class="row">
					<div class="caption">Улица, дом, квартира:<br/>
					(город, если не выбраны)</div>
					<div class="field">
						<input type="text" size="30" name="address" value="{/shop/siteuser/address}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">Фамилия, Имя, Отчество:</div>
					<div class="field">
						<input type="text" size="15" class="width1" name="surname" value="{/shop/siteuser/surname}" />
						<input type="text" size="15" class="width1" name="name" value="{/shop/siteuser/name}" />
						<input type="text" size="15" class="width1" name="patronymic" value="{/shop/siteuser/patronymic}" />
					</div>
				</div>
				<div class="row">
					<div class="caption">Компания:</div>
					<div class="field">
						<input type="text" size="30" name="company" value="{/shop/siteuser/company}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">Телефон:</div>
					<div class="field">
						<input type="text" size="30" name="phone" value="{/shop/siteuser/phone}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">Факс:</div>
					<div class="field">
						<input type="text" size="30" name="fax" value="{/shop/siteuser/fax}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">E-mail:</div>
					<div class="field">
						<input type="text" size="30" name="email" value="{/shop/siteuser/email}" class="width2" />
					</div>
				</div>
				<div class="row">
					<div class="caption">Комментарий:</div>
					<div class="field">
						<textarea rows="3" name="description" class="width2"></textarea>
					</div>
				</div>
				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<input name="step" value="2" type="hidden" />
						<input value="Далее →" type="submit" class="button" />
					</div>
				</div>
			</div>
		</form>
		
		<SCRIPT type="text/javascript" language="JavaScript">
			$(function() {
			$.loadLocations('<xsl:value-of select="/shop/url" />cart/', $('#shop_country_id').val());
			});
		</SCRIPT>
	</xsl:template>
	
	<xsl:template match="shop_country">
		<option value="{@id}">
			<xsl:if test="/shop/shop_country_id = @id">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name" />
		</option>
	</xsl:template>
</xsl:stylesheet>