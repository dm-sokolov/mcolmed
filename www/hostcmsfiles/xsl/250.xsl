<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/shop"/>
	</xsl:template>
	
	<xsl:variable name="n" select="number(3)"/>
	
	<!-- Шаблон для магазина -->
	<xsl:template match="/shop">
		
		<!-- Получаем ID родительской группы и записываем в переменную $parent_group_id -->
		<xsl:variable name="group" select="group"/>
		
		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop">
					<xsl:value-of select="name"/>
				</h1>
				
				<!-- Описание выводится при отсутствии фильтрации по тэгам -->
				<xsl:if test="count(tag) = 0 and page = 0 and description != ''">
					<div hostcms:id="{@id}" hostcms:field="description" hostcms:entity="shop" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<h1 hostcms:id="{$group}" hostcms:field="name" hostcms:entity="shop_group">
					<xsl:value-of select=".//shop_group[@id=$group]/name"/>
				</h1>
				
				<!-- Описание выводим только на первой странице -->
				<xsl:if test="page = 0 and .//shop_group[@id=$group]/description != ''">
					<div hostcms:id="{$group}" hostcms:field="description" hostcms:entity="shop_group" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select=".//shop_group[@id=$group]/description"/></div>
				</xsl:if>
				
				<!-- Путь к группе -->
				<!--
				<p>
					<xsl:apply-templates select=".//shop_group[@id=$group]" mode="breadCrumbs"/>
				</p>
				-->
			</xsl:otherwise>
		</xsl:choose>
		
		
		<!-- Обработка выбранных тэгов -->
		<xsl:if test="count(tag)">
		<p class="h2">Метка — <strong><xsl:value-of select="tag/name"/></strong>.</p>
			<xsl:if test="tag/description != ''">
				<p><xsl:value-of select="tag/description" disable-output-escaping="yes" /></p>
			</xsl:if>
		</xsl:if>
		
		<xsl:variable name="count">1</xsl:variable>
		
		<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
		<xsl:if test="count(tag) = 0 and count(shop_producer) = 0 and count(//shop_group[parent_id=$group]) &gt; 0">
			<table width="100%" border="0" cellpadding="3" cellspacing="0">
				<tr>
					<td valign="top" align="center">
						<xsl:apply-templates select=".//shop_group[parent_id=$group]"/>
					</td>
				</tr>
			</table>
		</xsl:if>
		
		<!-- дополнение пути для action, если выбрана метка -->
		<xsl:if test="count(shop_item) &gt; 0 or /shop/filter = 1">
			<!-- дополнение пути для action, если выбрана метка -->
		<xsl:variable name="form_tag_url"><xsl:if test="count(tag) = 1">tag/<xsl:value-of select="tag/urlencode"/>/</xsl:if></xsl:variable>
			
			<xsl:variable name="path"><xsl:choose>
					<xsl:when test="/shop//shop_group[@id=$group]/node()"><xsl:value-of select="/shop//shop_group[@id=$group]/url"/></xsl:when>
					<xsl:otherwise><xsl:value-of select="/shop/url"/></xsl:otherwise>
			</xsl:choose></xsl:variable>
			
			<form method="get" action="{$path}{$form_tag_url}">
				<div class="shop_block">
					<div>
						Цена от:&#xA0;
						<input name="price_from" size="5" type="text">
							<xsl:if test="/shop/price_from != 0">
								<xsl:attribute name="value">
									<xsl:value-of disable-output-escaping="yes" select="/shop/price_from"/>
								</xsl:attribute>
							</xsl:if>
						</input>&#xA0;
						
						до:&#xA0;
						<input name="price_to" size="5" type="text">
							<xsl:if test="/shop/price_to != 0">
								<xsl:attribute name="value">
									<xsl:value-of disable-output-escaping="yes" select="/shop/price_to"/>
								</xsl:attribute>
							</xsl:if>
						</input>&#xA0;&#xA0;&#xA0;
												
						<select name="sorting" onchange="$(this).parents('form:first').submit()">
							<option disabled="disabled">Сортировать</option>
							<option value="1">
								<xsl:if test="/shop/sorting = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								По цене (сначала дешевые)
							</option>
							<option value="2">
								<xsl:if test="/shop/sorting = 2"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								По цене (сначала дорогие)
							</option>
							<option value="3">
								<xsl:if test="/shop/sorting = 3"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								По названию
							</option>
						</select>
						<!--
						<span style="white-space: nowrap">Товаров на странице:</span>&#xA0;
						<select name="on_page">
							<option value="0">&#x2026;</option>
							<xsl:call-template name="for_on_page">
								<xsl:with-param name="i" select="10"/>
								<xsl:with-param name="n" select="50"/>
							</xsl:call-template>
						</select>&#xA0;
						-->
					</div>
					
					<xsl:if test="count(shop_item_properties//property[filter != 0 and (type = 0 or type = 1 or type = 3 or type = 7)])">
						<p>
							<b>Фильтр по дополнительным свойствам товара:</b>
						</p>
						<table cellpadding="10px" cellspacing="0">
							<tr valign="top">
								<xsl:apply-templates select="shop_item_properties//property[filter != 0 and (type = 0 or type = 1 or type = 3 or type = 7)]" mode="propertyList"/>
							</tr>
						</table>
					</xsl:if>
					<div class="nofloat" style="margin-left: 40%">
						<div class="gray_button">
							<div>
								<input name="filter" value="Применить" type="submit"/>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Таблица с элементами для сравнения -->
				<xsl:if test="count(/shop/compare_items/compare_item) &gt; 0">
					<table cellpadding="5px" cellspacing="0" border="0">
						<tr>
							<td>
								<input type="checkbox" onclick="SelectAllItemsByPrefix(this.checked, 'del_compare_id_')" />
							</td>
							<td>
								<b>Сравниваемые элементы</b>
							</td>
						</tr>
						<xsl:apply-templates select="compare_items/compare_item"/>
					</table>
				</xsl:if>
												
				<!-- Сортировка товаров -->
				<xsl:if test="0">
				<div class="shop_block">
					<!-- Определяем ссылку с параметрами фильтра -->
		<xsl:variable name="filter"><xsl:if test="/shop/filter/node()">?filter=1&amp;sorting=<xsl:value-of select="/shop/sorting"/>&amp;price_from=<xsl:value-of select="/shop/price_from"/>&amp;price_to=<xsl:value-of select="/shop/price_to"/><xsl:for-each select="/shop/*"><xsl:if test="starts-with(name(), 'property_')">&amp;<xsl:value-of select="name()"/>=<xsl:value-of select="."/></xsl:if></xsl:for-each></xsl:if></xsl:variable>
					
					
					<!-- Определяем первый символ вопрос или амперсанд -->
					<xsl:variable name="first_symbol">
						<xsl:choose>
							<xsl:when test="$filter != ''">&amp;</xsl:when>
							<xsl:otherwise>?</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<div style="float: left;">
						Сортировать по алфавиту
					</div>
					<xsl:choose>
						<xsl:when test="/shop/sort_by_field = 1 and /shop/order_direction = 'ASC'">
							<div class="arrow_up">
								<img src="/hostcmsfiles/images/arrow_up.png" style="float: left; filter: alpha(opacity=0); margin: 0px 0px -4px 0px" alt="по возрастанию"/>
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div class="arrow_up_gray">
								<a href="{$filter}{$first_symbol}sort_by_field=1&amp;order_direction=1" class="without_decor">
									<img src="/hostcmsfiles/images/arrow_up_gray.png" alt="по возрастанию"/>
								</a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="/shop/sort_by_field = 1 and /shop/order_direction = 'DESC'">
							<div class="arrow_down">
								<img src="/hostcmsfiles/images/arrow_down.png" alt="по убыванию"/>
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div class="arrow_down_gray">
								<a href="{$filter}{$first_symbol}sort_by_field=1&amp;order_direction=2" class="without_decor">
									<img src="/hostcmsfiles/images/arrow_down_gray.png" alt="по убыванию"/>
								</a>
							</div>
						</xsl:otherwise>
				</xsl:choose><div style="float: left;">,&#xA0;по цене</div>
					
					<xsl:choose>
						<xsl:when test="/shop/sort_by_field = 2 and /shop/order_direction = 'ASC'">
							<div class="arrow_up">
								<img src="/hostcmsfiles/images/arrow_up.png" alt="по возрастанию"/>
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div class="arrow_up_gray">
								<a href="{$filter}{$first_symbol}sort_by_field=2&amp;order_direction=1" class="without_decor">
									<img src="/hostcmsfiles/images/arrow_up_gray.png" alt="по возрастанию"/></a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="/shop/sort_by_field = 2 and /shop/order_direction = 'DESC'">
							<div class="arrow_down">
								<img src="/hostcmsfiles/images/arrow_down.png" style="filter: alpha(opacity=0); margin: 0px 0px -4px 0px" alt="по убыванию"/>
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div class="arrow_down_gray">
								<a href="{$filter}{$first_symbol}sort_by_field=2&amp;order_direction=2" class="without_decor">
									<img src="/hostcmsfiles/images/arrow_down_gray.png" alt="по убыванию"/>
								</a>
							</div>
						</xsl:otherwise>
					</xsl:choose>
					<div class="clearing"></div>
				</div>
				</xsl:if>
				
				<!-- Определяем ссылку с параметрами фильтра -->
				<xsl:variable name="filter">
					<xsl:choose>
						<xsl:when test="/shop/apply_filter/node()">?action=apply_filter&amp;producer_id=<xsl:value-of select="/shop/producer_id"/>&amp;saller_id=<xsl:value-of select="/shop/saller_id"/>&amp;price_from=<xsl:value-of select="/shop/price_from"/>&amp;price_to=<xsl:value-of select="/shop/price_to"/>&amp;on_page=<xsl:value-of select="/shop/on_page"/>
							<xsl:if test="/shop/property_xml/node()">
								<!-- GET для доп. свойств -->
								<xsl:value-of select="/shop/property_xml"/>
							</xsl:if>
						</xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<!-- Определяем первый символ вопрос или амперсанд -->
				<xsl:variable name="first_symbol">
					<xsl:choose>
						<xsl:when test="$filter != ''">&amp;</xsl:when>
						<xsl:otherwise>?</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<!-- Отображаем товары -->
				<div class="column p_l">
					<xsl:apply-templates select="shop_item" />
				</div>
				<!--
				<xsl:if test="count_items &gt; 0">
					<div class="nofloat" style="height: 27px; padding: 0 0 10px 0;">
						<div class="gray_button">
							<div>
								<input name="add_compare" value="Добавить для сравнения" type="submit" />
							</div>
						</div>
					</div>
				</xsl:if>
				-->
				<xsl:if test="count(/shop/group[@id = /shop/@current_group_id]/propertys/property) > 0">
					<div style="margin: 10px 0px;">
						<h2>Атрибуты группы товаров</h2>
						
						<xsl:if test="count(property[@dir_id = 0])">
							<table border="0">
								<xsl:apply-templates select="property[@dir_id = 0]"/>
							</table>
						</xsl:if>
						
						<xsl:apply-templates select="/shop/properties_groups_dir"/>
					</div>
				</xsl:if>
				
				<xsl:if test="total &gt; 0 and limit &gt; 0">

					<xsl:variable name="count_pages" select="ceiling(total div limit)"/>

					<xsl:variable name="visible_pages" select="5"/>

					<xsl:variable name="real_visible_pages"><xsl:choose>
						<xsl:when test="$count_pages &lt; $visible_pages"><xsl:value-of select="$count_pages"/></xsl:when>
						<xsl:otherwise><xsl:value-of select="$visible_pages"/></xsl:otherwise>
					</xsl:choose></xsl:variable>

					<!-- Считаем количество выводимых ссылок перед текущим элементом -->
					<xsl:variable name="pre_count_page"><xsl:choose>
						<xsl:when test="page - (floor($real_visible_pages div 2)) &lt; 0">
							<xsl:value-of select="page"/>
						</xsl:when>
						<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - ($count_pages - page - 1) - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="round($real_visible_pages div 2) = $real_visible_pages div 2">
									<xsl:value-of select="floor($real_visible_pages div 2) - 1"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="floor($real_visible_pages div 2)"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<!-- Считаем количество выводимых ссылок после текущего элемента -->
					<xsl:variable name="post_count_page"><xsl:choose>
						<xsl:when test="0 &gt; page - (floor($real_visible_pages div 2) - 1)">
							<xsl:value-of select="$real_visible_pages - page - 1"/>
						</xsl:when>
						<xsl:when test="($count_pages - page - 1) &lt; floor($real_visible_pages div 2)">
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$real_visible_pages - $pre_count_page - 1"/>
						</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<xsl:variable name="i"><xsl:choose>
						<xsl:when test="page + 1 = $count_pages"><xsl:value-of select="page - $real_visible_pages + 1"/></xsl:when>
						<xsl:when test="page - $pre_count_page &gt; 0"><xsl:value-of select="page - $pre_count_page"/></xsl:when>
						<xsl:otherwise>0</xsl:otherwise>
					</xsl:choose></xsl:variable>

					<p>
						<xsl:call-template name="for">
							<xsl:with-param name="limit" select="limit"/>
							<xsl:with-param name="page" select="page"/>
							<xsl:with-param name="items_count" select="total"/>
							<xsl:with-param name="i" select="$i"/>
							<xsl:with-param name="post_count_page" select="$post_count_page"/>
							<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
							<xsl:with-param name="visible_pages" select="$real_visible_pages"/>
						</xsl:call-template>
					</p>
					<div style="clear: both"></div>
				</xsl:if>				
			</form>
		</xsl:if>
	</xsl:template>
	
	<!-- Вывод раздела для свойств группы товаров -->
	<xsl:template match="properties_groups_dir">
		
	<p><b><xsl:value-of select="shop_properties_groups_dir_name"/></b></p>
		
		<xsl:variable name="dir_id" select="@id"/>
		
		<xsl:if test="count(/shop/group[@id = /shop/@current_group_id]/propertys/property)">
			<table border="0">
				<xsl:apply-templates select="/shop/group[@id = /shop/@current_group_id]/propertys/property[@parent_id = $dir_id]"/>
			</table>
		</xsl:if>
		
		<xsl:if test="count(properties_groups_dir)">
			<blockquote>
				<xsl:apply-templates select="properties_groups_dir"/>
			</blockquote>
		</xsl:if>
	</xsl:template>
	
	<!-- Вывод строки со значением свойства -->
	<xsl:template match="property">
		<tr>
			<td style="padding: 5px" bgcolor="#eeeeee">
				<b><xsl:value-of select="name"/></b>
			</td>
			<td style="padding: 5px" bgcolor="#eeeeee">
				<xsl:choose>
					<xsl:when test="type = 1">
						<a href="{file_path}">Скачать файл</a>
					</xsl:when>
					<xsl:when test="type = 7">
						<xsl:choose>
							<xsl:when test="value = 1">
								<input type="checkbox" checked="" disabled="" />
							</xsl:when>
							<xsl:otherwise>
								<input type="checkbox" disabled="" />
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of disable-output-escaping="yes" select="value"/>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
	</xsl:template>
	
	<!-- Шаблон для списка товаров для сравнения -->
	<xsl:template match="compare_items/compare_item">
		<xsl:variable name="var_compare_id" select="."/>
		<tr>
			<td>
				<input type="checkbox" name="del_compare_id_{compare_item_id}" id="id_del_compare_id_{compare_item_id}"/>
			</td>
			<td>
				<a href="{/shop/path}{compare_item_fullpath}{compare_item_path}/">
					<xsl:value-of disable-output-escaping="yes" select="compare_item_name"/>
				</a>
			</td>
		</tr>
	</xsl:template>
	
	
	<!-- Шаблон для фильтра по дополнительным свойствам -->
	<xsl:template match="properties_for_group/property">
		
		<xsl:variable name="nodename">property_id_<xsl:value-of select="@id"/></xsl:variable>
		
		<xsl:variable name="nodename_from">property_id_<xsl:value-of select="@id"/>_from</xsl:variable>
		<xsl:variable name="nodename_to">property_id_<xsl:value-of select="@id"/>_to</xsl:variable>
		
		
		<td>
			<xsl:value-of disable-output-escaping="yes" select="property_name"/>&#xA0;
			<xsl:if test="property_show_kind = 1">
				<!-- Отображаем поле ввода -->
				<br/>
				<input type="text" name="property_id_{@id}">
					<xsl:if test="/shop/*[name()=$nodename] != ''">
						<xsl:attribute name="value">
							<xsl:value-of select="/shop/*[name()=$nodename]"/>
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
			
			<xsl:if test=" property_show_kind = 5">
				<!-- Отображаем флажок -->
				<br/>
				<input type="checkbox" name="property_id_{@id}" id="property_id_{@id}" style="padding-top:4px">
					<xsl:if test="/shop/*[name()=$nodename] != ''">
						<xsl:attribute name="checked">
							<xsl:value-of select="/shop/*[name()=$nodename]"/>
						</xsl:attribute>
					</xsl:if>
				</input>
				<label for="property_id_{@id}">Да</label>
			</xsl:if>
			
			<xsl:if test=" property_show_kind = 6">
				<!-- Отображение полей "От.. До.." -->
				<br/>
				от: <input type="text" name="property_id_{@id}_from" size="5" value="{/shop/*[name()=$nodename_from]}"/> до: <input type="text" name="property_id_{@id}_to" size="5" value="{/shop/*[name()=$nodename_to]}"/>
			</xsl:if>
			
			<xsl:if test="property_show_kind = 7">
				<!-- Отображаем список  с множественным выбором-->
				<br/>
				<select name="property_id_{@id}[]" multiple="">
					<xsl:apply-templates select="list_items/list_item"/>
				</select>
			</xsl:if>
		</td>
		<xsl:if test="position() mod 6 = 0">
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
					<xsl:attribute name="checked">checked</xsl:attribute>
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
					
					<xsl:attribute name="checked">checked</xsl:attribute>
				</xsl:if>
				<label for="id_property_id_{../../@id}_{@id}">
					<xsl:value-of disable-output-escaping="yes" select="list_item_value"/>
				</label>
			</input>
		</xsl:if>
		<xsl:if test="../../property_show_kind = 7">
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
		
	</xsl:template>
	
	<!-- Цикл с шагом 10 для select'a количества элементов на страницу -->
	<xsl:template name="for_on_page">
		<xsl:param name="i" select="0"/>
		<xsl:param name="n"/>
		
		<option value="{$i}">
			<xsl:if test="$i = /shop/on_page">
				<xsl:attribute name="selected">
				</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="$i"/>
		</option>
		
		<xsl:if test="$n &gt; $i">
			<!-- Рекурсивный вызов шаблона -->
			<xsl:call-template name="for_on_page">
				<xsl:with-param name="i" select="$i + 10"/>
				<xsl:with-param name="n" select="$n"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для групп товара -->
	<xsl:template match="shop_group">
		<!--<xsl:variable name="parent_id" select="@parent"/>-->
		<div class="group_good_block">
			<xsl:if test="image_small != ''">
				<a href="{/shop/path}{fullpath}"><img src="{dir}{image_small}" border="0" style="margin-top: 5%; margin-bottom: 3px;" /></a><br />
			</xsl:if>
			<div class="index_item_title">
				<a href="{url}" style="font-weight: bold" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group">
					<xsl:value-of select="name"/>
			</a>&#xA0;<span class="group_count_goods">(<xsl:value-of select="items_total_count"/>)</span>
			</div>
			<!--
			<br/>
			<xsl:value-of disable-output-escaping="yes" select="description"/>
			-->
			<xsl:if test="count(shop_group) &gt; 1">
				<!--	<xsl:apply-templates select="group" mode="sub_group"/>-->
			</xsl:if>
		</div>
		
		<!--<xsl:if test="position()= round(count(//group[@parent = $parent_id]) div 3)">-->
			<xsl:if test="position() mod 3 = 0">
				
				<xsl:text disable-output-escaping="yes">
					&lt;/td&gt;
					&lt;td valign="top" width="33%" align="center"&gt;
				</xsl:text>
			</xsl:if>
		</xsl:template>
		
		<!-- Шаблон для подразделов -->
		<xsl:template match="shop_group" mode="sub_group">
			<a href="{url}"  hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group">
				<xsl:value-of select="name"/>
			</a>
			<xsl:variable name="parent_id" select="parent_id"/>
			<!-- Ставим запятую после группы, за которой следуют еще группы из данной родителской группы -->
			<xsl:if test="position() != last() and count(//shop_group[parent_id = $parent_id]) &gt; 1">,&#xA0;</xsl:if>
		</xsl:template>
		
		<!-- Шаблон для товара -->
		<xsl:template match="shop_item">
			<div class="good_block">
				<!-- Указана малое изображение -->
				<xsl:if test="image_small != ''">
					<a href="{url}">
						<img src="{dir}{image_small}" alt="{name}" title="{name}" style="margin-top: 5%; margin-bottom: 3px;"/>
					</a>
					<!--<div class="hit" style="margin: {small_image/@height - 35}px 0 0 90px;"></div>-->
				</xsl:if>
				<div class="index_item_title">
					<a href="{url}" title="{name}">
						<xsl:value-of select="name"/>
					</a>
				</div>
			</div>
			
			<div class="dcc">
				<div class="deteils">
					<div class="inner"><xsl:value-of select="format-number(price, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="currency"/></div>
				</div>
				<div class="in_cart">
					<div class="inner">
						<a href="{/shop/url}cart/?add={@id}" onclick="return $.addIntoCart('{/shop/url}cart/', {@id}, 1)">Купить</a>
					</div>
				</div>
				<div style="clear: both" />
			</div>
			<xsl:if test="position() = round(/shop/items_on_page div 3) or position() = round(/shop/items_on_page * 2 div 3)">
				<xsl:text disable-output-escaping="yes">
					&lt;/div&gt;
					&lt;div class="column p_l"&gt;
				</xsl:text>
			</xsl:if>
			
		</xsl:template>
		
		<!-- Метки для товаров -->
		<xsl:template match="tag">
			<a href="{/shop/url}tag/{urlencode}/" class="tag">
				<xsl:value-of select="name"/>
			</a>
		<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if>
		</xsl:template>
		
		<!-- Шаблон для модификаций -->
		<xsl:template match="modifications/item">
			<tr>
				<td>
					
					<a href="{/shop/path}{fullpath}{path}/">
						<img src="{small_image}" class="image" />
					</a>
					
				</td>
				<td>
					<!-- Название модификации -->
					<a href="{/shop/path}{fullpath}{path}/">
						<xsl:value-of select="name"/>
					</a>
				</td>
				<td>
					<!-- Цена модификации -->
					<xsl:choose>
						<xsl:when test="price_discount != 0">
							<xsl:value-of disable-output-escaping="yes" select="price_discount"/>&#xA0;
							<!-- Валюта товара -->
							<xsl:value-of disable-output-escaping="yes" select="currency"/>
						</xsl:when>
						<xsl:otherwise>договорная</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</xsl:template>
		
		<!-- Шаблон для скидки -->
		<xsl:template match="discount">
			<br/>
			<xsl:value-of select="name"/>&#xA0;
			<xsl:value-of disable-output-escaping="yes" select="value"/>%</xsl:template>
		
		<!-- Шаблон для спеццен -->
		<xsl:template match="special_price">
			
			<xsl:variable name="item_id" select="@item_id" />
			
			<br/>
			от <xsl:value-of select="shop_special_prices_from"/> до <xsl:value-of select="shop_special_prices_to"/>&#xA0;<xsl:value-of select="/shop/item[@id = $item_id]/mesure"/>
			<xsl:text> </xsl:text>
			&#8212;
			<xsl:text> </xsl:text>
			<xsl:value-of select="format-number(shop_special_prices_price,'### ##0,00', 'my')" />&#xA0;<xsl:value-of select="/shop/item[@id = $item_id]/currency"/>
			за 1 <xsl:value-of select="/shop/item[@id = $item_id]/mesure"/>
		</xsl:template>
		
		<!-- Цикл для вывода строк ссылок -->
		<xsl:template name="for">

			<xsl:param name="limit"/>
			<xsl:param name="page"/>
			<xsl:param name="pre_count_page"/>
			<xsl:param name="post_count_page"/>
			<xsl:param name="i" select="0"/>
			<xsl:param name="items_count"/>
			<xsl:param name="visible_pages"/>

			<xsl:variable name="n" select="ceiling($items_count div $limit)"/>

			<xsl:variable name="start_page"><xsl:choose>
					<xsl:when test="$page + 1 = $n"><xsl:value-of select="$page - $visible_pages + 1"/></xsl:when>
					<xsl:when test="$page - $pre_count_page &gt; 0"><xsl:value-of select="$page - $pre_count_page"/></xsl:when>
					<xsl:otherwise>0</xsl:otherwise>
			</xsl:choose></xsl:variable>

			<xsl:if test="$i = $start_page and $page != 0">
				<span class="ctrl">
					← Ctrl
				</span>
			</xsl:if>

			<xsl:if test="$i = ($page + $post_count_page + 1) and $n != ($page+1)">
				<span class="ctrl">
					Ctrl →
				</span>
			</xsl:if>

			<!-- Передаем фильтр -->
			<xsl:variable name="filter"><xsl:if test="/shop/filter/node()">?filter=1&amp;sorting=<xsl:value-of select="/shop/sorting"/>&amp;price_from=<xsl:value-of select="/shop/price_from"/>&amp;price_to=<xsl:value-of select="/shop/price_to"/><xsl:for-each select="/shop/*"><xsl:if test="starts-with(name(), 'property_')">&amp;<xsl:value-of select="name()"/>=<xsl:value-of select="."/></xsl:if></xsl:for-each></xsl:if></xsl:variable>
			
			<xsl:if test="$items_count &gt; $limit and ($page + $post_count_page + 1) &gt; $i">
				<!-- Заносим в переменную $group идентификатор текущей группы -->
				<xsl:variable name="group" select="/shop/group"/>

				<!-- Путь для тэга -->
				<xsl:variable name="tag_path"><xsl:if test="count(/shop/tag) != 0">tag/<xsl:value-of select="/shop/tag/urlencode"/>/</xsl:if></xsl:variable>

				<!-- Путь для сравнения товара -->
				<xsl:variable name="shop_producer_path"><xsl:if test="count(/shop/shop_producer)">producer-<xsl:value-of select="/shop/shop_producer/@id"/>/</xsl:if></xsl:variable>

				<!-- Определяем группу для формирования адреса ссылки -->
				<xsl:variable name="group_link"><xsl:choose><xsl:when test="$group != 0"><xsl:value-of select="/shop//shop_group[@id=$group]/url"/></xsl:when><xsl:otherwise><xsl:value-of select="/shop/url"/></xsl:otherwise></xsl:choose></xsl:variable>

				<!-- Определяем адрес ссылки -->
				<xsl:variable name="number_link"><xsl:if test="$i != 0">page-<xsl:value-of select="$i + 1"/>/</xsl:if></xsl:variable>

				
				
				<!-- Выводим ссылку на первую страницу -->
				<xsl:if test="$page - $pre_count_page &gt; 0 and $i = $start_page">
					<a href="{$group_link}{$tag_path}{$shop_producer_path}{$filter}" class="page_link" style="text-decoration: none;">←</a>
				</xsl:if>

				<!-- Ставим ссылку на страницу-->
				<xsl:if test="$i != $page">
					<xsl:if test="($page - $pre_count_page) &lt;= $i and $i &lt; $n">
						<!-- Выводим ссылки на видимые страницы -->
						<a href="{$group_link}{$number_link}{$tag_path}{$shop_producer_path}{$filter}" class="page_link">
							<xsl:value-of select="$i + 1"/>
						</a>
					</xsl:if>

					<!-- Выводим ссылку на последнюю страницу -->
					<xsl:if test="$i+1 &gt;= ($page + $post_count_page + 1) and $n &gt; ($page + 1 + $post_count_page)">
						<!-- Выводим ссылку на последнюю страницу -->
						<a href="{$group_link}page-{$n}/{$tag_path}{$shop_producer_path}{$filter}" class="page_link" style="text-decoration: none;">→</a>
					</xsl:if>
				</xsl:if>

				<!-- Ссылка на предыдущую страницу для Ctrl + влево -->
				<xsl:if test="$page != 0 and $i = $page"><xsl:variable name="prev_number_link"><xsl:if test="($page) != 0">page-<xsl:value-of select="$i"/>/</xsl:if></xsl:variable><a href="{$group_link}{$prev_number_link}{$tag_path}{$shop_producer_path}{$filter}" id="id_prev"></a></xsl:if>

				<!-- Ссылка на следующую страницу для Ctrl + вправо -->
				<xsl:if test="($n - 1) > $page and $i = $page">
					<a href="{$group_link}page-{$page+2}/{$tag_path}{$shop_producer_path}{$filter}" id="id_next"></a>
				</xsl:if>

				<!-- Не ставим ссылку на страницу-->
				<xsl:if test="$i = $page">
					<span class="current">
						<xsl:value-of select="$i+1"/>
					</span>
				</xsl:if>

				<!-- Рекурсивный вызов шаблона. НЕОБХОДИМО ПЕРЕДАВАТЬ ВСЕ НЕОБХОДИМЫЕ ПАРАМЕТРЫ! -->
				<xsl:call-template name="for">
					<xsl:with-param name="i" select="$i + 1"/>
					<xsl:with-param name="limit" select="$limit"/>
					<xsl:with-param name="page" select="$page"/>
					<xsl:with-param name="items_count" select="$items_count"/>
					<xsl:with-param name="pre_count_page" select="$pre_count_page"/>
					<xsl:with-param name="post_count_page" select="$post_count_page"/>
					<xsl:with-param name="visible_pages" select="$visible_pages"/>
				</xsl:call-template>
			</xsl:if>
		</xsl:template>
			
		<!-- Шаблон выводит рекурсивно ссылки на группы инф. элемента -->
		<xsl:template match="shop_group" mode="breadCrumbs">
			<xsl:param name="parent_id" select="parent_id"/>
			
			<!-- Получаем ID родительской группы и записываем в переменную $group -->
			<xsl:param name="group" select="/shop/shop_group"/>
			
			<xsl:apply-templates select="//shop_group[@id=$parent_id]" mode="breadCrumbs"/>
			
			<xsl:if test="parent_id=0">
				<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
					<xsl:value-of select="/shop/name"/>
				</a>
			</xsl:if>
			
		<span class="path_arrow"><xsl:text>→</xsl:text></span>
			
			<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group">
				<xsl:value-of select="name"/>
			</a>
		</xsl:template>
	</xsl:stylesheet>