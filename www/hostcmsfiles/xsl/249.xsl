<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- МагазинТовар -->
	
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	
	<xsl:template match="/shop">
		<xsl:apply-templates select="shop_item"/>
	</xsl:template>
	
	<xsl:template match="shop_item">
		
		<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item">
			<xsl:value-of select="name"/>
		</h1>
		
		<!-- Получаем ID родительской группы и записываем в переменную $group -->
		<xsl:if test="0">
			<xsl:variable name="group" select="/shop/group"/>
			
			<p>
				<xsl:if test="$group = 0">
					<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
						<xsl:value-of select="/shop/name"/>
					</a>
				</xsl:if>
				
				<!-- Путь к группе -->
				<xsl:apply-templates select="/shop//shop_group[@id=$group]" mode="breadCrumbs"/>
				
				<!-- Если модификация, выводим в пути родительский товар -->
				<xsl:if test="shop_item/node()">
					<span class="url_arrow">→</span>
					<a href="{shop_item/url}">
						<xsl:value-of disable-output-escaping="yes" select="shop_item/name"/>
					</a>
				</xsl:if>
				
				<span class="url_arrow">→</span>
				
			<b><a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item"><xsl:value-of select="name"/></a></b>
			</p>
		</xsl:if>
		
		
		<!-- Средняя оценка товара -->
		<xsl:if test="comments/average_grade/node()">
			<div style="float: left; margin: 0px 0px 5px 15px">
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="comments_average_grade"/>
					<xsl:with-param name="const_grade" select="5"/>
				</xsl:call-template>
			</div>
			<div style="clear: both"></div>
		</xsl:if>
		
		<!-- Выводим сообщение -->
		<xsl:if test="/shop/message/node()">
			<!--<div id="error">-->
				<xsl:value-of disable-output-escaping="yes" select="/shop/message"/>
				<!--</div>-->
		</xsl:if>
		
		<div class="catalog_item">
			<!-- Изображение для товара, если есть -->
			<xsl:if test="image_small != ''">
				<div id="gallery" style="float: left; width: {image_small_width}px; margin: 0px 10px 10px 0px;">
					<a href="{dir}{image_large}" target="_blank">
						<img src="{dir}{image_small}" class="image" />
					</a>
				</div>
			</xsl:if>
			
			<!-- Цена товара -->
			<p>Цена:
				<span style="font-size: 11pt; font-weight: bold">
					<xsl:choose>
						<xsl:when test="price != 0">
							<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of select="currency" disable-output-escaping="yes"/>
						</xsl:when>
						<xsl:otherwise>договорная</xsl:otherwise>
					</xsl:choose>
				</span>
				<br/>
				
				<!-- Если цена со скидкой - выводим ее -->
				<xsl:if test="price_tax != price">
					<span style="color: gray; text-decoration: line-through;">
						<xsl:variable name="price_tax" select="price_tax"/>
						<span style="font-size: 11pt">
							<xsl:value-of select="format-number($price_tax, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="currency"/></span>
					</span>
					<br/>
				</xsl:if>
			</p>
			
			<!-- Ссылку на добавление в корзины выводим, если:
			type != 1 - простой тип товара или делимый (0 - простой, 2 - делимый)
			type = 1 - электронный товар, при этом остаток на складе больше 0 или -1,
			что означает неограниченное количество -->
			<xsl:if test="type = 0 or (type = 1 and (digitals > 0 or digitals = -1))">
				<p>
					<input type="text" size="3" value="1" id="count" name="count" class="input_count_items"/>
					
					<a href="{/shop/url}cart/?add={@id}" onclick="return $.addIntoCart('{/shop/url}cart/', {@id}, $('#count').val())">
						<img alt="В корзину" title="В корзину" src="/hostcmsfiles/images/cart.gif" style="margin: 0px 0px -4px 10px" /></a>
				</p>
			</xsl:if>
			
			<xsl:if test="marking != ''">
			<p>Артикул: <b hostcms:id="{@id}" hostcms:field="marking" hostcms:entity="shop_item"><xsl:value-of select="marking"/></b></p>
			</xsl:if>
			
			<xsl:if test="shop_producer/node()">
			<p>Производитель: <b><xsl:value-of select="shop_producer/name"/></b></p>
			</xsl:if>
			
			<!-- Если указан вес товара -->
			<xsl:if test="weight != 0">
			<p>Вес товара: <span hostcms:id="{@id}" hostcms:field="weight" hostcms:entity="shop_item"><xsl:value-of select="weight"/></span>&#xA0;<xsl:value-of select="/shop/shop_measure/name"/></p>
			</xsl:if>
			
			<!-- Показываем скидки -->
			<xsl:if test="count(shop_discount)">
				<xsl:apply-templates select="shop_discount"/>
			</xsl:if>
			
			<!-- Показываем количество на складе, если больше нуля -->
			<xsl:if test="rest &gt; 0 and type != 1">
				<p>В наличии: <xsl:value-of select="rest"/>&#xA0;<xsl:value-of select="shop_measure/name"/></p>
			</xsl:if>
			
			<!-- Если электронный товар, выведим доступное количество -->
			<xsl:if test="type = 1">
				<p>
					<strong>
						<xsl:choose>
							<xsl:when test="digitals = 0">
								Электронный товар закончился.
							</xsl:when>
							<xsl:when test="digitals = -1">
								Электронный товар доступен для заказа.
							</xsl:when>
							<xsl:otherwise>
							На складе осталось: <xsl:value-of select="digitals" /><xsl:text> </xsl:text><xsl:value-of select="shop_measure/name" />
							</xsl:otherwise>
						</xsl:choose>
					</strong>
				</p>
			</xsl:if>
			
			<div style="clear: both;"></div>
			<!-- Текст товара -->
			<xsl:if test="text != ''">
				<p class="shop_item_description"  hostcms:id="{@id}" hostcms:field="text" hostcms:entity="shop_item" hostcms:type="wysiwyg">
					<xsl:value-of disable-output-escaping="yes" select="text"/>
				</p>
			</xsl:if>
			
			<xsl:if test="count(property_value) > 0">
				<h2>Характеристики</h2>
				<dl class="additional_info">
					<xsl:apply-templates select="property_value"/>
				</dl>
			</xsl:if>
		</div>
		
		<!-- Тэги для информационного элемента -->
		<xsl:if test="count(tag) &gt; 0">
			<p>
				<img src="/hostcmsfiles/images/tags.gif" align="left" style="margin: 0px 5px -2px 0px"/>
				<xsl:apply-templates select="tag"/>
			</p>
		</xsl:if>
		
		<!-- Модификации -->
		<xsl:if test="count(modifications/shop_item) &gt; 0">
			<b>Модификации:</b>
			<table cellspacing="3" cellpadding="3" style="margin-left: -6px;">
				<tr>
					<td style="border-bottom: 1px solid #dadada;">Название</td>
					<td style="border-bottom: 1px solid #dadada;">Цена</td>
				</tr>
				<xsl:apply-templates select="modifications/shop_item"/>
			</table>
		</xsl:if>
		
		<xsl:if test="count(associated/shop_item) &gt; 0">
			<p>
				<b>Сопутствующие товары:</b>
			</p>
			<!-- Отображаем сопутствующие товары -->
			<xsl:apply-templates select="associated/shop_item"/>
			<div style="clear: both;"></div>
		</xsl:if>
		
		<!-- Отзывы о товаре -->
		<xsl:if test="/shop/show_comments/node() and /shop/show_comments = 1">
			<xsl:if test="count(comment) &gt; 0">
				<p class="title">
				<a name="comments"></a>Отзывы о товаре</p>
				<xsl:apply-templates select="comment" />
			</xsl:if>
		</xsl:if>
		
		<!-- Если разрешено отображать формы добавления комментария
		1 - Только авторизированным
		2 - Всем
		-->
		<xsl:if test="/shop/show_add_comments/node() and ((/shop/show_add_comments = 1 and /shop/siteuser_id &gt; 0)  or /shop/show_add_comments = 2)">
			<xsl:if test="/shop/show_add_comments/node() and ((/shop/show_add_comments = 1 and /shop/siteuser_id &gt; 0)  or /shop/show_add_comments = 2)">
				
				<p class="button" onclick="$('.comment_reply').hide('slow');$('#AddComment').toggle('slow')">
					Добавить комментарий
				</p>
				
				<div id="AddComment" class="comment_reply">
					<xsl:call-template name="AddCommentForm"></xsl:call-template>
				</div>
			</xsl:if>
			<!--
			<div id="ShowAddComment">
				<a href="javascript:void(0)" onclick="javascript:cr('AddComment')">Добавить комментарий</a>
			</div>
			-->
		</xsl:if>
		
		<div id="AddComment" style="display: none">
			<xsl:call-template name="AddCommentForm"></xsl:call-template>
		</div>
	</xsl:template>
	
	<!-- Шаблон вывода добавления комментария -->
	<xsl:template name="AddCommentForm">
		<xsl:param name="id" select="0"/>
		
		<!-- Заполняем форму -->
		<xsl:variable name="subject">
			<xsl:if test="/shop/comment/parent_id/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/subject"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="email">
			<xsl:if test="/shop/comment/email/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/email"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="phone">
			<xsl:if test="/shop/comment/phone/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/phone"/>
		</xsl:if></xsl:variable>
		<xsl:variable name="text">
			<xsl:if test="/shop/comment/text/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of disable-output-escaping="yes" select="/shop/comment/text"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="name">
			<xsl:if test="/shop/comment/author/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/author"/>
			</xsl:if>
		</xsl:variable>
		
		<div class="comment">
			<!--Отображение формы добавления комментария-->
			<form action="{/shop/shop_item/url}" name="comment_form_0{$id}" method="post">
				<!-- Авторизированным не показываем -->
				<xsl:if test="/shop/siteuser_id = 0">
					<div class="row">
						<div class="caption">Имя</div>
						<div class="field">
							<input type="text" size="65" name="author" value="{$name}"/>
						</div>
					</div>
					<div class="row">
						<div class="caption">E-mail</div>
						<div class="field">
							<input id="email{$id}" type="text" size="65" name="email" value="{$email}" />
							<div id="error_email{$id}"></div>
						</div>
					</div>
					
					<div class="row">
						<div class="caption">Телефон</div>
						<div class="field">
							<input type="text" size="65" name="comment_phone" value="{$phone}"/>
						</div>
					</div>
				</xsl:if>
				
				<div class="row">
					<div class="caption">Тема</div>
					<div class="field">
						<input type="text" size="65" name="subject" value="{$subject}"/>
					</div>
				</div>
				
				<div class="row">
					<div class="caption">Комментарий</div>
					<div class="field">
						<textarea name="text" cols="63" rows="5" class="mceEditor"><xsl:value-of select="$text"/></textarea>
					</div>
				</div>
				<div class="row">
					<div class="caption">Оценка</div>
					<div class="field stars">
						<select name="grade">
							<option value="1">Poor</option>
							<option value="2">Fair</option>
							<option value="3">Average</option>
							<option value="4">Good</option>
							<option value="5">Excellent</option>
						</select>
					</div>
				</div>
				
				<!-- Обработка CAPTCHA -->
				<xsl:if test="//captcha_id != 0 and /shop/siteuser_id = 0">
					<div class="row">
						<div class="caption"></div>
						<div class="field">
							<img id="comment_{$id}" class="captcha" src="/captcha.php?id={//captcha_id}{$id}&amp;height=30&amp;width=100" title="Контрольное число" name="captcha"/>
							
							<div class="captcha">
								<img src="/images/refresh.png" /> <span onclick="$('#comment_{$id}').updateCaptcha('{//captcha_id}{$id}', 30); return false">Показать другое число</span>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="caption">
					Контрольное число<sup><font color="red">*</font></sup>
						</div>
						<div class="field">
							<input type="hidden" name="captcha_id" value="{//captcha_id}{$id}"/>
							<input type="text" name="captcha" size="15"/>
						</div>
					</div>
				</xsl:if>
				
				<xsl:if test="$id != 0">
					<input type="hidden" name="parent_id" value="{$id}"/>
				</xsl:if>
				
				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<input id="submit_email{$id}" type="submit" name="add_comment" value="Опубликовать" class="button" />
					</div>
				</div>
			</form>
		</div>
	</xsl:template>
	
	<!-- Вывод строки со значением свойства -->
	<xsl:template match="property_value">
		<xsl:if test="value/node() and value != '' or file/node() and file != ''">
