<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/shop"/>
	</xsl:template>
	
	<!-- Шаблон для магазина -->
	<xsl:template match="/shop">
		
		<!-- Получаем ID родительской группы и записываем в переменную $parent_group_id -->
		<xsl:variable name="parent_group_id" select="@current_group_id"/>
		
		<!-- Если в находимся корне - выводим название магазина -->
		<h1>
			<xsl:value-of select="name"/>
		</h1>
		
		<xsl:variable name="count">1</xsl:variable>
		
		<!-- Выводим группы магазина -->
		<!--
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<xsl:apply-templates select="//group[@parent=$parent_group_id]" />
			</tr>
		</table>
		-->
		
		<form method="get" action="./">
			
			
			<!-- Выводим товары магазина -->
			<xsl:if test="count(item) &gt; 0">
				<table width="100%" cellspacing="5" cellpadding="2" border="0">
					<tr>
						<td height="25px" style="border-bottom: 1px solid #dadada;">
							<b>Изображение</b>
						</td>
						<td style="border-bottom: 1px solid #dadada;">
						<b>Наименование</b><xsl:text> </xsl:text></td>
						<td style="border-bottom: 1px solid #dadada;">
						<b>Цена</b><xsl:text> </xsl:text></td>
					</tr>
					<xsl:apply-templates select="item"/>
				</table>
			</xsl:if>
		</form>
	</xsl:template>
	
	<!-- Шаблон для списка товаров для сравнения -->
	<xsl:template match="compare_items/compare_item">
		<xsl:variable name="var_compare_id" select="."/>
		<tr>
			<td>
				<a href="{/shop/path}{compare_item_fullpath}{compare_item_path}">
					<xsl:value-of disable-output-escaping="yes" select="compare_item_name"/>
				</a>
			</td>
			<td>
				<input type="checkbox" name="del_compare_id_{compare_item_id}" id="id_del_compare_id_{compare_item_id}"/>
				<label for="id_del_compare_id_{compare_item_id}">Да</label>
			</td>
		</tr>
	</xsl:template>
	
	<!-- Шаблон для фильтра производителей -->
	<xsl:template match="producerslist/producer">
		<option value="{@id}">
			<xsl:if test="@id = /shop/producer_id">
				<xsl:attribute name="selected">
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="name"/>
		</option>
	</xsl:template>
	
	<!-- Шаблон для фильтра продавцов -->
	<xsl:template match="sallers/saller">
		<option value="{@id}">
			<xsl:if test="@id = /shop/saller_id">
				<xsl:attribute name="selected">
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="sallers_name"/>
		</option>
	</xsl:template>
	
	<!-- Шаблон для фильра по дополнительным свойствам -->
	<xsl:template match="properties_for_group/property">
		<td>
			<xsl:value-of disable-output-escaping="yes" select="property_name"/><xsl:text> </xsl:text>
			<xsl:if test="property_show_kind = 1">
				<!-- Отображаем поле ввода -->
				<xsl:variable name="nodename">property_id_<xsl:value-of select="@id"/></xsl:variable>
				<br/>
				<input type="text" name="property_id_{@id}">
					<xsl:if test="/shop/*[name()=$nodename] != ''">
						<xsl:attribute name="value">
							<xsl:value-of disable-output-escaping="yes" select="/shop/*[name()=$nodename]"/>
						</xsl:attribute>
					</xsl:if>
				</input>
			</xsl:if>
			<xsl:if test="property_show_kind = 2">
				<!-- Отображаем список -->
				<br/>
				<select name="property_id_{@id}">
					<option value="0">...</option>-->
					<xsl:apply-templates select="list_items/list_item"/>
				</select>
			</xsl:if>
			<xsl:if test="property_show_kind = 3">
				<!-- Отображаем переключатели -->
				<br/>
				<input type="radio" name="property_id_{@id}" value="0" id="id_prop_radio_{@id}_0"></input>
				<label for="id_prop_radio_{@id}_0">Любой вариант</label>
				<xsl:apply-templates select="list_items/list_item"/>
			</xsl:if>
			<xsl:if test=" property_show_kind = 4">
				<!-- Отображаем флажки -->
				<xsl:apply-templates select="list_items/list_item"/>
			</xsl:if>
		</td>
		<xsl:if test="position() mod 6 = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/tr&gt;
				&lt;tr valign="top"&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="list_items/list_item">
		<xsl:if test="../../property_show_kind = 2">
			<!-- Отображаем список -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/></xsl:variable>
			<option value="{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="selected">
					</xsl:attribute>
				</xsl:if>
				<xsl:value-of disable-output-escaping="yes" select="list_item_value"/>
			</option>
		</xsl:if>
		<xsl:if test="../../property_show_kind = 3">
			<!-- Отображаем переключатели -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/></xsl:variable>
			<br/>
			<input type="radio" name="property_id_{../../@id}" value="{@id}" id="id_property_id_{../../@id}_{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="checked">
					</xsl:attribute>
				</xsl:if>
				<label for="id_property_id_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="list_item_value"/>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="../../property_show_kind = 4">
			<!-- Отображаем флажки -->
			<xsl:variable name="nodename">property_id_<xsl:value-of select="../../@id"/>_item_id_<xsl:value-of select="@id"/></xsl:variable>
			<br/>
			<input type="checkbox" name="property_id_{../../@id}_item_id_{@id}" id="id_property_id_{../../@id}_{@id}">
				<xsl:if test="/shop/*[name()=$nodename] = @id">
					<xsl:attribute name="checked">
					</xsl:attribute>
				</xsl:if>
				<label for="id_property_id_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="list_item_value"/>
				</label>
			</input>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для групп товара -->
	<xsl:template match="group">
		<td valign="top" width="33%">
			<b>
				<a href="{/shop/path}{fullpath}">
					<xsl:value-of select="name"/>
				</a>
			</b>
			
			<!-- Количество элементов в группе -->
			<xsl:text> </xsl:text><span style="color: #aaaaaa">(<xsl:value-of disable-output-escaping="yes" select="count_all_items"/>)</span>
			
			
			<br/>
			<!-- Если есть изображение для группы - выводим его -->
			<xsl:if test="small_image != ''">
				<img src="{small_image}" align="left" class="image" />
			</xsl:if>
			<!-- Выводим описание -->
			<xsl:value-of disable-output-escaping="yes" select="description"/>
		</td>
		
		<!-- На строку - не более 3-х пунктов, если уже 3 выведено - начинаем новую строку -->
		<xsl:if test="position() mod 3 = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/tr&gt;
				&lt;tr&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для товара -->
	<xsl:template match="item">
		
		<!-- Определяем цвет фона -->
		<xsl:variable name="background_color">
			<xsl:choose>
				<xsl:when test="(position() + 1) mod 2 &gt; 0">#f7f7f7</xsl:when>
				<xsl:otherwise>#ffffff</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		
		<tr style="background-color: {$background_color}; padding: 5px;">
			<td width="100">
				<!-- Изображение для товара, если есть -->
				<xsl:if test="small_image!=''">
					<a href="{/shop/path}{fullpath}{path}/">
						<img src="{small_image}" class="image" />
					</a>
				</xsl:if>
			</td>
			<td>
				<!-- Название товара -->
				<a href="{/shop/path}{fullpath}{path}/">
					<xsl:value-of select="name"/>
				</a>
			</td>
			<td width="100">
				<!-- Ссылку на добавление в корзины выводим, если:
				type = 0 - простой тип товара
				type = 1 - электронный товар, при этом остаток на складе больше 0 или -1,
				что означает неограниченное количество -->
				<xsl:if test="type = 0 or (type = 1 and (eitem_count > 0 or eitem_count = -1))">
					<a href="{/shop/path}cart/?add={@id}" onclick="return $.addIntoCart('{/shop/url}cart/', {@id}, document.getElementById('count_{@id}').value)">
						<img alt="В корзину" title="В корзину" src="/hostcmsfiles/images/cart.gif"/>
					</a><xsl:text> </xsl:text>
				</xsl:if>
				
				<!-- Цена товара -->
				<strong>
					<xsl:value-of disable-output-escaping="yes" select="price_discount"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/>
				</strong>
				<!-- Если цена со скидкой - выводим ее -->
				<xsl:if test="price_tax != price_discount">
					<br/>
					<font color="gray">
						<strike>
							<xsl:value-of disable-output-escaping="yes" select="price_tax"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/></strike>
					</font>
				</xsl:if>
			</td>
		</tr>
		
		<!-- На строку - не более 3-х пунктов, если уже 3 выведено - начинаем новую строку -->
		<!--
		<xsl:if test="position() mod 3 = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/tr&gt;
				&lt;tr&gt;
			</xsl:text>
		</xsl:if>
		-->
	</xsl:template>
	
	<!-- Шаблон для модификаций -->
	<xsl:template match="modifications/item">
		<tr>
			<td>
				<!-- Название модификации -->
				<a href="{/shop/path}{fullpath}{path}/">
					<xsl:value-of select="name"/>
				</a>
			</td>
			<td>
				<!-- Цена модификации -->
				<xsl:value-of disable-output-escaping="yes" select="price_discount"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/>
			</td>
		</tr>
	</xsl:template>
	
	<!-- Шаблон для скидки -->
	<xsl:template match="discount">
		<br/>
		<xsl:value-of select="name"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="value"/>%</xsl:template>
	
	<!-- ======================================================== -->
	<!-- Шаблон выводит рекурсивно ссылки на группы инф. элемента -->
	<!-- ======================================================== -->
	
	<xsl:template match="group" mode="goup_path">
		<xsl:param name="parent_id" select="@parent"/>
		
		<!-- Получаем ID родительской группы и записываем в переменную $parent_group_id -->
		<xsl:param name="parent_group_id" select="/shop/@current_group_id"/>
		
		<xsl:apply-templates select="//group[@id=$parent_id]" mode="goup_path"/>
		
		<xsl:if test="@parent=0">
			<a href="{/shop/path}">
				<xsl:value-of select="/shop/name"/>
			</a>
		</xsl:if>
		
		<span><xsl:text> → </xsl:text></span>
		
		<!-- ============================================================================================== -->
		<!-- Если ID группы, для которой выводим список элементов, равен ID текущей группы - выводим жирным -->
		<!-- ============================================================================================== -->
		<xsl:if test="$parent_group_id=@id">
			<a href="{/shop/path}{fullpath}">
				<b>
					<xsl:value-of select="name"/>
				</b>
			</a>
		</xsl:if>
		
		<!-- ============================ -->
		<!-- Иначе выводим обычную ссылку -->
		<!-- ============================ -->
		<xsl:if test="$parent_group_id!=@id">
			<a href="{/shop/path}{fullpath}">
				<xsl:value-of select="name"/>
			</a>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>