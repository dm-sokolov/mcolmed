<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/siteuser">
		<!-- Быстрая регистрация в магазине -->
		<xsl:if test="fastRegistration/node()">
			<div id="fastRegistrationDescription" style="float: left">
				<p class="title">Быстрая регистрация</p>
				<b>Какие преимущества дает регистрация на сайте?</b>
				
				<ul class="ul1">
					<li>Вы получаете возможность оформлять заказы прямо на сайте.</li>
					<li>Вы будете получать информацию о специальных акциях магазина, доступных только зарегистрированным пользователям.</li>
				</ul>
				
				<p>
					<a href="/users/registration/" onclick="$('#fastRegistrationDescription').hide('slow'); $('#fastRegistration').show('slow'); return false">Заполнить форму регистрации →</a>
				</p>
			</div>
		</xsl:if>
		
		<div>
			<xsl:if test="fastRegistration/node()">
				<xsl:attribute name="id">fastRegistration</xsl:attribute>
			</xsl:if>
			
			<h1>
				<xsl:choose>
					<xsl:when test="@id > 0">Анкетные данные</xsl:when>
					<xsl:otherwise>Регистрация нового пользователя</xsl:otherwise>
				</xsl:choose>
			</h1>
			
			<!-- Выводим ошибку, если она была передана через внешний параметр -->
			<xsl:if test="error/node()">
				<div id="error">
					<xsl:value-of select="error"/>
				</div>
			</xsl:if>
			
			<p>Обратите внимание, введенные контактные данные будут доступны на странице пользователя
				неограниченному кругу лиц.
				<br />Обязательные поля отмечены *.</p>
			
			<form action="/users/registration/" method="post" enctype="multipart/form-data" class="registration">
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000000"/>
				<table border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td width="105px">Логин</td>
						<td>
							<input name="login" type="text" value="{login}" size="40"/> *</td>
					</tr>
					<tr>
						<td>Пароль</td>
						<td>
							<input name="password" type="password" value="" size="40"/>
							
							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="id = ''"> *</xsl:if>
						</td>
					</tr>
					<tr>
						<td>Повтор пароля</td>
						<td>
							<input name="password2" type="password" value="" size="40"/>
							
							<!-- Для авторизированного пользователя заполнять пароль при редактирвоании данных необязательно -->
							<xsl:if test="id = ''"> *</xsl:if>
						</td>
					</tr>
					<tr>
						<td>E-mail</td>
						<td>
							<input name="email" type="text" value="{email}" size="40"/> *</td>
					</tr>
					<tr>
						<td>Фамилия</td>
						<td>
							<input name="surname" type="text" value="{surname}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Имя</td>
						<td>
							<input name="name" type="text" value="{name}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Отчество</td>
						<td>
							<input name="patronymic" type="text" value="{patronymic}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Компания</td>
						<td>
							<input name="company" type="text" value="{company}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Телефон</td>
						<td>
							<input name="phone" type="text" value="{phone}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Факс</td>
						<td>
							<input name="fax" type="text" value="{fax}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Сайт</td>
						<td>
							<input name="website" type="text" value="{website}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>ICQ</td>
						<td>
							<input name="icq" type="text" value="{icq}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Страна</td>
						<td>
							<input name="country" type="text" value="{country}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Почтовый индекс</td>
						<td>
							<input name="postcode" type="text" value="{postcode}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Город</td>
						<td>
							<input name="city" type="text" value="{city}" size="40"/>
						</td>
					</tr>
					<tr>
						<td>Адрес</td>
						<td>
							<input name="address" type="text" value="{address}" size="40"/>
						</td>
					</tr>
					
					<!-- Внешние параметры -->
					<xsl:if test="count(properties/property)">
						<xsl:apply-templates select="properties/property"/>
					</xsl:if>
					
					<xsl:if test="@id > 0 and count(maillist) > 0">
						<tr>
							<td colspan="2">
								<h2>Почтовые рассылки</h2>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<table border="0" class="table_themes">
									<tr>
										<th>Рассылка</th>
										<th>Формат</th>
										<th>Подписаться</th>
									</tr>
									<xsl:apply-templates select="maillist"></xsl:apply-templates>
								</table>
							</td>
						</tr>
					</xsl:if>
					
					<!-- Код подтверждения выводится только при регистрации -->
					<xsl:if test="not(/siteuser/@id > 0)">
						<tr>
							<td>Контрольное число</td>
							<td>
								<div style="float:left">
									<img class="image" id="registerUser" src="/captcha.php?id={/siteuser/captcha_id}&amp;height=30&amp;width=100" title="Код подтверждения" name="captcha"/>
								</div>
								
								<div id="captcha" style="clear:both;">
									<img style="border: 0px" src="/hostcmsfiles/images/refresh.gif" />
									<span onclick="$('#registerUser').updateCaptcha('{/siteuser/captcha_id}', 30); return false" style="cursor: pointer">Показать другое число</span>
									
									<!--
									<a onclick="$('#registerUser').updateCaptcha('{/siteuser/captcha_id}', 30); return false">Показать другое число</a>
									-->
								</div>
								
								<div style="float: left;margin-top: 5px">
									<input type="hidden" name="captcha_id" value="{/siteuser/captcha_id}"/>
									<input type="text" name="captcha" size="15"/> *
								</div>
								
								<div id="captcha" style="clear:both;">
									Введите число, которое указано выше.
								</div>
							</td>
						</tr>
					</xsl:if>
				</table>
				
				<!-- Страница редиректа после авторизации -->
				<xsl:if test="location/node()">
					<input name="location" type="hidden" value="{location}" />
				</xsl:if>
				
				<!-- Определяем имя кнопки -->
				<xsl:variable name="buttonName"><xsl:choose>
						<xsl:when test="@id > 0">Изменить</xsl:when>
						<xsl:otherwise>Зарегистрироваться</xsl:otherwise>
				</xsl:choose></xsl:variable>
				
				<div class="gray_button">
					<div>
						<input name="apply" type="submit" value="{$buttonName}"/>
					</div>
				</div>
			</form>
		</div>
	</xsl:template>
	
	<xsl:template match="maillist">
		<xsl:variable name="id" select="@id" />
		<xsl:variable name="maillist_siteuser" select="/siteuser/maillist_siteuser[maillist_id = $id]" />
		
		<tr>
			<td>
				<xsl:value-of select="name"/>
			</td>
			<td>
				<xsl:value-of select="description"/>
			</td>
			<td align="center">
				<select name="type_{@id}">
					<option value="0">
					<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 0"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						Текст
					</option>
					<option value="1">
					<xsl:if test="$maillist_siteuser/node() and $maillist_siteuser/type = 1"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
						HTML
					</option>
				</select>
			</td>
			<td align="center">
				<input name="maillist_{@id}" type="checkbox" value="1">
				<xsl:if test="$maillist_siteuser/node()"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
				</input>
			</td>
		</tr>
	</xsl:template>
	
	<!-- Внешние свойства -->
	<xsl:template match="properties/property">
		<xsl:if test="type != 10">
			<xsl:variable name="id" select="@id" />
			<xsl:variable name="property_value" select="/siteuser/property_value[property_id=$id]" />
			
			<tr>
				<td>
					<xsl:value-of select="name"/>
				</td>
				<td>
					<xsl:choose>
						<!-- Отображаем поле ввода -->
						<xsl:when test="type = 0 or type=1">
							<input type="text" name="property_{@id}" value="$property_value/value" size="40" />
						</xsl:when>
						<!-- Отображаем файл -->
						<xsl:when test="type = 2">
							<input type="file" name="property_{@id}" size="35" />
							
							<xsl:if test="$property_value/file != ''">
								<xsl:text> </xsl:text>
						<a href="{/siteuser/dir}{$property_value/file}" target="_blank"><img src="/hostcmsfiles/images/preview.gif" class="img"/></a><xsl:text> </xsl:text><a href="?delete_property={$property_value/property_id}" onclick="return confirm('Вы уверены, что хотите удалить?')"><img src="/hostcmsfiles/images/delete.gif" class="img" /></a>
							</xsl:if>
						</xsl:when>
						<!-- Отображаем список -->
						<xsl:when test="type = 3">
							<select name="property_{@id}">
								<option value="0">...</option>
								<xsl:apply-templates select="list/list_item"/>
							</select>
						</xsl:when>
						<!-- Большое текстовое поле, Визуальный редактор -->
						<xsl:when test="type = 4 or type = 6">
							<textarea name="property_{@id}" size="40"><xsl:value-of disable-output-escaping="yes" select="$property_value/value" /></textarea>
						</xsl:when>
						<!-- Флажок -->
						<xsl:when test="type = 7">
							<input type="checkbox" name="property_{@id}">
							<xsl:if test="$property_value/value = 1"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
							</input>
						</xsl:when>
					</xsl:choose>
				</td>
			</tr>
		</xsl:if>
	</xsl:template>
	
	<!-- Внешнее свойство типа "список" -->
	<xsl:template match="list/list_item">
		<!-- Отображаем список -->
		<xsl:variable name="id" select="../../@id" />
		<option value="{@id}">
		<xsl:if test="/siteuser/property_value[property_id=$id]/value = @id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of disable-output-escaping="yes" select="value"/>
		</option>
	</xsl:template>
</xsl:stylesheet>