<xsl:variable name="class_name"><xsl:choose><xsl:when test="position() mod 2 !=0">grey</xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
			<xsl:variable name="property_id" select="property_id" />
			<xsl:variable name="property" select="/shop/shop_item_properties//property[@id=$property_id]" />
			
			<dt><xsl:value-of select="$property/name"/></dt>
			<dd class="{$class_name}">
				<xsl:choose>
					<xsl:when test="$property/type = 2">
						<a href="{file_path}">Скачать файл</a>
					</xsl:when>
					<xsl:when test="$property/type = 7">
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
			</dd>
		</xsl:if>
	</xsl:template>
	
	<!-- /// Метки для информационного элемента /// -->
	<xsl:template match="tag">
		<a href="{/shop/url}tag/{urlencode}/" class="tag">
			<xsl:value-of select="tag_name"/>
		</a>
	<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if>
	</xsl:template>
	
	<!-- Шаблон для модификаций -->
	<xsl:template match="modifications/shop_item">
		<tr>
			<td>
				<!-- Название модификации -->
				<a href="{url}">
					<xsl:value-of select="name"/>
				</a>
			</td>
			<td>
				<!-- Цена модификации -->
				<xsl:value-of select="price"/>&#xA0;
				<!-- Валюта -->
				<xsl:value-of disable-output-escaping="yes" select="currency"/>
			</td>
		</tr>
	</xsl:template>
	
	<!-- Вывод рейтинга товара -->
	<xsl:template name="show_average_grade">
		<xsl:param name="grade" select="0"/>
		<xsl:param name="const_grade" select="0"/>
		
		<!-- Чтобы избежать зацикливания -->
		<xsl:variable name="current_grade" select="$grade * 1"/>
		
		<xsl:choose>
			<!-- Если число целое -->
			<xsl:when test="floor($current_grade) = $current_grade and not($const_grade &gt; ceiling($current_grade))">
				
				<xsl:if test="$current_grade - 1 &gt; 0">
					<xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="$current_grade - 1"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>
				
				<xsl:if test="$current_grade != 0">
					<img src="/hostcmsfiles/images/stars_single.gif"/>
				</xsl:if>
			</xsl:when>
			<xsl:when test="$current_grade != 0 and not($const_grade &gt; ceiling($current_grade))">
				
				<xsl:if test="$current_grade - 0.5 &gt; 0">
					<xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="$current_grade - 0.5"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>
				
				<img src="/hostcmsfiles/images/stars_half.gif"/>
			</xsl:when>
			
			<xsl:otherwise>
				<!-- Выводим серые звездочки, пока текущая позиция не дойдет то значения, увеличенного до целого -->
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/hostcmsfiles/images/stars_gray.gif"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<!-- Шаблон для вывода звездочек (оценки) -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="n"/>
		
		<input type="radio" name="shop_grade" value="{$i}" id="id_shop_grade_{$i}">
			<xsl:if test="/shop/shop_grade = $i">
				<xsl:attribute name="checked"></xsl:attribute>
			</xsl:if>
	</input><xsl:text> </xsl:text>
		<label for="id_shop_grade_{$i}">
			<xsl:call-template name="show_average_grade">
				<xsl:with-param name="grade" select="$i"/>
				<xsl:with-param name="const_grade" select="5"/>
			</xsl:call-template>
		</label>
		<br/>
		<xsl:if test="$n &gt; $i and $n &gt; 1">
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="n" select="$n"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для отзывов -->
	<xsl:template match="comment">
		<a name="comment{@id}"></a>
		<div class="comment" id="comment{@id}">
			<div class="tl"></div>
			<div class="tr"></div>
			<div class="bl"></div>
			<div class="br"></div>
			
			<xsl:if test="subject != ''">
				<div>
					<strong class="subject" hostcms:id="{@id}" hostcms:field="subject" hostcms:entity="comment">
						<xsl:value-of select="subject"/>
					</strong>
				</div>
			</xsl:if>
			<span  hostcms:id="{@id}" hostcms:field="text" hostcms:entity="comment" hostcms:type="wysiwyg"><xsl:value-of select="text" disable-output-escaping="yes"/></span>
			
			<!-- Оценка комментария -->
			<!--
			<xsl:if test="grade != 0">
				<div>Оценка:
					<xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="grade"/>
						<xsl:with-param name="const_grade" select="5"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			-->
			<p class="tags">
				<!-- Оценка комментария -->
				<xsl:if test="grade != 0">
					<span><xsl:call-template name="show_average_grade">
							<xsl:with-param name="grade" select="grade"/>
							<xsl:with-param name="const_grade" select="5"/>
					</xsl:call-template></span>
				</xsl:if>
				
				<!--<img src="/images/user.png" />-->
				<img src="/hostcmsfiles/images/user.gif"/>
				
				<xsl:choose>
					<!-- Комментарий добавил авторизированный пользователь -->
					<xsl:when test="count(siteuser) &gt; 0">
					<span><a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a></span>
					</xsl:when>
					<!-- Комментарй добавил неавторизированный пользователь -->
					<xsl:otherwise>
						<span><xsl:value-of select="author" /></span>
					</xsl:otherwise>
				</xsl:choose>
				
				<img src="/hostcmsfiles/images/calendar.gif" /> <span><xsl:value-of select="datetime"/></span>
				
				<xsl:if test="/shop/show_add_comments/node()
					and ((/shop/show_add_comments = 1 and /shop/siteuser_id > 0)
					or /shop/show_add_comments = 2)">
				<span class="red" onclick="$('.comment_reply').hide('slow');$('#cr_{@id}').toggle('slow')">ответить</span></xsl:if>
				
			<span class="red"><a href="{/shop/shop_item/url}#comment{@id}" title="Ссылка на комментарий">#</a></span>
			</p>
			
		</div>
		<!-- Отображаем только авторизированным пользователям -->
		<xsl:if test="/shop/show_add_comments/node() and ((/shop/show_add_comments = 1 and /shop/siteuser_id > 0) or /shop/show_add_comments = 2)">
			<div class="comment_reply" id="cr_{@id}">
				<xsl:call-template name="AddCommentForm">
					<xsl:with-param name="id" select="@id"/>
				</xsl:call-template>
			</div>
		</xsl:if>
		
		<!-- Выбираем дочерние комментарии -->
		<xsl:if test="count(comment)">
			<div class="comment_sub">
				<xsl:apply-templates select="comment"/>
			</div>
		</xsl:if>
		<!--
		<div class="comment_desc">
			<xsl:choose>
				<xsl:when test="siteuser/name">
					<xsl:value-of select="siteuser/name"/>
				</xsl:when>
				<xsl:otherwise>
					<img src="/hostcmsfiles/images/user.gif"  style="margin: 0px 5px -4px 0px" />
					<b>
						<a href="/users/info/{siteuser/path}/"  class="c_u_l" ><xsl:value-of select="siteuser/login"/></a>
					</b>
				</xsl:otherwise>
		</xsl:choose>&#xA0;·&#xA0;<xsl:value-of select="datetime"/>&#xA0;·&#xA0;<a href="{/shop/shop_item/url}#comment{@id}" title="ссылка">#</a>
		</div>
		-->
	</xsl:template>
	
	<!-- Шаблон для скидки -->
	<xsl:template match="shop_discount">
		<p>
			<xsl:value-of select="name"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="percent"/>%
		</p>
	</xsl:template>
	
	<xsl:template match="associated/shop_item">
		<div style="clear: both">
			<p>
				<a href="{url}">
					<xsl:value-of select="name"/>
				</a>
			</p>
			<!-- Изображение для товара, если есть -->
			<xsl:if test="image_small != ''">
				<a href="{dir}{image_large}">
					<img src="{dir}{image_small}" align="left" style="border: 1px solid #000000; margin: 0px 5px 5px 0px"/>
				</a>
			</xsl:if>
			<xsl:if test="description != ''">
				<p hostcms:id="{@id}" hostcms:field="description" hostcms:entity="shop_item" hostcms:type="wysiwyg">
					<xsl:value-of disable-output-escaping="yes" select="description"/>
				</p>
			</xsl:if>
			<!-- Цена товара -->
			<strong>
				<xsl:choose>
					<xsl:when test="price != 0">
						<xsl:value-of disable-output-escaping="yes" select="format-number(price, '### ##0,00', 'my')"/>&#xA0;
						<!-- Валюта товара -->
						<xsl:value-of disable-output-escaping="yes" select="currency"/>
					</xsl:when>
					<xsl:otherwise>договорная</xsl:otherwise>
				</xsl:choose>
			</strong>
			<!-- Если цена со скидкой - выводим ее -->
			<xsl:if test="price_tax != price">
				<br/>
				<font color="gray">
					<strike>
						<xsl:value-of disable-output-escaping="yes" select="format-number(price_tax, '### ##0,00', 'my')"/>&#xA0;<xsl:value-of disable-output-escaping="yes" select="currency"/></strike>
				</font>
			</xsl:if>
			
			<!-- Если указан вес товара -->
			<xsl:if test="weight != 0">
			<br/>Вес товара: <span hostcms:id="{@id}" hostcms:field="weight" hostcms:entity="shop_item"><xsl:value-of select="weight"/></span><xsl:text> </xsl:text><xsl:value-of select="weight_mesure"/>
			</xsl:if>
			
			<!-- Показываем скидки -->
			<xsl:if test="count(shop_discount)">
				<xsl:apply-templates select="shop_discount"/>
			</xsl:if>
			
			<!-- Показываем количество на складе, если больше нуля -->
			<xsl:if test="rest &gt; 0 and type != 1">
				<br/>В наличии: <xsl:value-of select="rest"/>
			</xsl:if>
			
			<xsl:if test="shop_producer/node()">
				<br/>Производитель: <xsl:value-of select="shop_producer/name"/>
			</xsl:if>
		</div>
	</xsl:template>
	
	<!-- Шаблон выводит хлебные крошки -->
	<xsl:template match="shop_group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="parent_id"/>
		
		<!-- Выбираем рекурсивно вышестоящую группу -->
		<xsl:apply-templates select="//shop_group[@id=$parent_id]" mode="breadCrumbs"/>
		
		<xsl:if test="parent_id=0">
			<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
				<xsl:value-of select="/shop/name"/>
			</a>
		</xsl:if>
		
		<span class="url_arrow">→</span>
		
		<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>
</xsl:stylesheet>