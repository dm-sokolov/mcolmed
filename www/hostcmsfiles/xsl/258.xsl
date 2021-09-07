<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/document">
		
		<table border="0" cellspacing="0" cellpadding="0" style="margin-top: 20px;">
			<tr>
				
				<xsl:if test="ПоказатьАвторизацию = 'true'">
					<td style="vertical-align: top;" width="50%">
						<form action="./" style="padding-right: 50px;" name="address" method="POST">
							<p class="title">Авторизация</p>
							
							<xsl:choose>
								<xsl:when test="error != ''">
									<!-- Определяем текст ошибки по ее коду -->
									<xsl:variable name="error_text">
										<xsl:choose>
											<xsl:when test="error = -5">Вы не активировали свой аккаунт. По указанному адресу отправлено письмо с инструкцией об активации. После активации Вы можете авторизироваться.</xsl:when>
											<xsl:when test="error = -6">Неверный логин или пароль.</xsl:when>
											<xsl:otherwise></xsl:otherwise>
										</xsl:choose>
									</xsl:variable>
									
									<p style="font-weight: ;" class="red">
										<xsl:value-of select="$error_text"/>
									</p>
								</xsl:when>
								<xsl:otherwise>
									<p style="color: #707070">Если Вы зарегистрированы в нашем магазине, введите логин и пароль в соответствующие поля.</p>
								</xsl:otherwise>
							</xsl:choose>
							
							<table>
								<tr>
									<td>
										<div>Пользователь:</div>
										<input type="text" size="20" name="login" class="large_input" value="{site_users_login}"/>
									</td>
								</tr>
								<tr>
									<td>
										<div>Пароль:</div>
										<input type="password" size="20" class="large_input" name="password" value=""/>
									</td>
								</tr>
							</table>
							<p>
							Забыли пароль? Мы можем его <a href="/users/restore_password/">восстановить</a>.
								<!-- <a href="/users/restore_password/">Забыли&#xA0;пароль?</a> -->
							</p>
							<div class="gray_button">
								<div>
									<input name="step1_1" value="Продолжить оформление заказа" type="submit" class="cart_button"/>
								</div>
							</div>
						</form>
					</td>
				</xsl:if>
				
				<td style="vertical-align: top;" width="50%">
					
					<SCRIPT>
						<xsl:comment>
							<xsl:text disable-output-escaping="yes">
								<![CDATA[
								function HideShow(id, id1)
								{
								
								var el = document.getElementById(id);
								var el1 = document.getElementById(id1);
								el.style.display = 'none';
								el1.style.display = 'block';
								//el1.style.position = 'absolute';
								}
								]]>
							</xsl:text>
						</xsl:comment>
					</SCRIPT>
					
					<div id="first" style="display: block;">
						<p class="title">Регистрация нового клиента</p>
						<b>Какие преимущества дает регистрация на сайте?</b>
						<br/>
						<ul style="width: 270px">
							<li>Вы получаете возможность оформлять заказы прямо на сайте.</li>
							<!-- <li>Вы можете воспользоваться on-line кредитами</li>
							<li>Вы сможете получить персональную дисконтную карту и стать участником нашей программы лояльности</li> -->
							<li>Вы будете получать информацию о специальных акциях магазина, доступных только зарегистрированным пользователям.</li>
						</ul>
						
						<xsl:if test="error != ''">
							
							<xsl:variable name="error_text">
								<xsl:choose>
									<xsl:when test="error = -1">Введен некорректный электронный адрес</xsl:when>
									<xsl:when test="error = -2">Пользователь с указанным электронным адресом зарегистрирован ранее</xsl:when>
									<xsl:when test="error = -3">Пользователь с указанным логином зарегистрирован ранее</xsl:when>
									<xsl:when test="error = -4">Заполните, пожалуйста, все обязательные параметры</xsl:when>
									<xsl:when test="error = -7">Введено неверное подтверждение пароля!</xsl:when>
									<xsl:otherwise></xsl:otherwise>
								</xsl:choose>
							</xsl:variable>
							
							<p style="color:#800000; font-weight: bold;">
								<xsl:value-of select="$error_text"/>
							</p>
						</xsl:if>
						
						<p>
							<a href="/users/registration/" onClick="HideShow('first', 'second'); return false;" title="">Заполнить форму регистрации &#8594;</a>
						</p>
					</div>
					
					
					
					<div id="second" style="display: none;">
						
						<p class="title">Регистрация нового клиента</p>
						
						<p style="color: #707070">
						Поля, отмеченные <span class="red_star" style="position: relative; top: 6px;"> *</span>, обязательны для заполнения.
						</p>
						
						<form name="registration" method="POST">
							<table>
								<tr>
									<td>Логин:</td>
									<td>
										<input type="text" size="30" name="site_users_login" value="{site_users_login}"/>
									</td>
									<td class="red_star"> *</td>
								</tr>
								<tr>
									<td>Пароль:</td>
									<td>
										<input type="password" size="30" name="site_users_password" value=""/>
									</td>
									<td class="red_star"> *</td>
								</tr>
								<tr>
									<td>Повтор пароля:</td>
									<td>
										<input type="password" size="30" name="site_users_password_retry" value=""/>
									</td>
									<td class="red_star"> *</td>
								</tr>
								<tr>
									<td>E-mail:</td>
									<td>
										<input type="text" size="30" name="site_users_email" value="{site_users_email}"/>
									</td>
									<td class="red_star"> *</td>
								</tr>
								<tr>
									<td>Фамилия:</td>
									<td>
										<input type="text" size="30" name="site_users_surname" value="{site_users_surname}"/>
									</td>
								</tr>
								<tr>
									<td>Имя:</td>
									<td>
										<input type="text" size="30" name="site_users_name" value="{site_users_name}"/>
									</td>
								</tr>
								<tr>
									<td>Отчество:</td>
									<td>
										<input type="text" size="30" name="site_users_patronymic" value="{site_users_patronymic}"/>
									</td>
								</tr>
								<tr>
									<td>Компания:</td>
									<td>
										<input type="text" size="30" name="site_users_company" value="{site_users_company}"/>
									</td>
								</tr>
								<tr>
									<td>Телефон:</td>
									<td>
										<input name="site_users_phone" type="text" value="{site_users_phone}" size="30"/>
									</td>
								</tr>
							</table>
							<div class="gray_button">
								<div>
									<input name="step1_2" value="Продолжить оформление заказа" type="submit"/>
								</div>
							</div>
						</form>
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>