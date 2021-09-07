<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<!-- ОтобразитьФорму -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/form" />
	</xsl:template>
	
	<xsl:template match="/form">
		<script type="text/javascript">
			$(document).ready(function() {
			$('#form<xsl:value-of select="@id" />').validate({
			focusInvalid: true,
			errorClass: "input_error",
			submitHandler: function(form) {
			var d = $(form).serialize();
			$.showXslTemplate('/callback2/', <xsl:value-of select="@id" />, 287, 0, d);
			}
			})
			});
		</script>
		<div id="callBox{@id}" class="callBox">
			<div class="callBox-content">
				<div class="close" onclick="return $.modalClose2(this)"></div>
				<div class="h1"><xsl:value-of select="name" /></div>
				
				<div class="modal-form">
					<xsl:choose>
						<xsl:when test="success/node() and success = 1">
							<xsl:choose>
								<xsl:when test="@id =3">
									<p>Спасибо! Заявка отправлена, при появлении товара в наличии мы обязательно уведомим Вас.</p>
								</xsl:when>
								<xsl:otherwise>
								<div class="form-message-success"><p>Спасибо! Заявка отправлена, в течении 10 - 15 мин с Вами свяжется наш менеджер.</p></div>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<!-- Выводим ошибку (error), если она была передана через внешний параметр -->
								<xsl:when test="error != ''">
									<div id="error">
										<xsl:value-of disable-output-escaping="yes" select="error" />
									</div>
								</xsl:when>
								<xsl:when test="errorId/node()">
									<div id="error">
										<xsl:choose>
											<xsl:when test="errorId = 0">
												Вы неверно ввели число подтверждения отправки формы!
											</xsl:when>
											<xsl:when test="errorId = 1">
												Заполните все обязательные поля!
											</xsl:when>
											<xsl:when test="errorId = 2">
												Прошло слишком мало времени с момента последней отправки Вами формы!
											</xsl:when>
										</xsl:choose>
									</div>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of disable-output-escaping="yes" select="description" />
								</xsl:otherwise>
							</xsl:choose>
							
							<form name="form{@id}" id="form{@id}" class="validate" method="post" enctype="multipart/form-data">
								
								<!-- Вывод разделов формы 0-го уровня -->
								<xsl:apply-templates select="form_field_dir" />
								
								<!-- Вывод списка полей формы 0-го уровня -->
								<xsl:apply-templates select="form_field" />
								
								<!-- Код подтверждения -->
								<xsl:if test="captcha_id != 0">
									<div class="row">
										<div class="caption"></div>
										<div class="field">
											
											<img id="formCaptcha_{/form/@id}_{/form/captcha_id}" src="/captcha.php?id={captcha_id}&amp;height=30&amp;width=100" class="captcha" name="captcha" />
											
											<div class="captcha">
												<img src="/images/refresh.png" /> <span onclick="$('#formCaptcha_{/form/@id}_{/form/captcha_id}').updateCaptcha('{/form/captcha_id}', 30); return false">Показать другое число</span>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="caption">
									Контрольное число<sup><font color="red">*</font></sup>
										</div>
										<div class="field">
											<input type="hidden" name="captcha_id" value="{/form/captcha_id}"/>
											<input type="text" name="captcha" size="15" class="required" minlength="4" title="Введите число, которое указано выше."/>
										</div>
									</div>
								</xsl:if>
								
								<div class="row">
									<div class="field">
										<input name="{button_name}" value="{button_value}" type="submit" class="button"/>
									</div>
								</div>
							</form>
							
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template match="form_field_dir">
		<fieldset class="maillist_fieldset">
			<legend><xsl:value-of select="name" /></legend>
			
			<!-- Вывод списка полей формы -->
			<xsl:apply-templates select="form_field" />
			
			<!-- Вывод разделов формы -->
			<xsl:apply-templates select="form_field_dir" />
		</fieldset>
	</xsl:template>
	
	<xsl:template match="form_field">
		<!-- Не скрытое поле и не надпись -->
		<xsl:if test="type != 7 and type != 8">
			<xsl:variable name="caption">
				<xsl:value-of select="caption" />
				<xsl:if test="obligatory = 1">
					<xsl:text> *</xsl:text>
				</xsl:if>
			</xsl:variable>
			<div class="row">
				<div class="field">
					<!-- Текстовые поля -->
					<xsl:if test="type = 0 or type = 1 or type = 2">
						<input type="text" name="{name}" value="{value}" size="{size}" placeholder="{$caption}" class="input__text">
							<xsl:choose>
								<!-- Поле для ввода пароля -->
								<xsl:when test="type = 1">
									<xsl:attribute name="type">password</xsl:attribute>
								</xsl:when>
								<!-- Поле загрузки файла -->
								<xsl:when test="type = 2">
									<xsl:attribute name="type">file</xsl:attribute>
								</xsl:when>
								<!-- Текстовое поле -->
								<xsl:otherwise>
									<xsl:attribute name="type">text</xsl:attribute>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:if test="obligatory = 1">
								<xsl:attribute name="class">input__text required</xsl:attribute>
								<xsl:attribute name="minlength">1</xsl:attribute>
								<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
							</xsl:if>
							<xsl:if test="name = 'phone'">
								<xsl:attribute name="type">tel</xsl:attribute>
							</xsl:if>
						</input>
						<xsl:if test="name = 'phone'">
							<script type="text/javascript">
								<xsl:comment>
									<xsl:text disable-output-escaping="yes">
										<![CDATA[
										$(document).ready(function() {
										$("input[type='tel']").mask("+7 (999) 999-99-99", {placeholder:"_"});
										});
										]]>
									</xsl:text>
								</xsl:comment>
							</script>
						</xsl:if>
					</xsl:if>
					
					<!-- Радиокнопки -->
					<xsl:if test="type = 3 or type = 9">
						<xsl:apply-templates select="list/list_item" />
						<label class="input_error" for="{name}" style="display: none">Выберите, пожалуйста, значение.</label>
					</xsl:if>
					
					<!-- Checkbox -->
					<xsl:if test="type = 4">
						<input type="checkbox" name="{name}">
							<xsl:if test="checked = 1 or value = 1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
					</xsl:if>
					
					<!-- Textarea -->
					<xsl:if test="type = 5">
						<textarea name="{name}" cols="{cols}" rows="{rows}" wrap="off" class="textarea">
							<xsl:if test="obligatory = 1">
								<xsl:attribute name="class">required</xsl:attribute>
								<xsl:attribute name="minlength">1</xsl:attribute>
								<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
							</xsl:if>
							<xsl:if test="/form/shop_item/*">
								<xsl:value-of select="/form/shop_item/name" />
								<xsl:text>  </xsl:text>
							</xsl:if>
							<xsl:value-of select="value" />
						</textarea>
					</xsl:if>
					
					<!-- Список -->
					<xsl:if test="type = 6">
						<select name="{name}">
							<xsl:if test="obligatory = 1">
								<xsl:attribute name="class">required</xsl:attribute>
								<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
							</xsl:if>
							<option value="">...</option>
							<xsl:apply-templates select="list/list_item" />
						</select>
					</xsl:if>
				</div>
			</div>
		</xsl:if>
		
		<!-- скрытое поле -->
		<xsl:if test="type = 7">
			<input type="hidden" name="{name}" value="{value}" />
		</xsl:if>
		
		<!-- Надпись -->
		<xsl:if test="type = 8">
			<div class="row">
				<div class="caption"></div>
				<div class="field">
					<strong><xsl:value-of select="caption" /></strong>
				</div>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Формируем радиогруппу или выпадающий список -->
	<xsl:template match="list/list_item">
		<xsl:choose>
			<xsl:when test="../../type = 3">
				<input id="{../../name}_{@id}" type="radio" name="{../../name}" value="{value}">
					<xsl:if test="value = ../../value">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
					<xsl:if test="../../obligatory = 1">
						<xsl:attribute name="class">required</xsl:attribute>
						<xsl:attribute name="minlength">1</xsl:attribute>
						<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
					</xsl:if>
			</input><xsl:text> </xsl:text>
				<label for="{../../name}_{@id}"><xsl:value-of disable-output-escaping="yes" select="value" /></label>
				<br/>
			</xsl:when>
			<xsl:when test="../../type = 6">
				<option value="{value}">
					<xsl:if test="value = ../../value">
						<xsl:attribute name="selected">selected</xsl:attribute>
					</xsl:if>
					<xsl:value-of disable-output-escaping="yes" select="value" />
				</option>
			</xsl:when>
			<xsl:when test="../../type = 9">
				<xsl:variable name="currentValue" select="@id" />
				<input id="{../../name}_{@id}" type="checkbox" name="{../../name}_{@id}" value="{value}">
					<xsl:if test="../../values[value=$currentValue]/node()">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
					<xsl:if test="../../obligatory = 1">
						<xsl:attribute name="class">required</xsl:attribute>
						<xsl:attribute name="minlength">1</xsl:attribute>
						<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
					</xsl:if>
			</input><xsl:text> </xsl:text>
				<label for="{../../name}_{@id}"><xsl:value-of disable-output-escaping="yes" select="value" /></label>
				<br/>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	
</xsl:stylesheet>