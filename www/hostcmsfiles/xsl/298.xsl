<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<!-- ОтобразитьФорму -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="form" />
	</xsl:template>
	
	<xsl:template match="form">
		<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<script type="text/javascript">
				$(function() {
				let sPage = location.href.replace(/https?:\/\//i, "");
				$('input[name="sPage"]').val(sPage);
				let siteID = <xsl:value-of select="site_id" />;
				/*dataLayer.push({'event': 'form<xsl:value-of select="@id" />.openMS'});
				$('#form<xsl:value-of select="@id" /> .btn-primary').on('click',function(a) {
				dataLayer.push({'event': 'form<xsl:value-of select="@id" />.sendMS'});
				});*/
				$('#form<xsl:value-of select="@id" />').validate({
				onfocusout: function(element) {
				// if ($(element).attr("name") == "email") {
				//     var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i
				//     var value = $(element).val()
				//     if
				// }
				$(element).valid();
				},
				focusInvalid: false,
				errorClass: "input_error",
				submitHandler: function(form) {
				$(".modal, .modal-backdrop").remove();
				let d = $(form).serialize();
				setTimeout(function() {
				$.showXslTemplate('/callback/', <xsl:value-of select="@id" />, 298, d);
				}, 500);
				
				if (siteID == 1) {
				yaCounter20420221.reachGoal('SEND_FORM_<xsl:value-of select="@id" />');
				var _tmr = window._tmr || (window._tmr = []);_tmr.push({ id: "3143268", type: "reachGoal", goal: "lead" });
				}
				else if (siteID == 8)  {
				yaCounter53914072.reachGoal('SEND_FORM_<xsl:value-of select="@id" />');
				}
				else if (siteID == 7)  {
				yaCounter53914477.reachGoal('SEND_FORM_<xsl:value-of select="@id" />');
				}
				else if (siteID == 6)  {
				yaCounter53914528.reachGoal('SEND_FORM_<xsl:value-of select="@id" />');
				}
				else if (siteID == 5)  {
				yaCounter53914564.reachGoal('SEND_FORM_<xsl:value-of select="@id" />');
				}
				else if (siteID == 4) {
				yaCounter53914231.reachGoal('SEND_FORM_<xsl:value-of select="@id" />');
				}
				
				/*dataLayer.push({'event': 'form<xsl:value-of select="@id" />.successMS'});*/
				}
				});
				});
				
			</script>
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form name="form{@id}" id="form{@id}" class="validate" method="post">
						<div class="modal-body">
							
							<button type="button" class="modal__close" data-dismiss="modal">
								<svg height="20px" viewBox="0 0 329.26933 329" width="20px" xmlns="http://www.w3.org/2000/svg"><path d="m194.800781 164.769531 128.210938-128.214843c8.34375-8.339844 8.34375-21.824219 0-30.164063-8.339844-8.339844-21.824219-8.339844-30.164063 0l-128.214844 128.214844-128.210937-128.214844c-8.34375-8.339844-21.824219-8.339844-30.164063 0-8.34375 8.339844-8.34375 21.824219 0 30.164063l128.210938 128.214843-128.210938 128.214844c-8.34375 8.339844-8.34375 21.824219 0 30.164063 4.15625 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921875-2.089844 15.082031-6.25l128.210937-128.214844 128.214844 128.214844c4.160156 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921874-2.089844 15.082031-6.25 8.34375-8.339844 8.34375-21.824219 0-30.164063zm0 0"/></svg>
							</button>
							<xsl:choose>
								<xsl:when test="success/node() and success = 1">
									<div><xsl:value-of disable-output-escaping="yes" select="substring-after(description, '&lt;!-- pagebreak --&gt;')" /></div>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<!-- Выводим ошибку (error), если она была передана через внешний параметр -->
										<xsl:when test="error != ''">
											<div id="error"><xsl:value-of disable-output-escaping="yes" select="error" /></div>
										</xsl:when>
										<xsl:when test="errorId/node()">
											<div id="error">
												<xsl:choose>
													<xsl:when test="errorId = 0">Вы неверно ввели число подтверждения отправки формы!</xsl:when>
													<xsl:when test="errorId = 1">Заполните все обязательные поля!</xsl:when>
													<xsl:when test="errorId = 2">Прошло слишком мало времени с момента последней отправки Вами формы!</xsl:when>
												</xsl:choose>
											</div>
										</xsl:when>
										<xsl:otherwise>
											<div><xsl:value-of disable-output-escaping="yes" select="substring-before(description, '&lt;!-- pagebreak --&gt;')" /></div>
										</xsl:otherwise>
									</xsl:choose>
									
									<xsl:apply-templates select="form_field" />
									<div class="">
										<small class="form-text text-danger mb-1">Поля, отмеченные * обязательны для заполнения.</small>
										<small class="form-text text-muted">
											<br />
										Нажимая кнопку "<xsl:value-of select="button_value" />", я подтверждаю, что даю свое согласие на обработку предоставленных мной данных в соответствии с <a href="/personalnie-dannie/">Политикой обработки персональных данных</a>
										</small>
									</div>
								</xsl:otherwise>
							</xsl:choose>
						</div>
						<div class="modal-footer">
							<xsl:if test="not(success)">
								<button type="submit" class="btn btn-primary btn-lg border-radius autofocus-btn" name="{button_name}" value="{button_value}"><xsl:value-of select="button_value" /></button>
							</xsl:if>
						</div><script type="text/javascript">
							<xsl:comment>
								<xsl:text disable-output-escaping="yes">
								</xsl:text>
							</xsl:comment>
						</script>
					</form>
				</div>
			</div>
		</div>
	</xsl:template>
	
	<xsl:template match="form_field">
		<xsl:variable name="placeholder">
			<xsl:text> *</xsl:text>	<xsl:value-of select="value" />
			<xsl:if test="obligatory = 1"></xsl:if>
		</xsl:variable>
		<xsl:variable name="error-mes">
			
		<xsl:if test="name = 'name'"> <xsl:text>Напишите имя</xsl:text> </xsl:if>
		<xsl:if test="name = 'phone'"> <xsl:text>Напишите номер телефона</xsl:text> </xsl:if>
		<xsl:if test="name = 'vrach'"> <xsl:text>Выберите врача</xsl:text> </xsl:if>
		</xsl:variable>
		<!-- Не скрытое поле и не надпись -->
		<xsl:if test="type != 7 and type != 8">
			
			<div class="form-group relative">
				<!-- Текстовые поля -->
				<xsl:if test="type = 0 or type = 1 or type = 2">
					<input type="text" name="{name}" size="{size}"  class="form-control">
			
						
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
							<xsl:attribute name="class">form-control required</xsl:attribute>
							<xsl:attribute name="minlength">1</xsl:attribute>
							<xsl:attribute name="title"><xsl:choose>
									<xsl:when test="name = 'name'">Напишите имя</xsl:when>
									<xsl:when test="name = 'phone'">Напишите номер телефона</xsl:when>
									<xsl:when test="name = 'vrach'">Выберите врача</xsl:when>
							</xsl:choose></xsl:attribute>
						</xsl:if>
						<xsl:if test="name = 'phone'">
							<xsl:attribute name="type">tel</xsl:attribute>
						</xsl:if>
						<!--xsl:if test="name = 'docname'">
						<xsl:attribute name="value"><xsl:value-of select="/form/docname"/></xsl:attribute>
					</xsl:if-->
				</input><label class="absolute-label" for="{name}"><xsl:text> *</xsl:text>	<xsl:value-of select="value" /> </label>
				<xsl:if test="name = 'phone'">
					<script type="text/javascript">
						<xsl:comment>
							<xsl:text disable-output-escaping="yes">
								<![CDATA[
								$(document).ready(function() {
								$("input[type='tel']").mask("+7 (999) 999-99-99", {placeholder:"_", completed: function() {
									console.log($(this))
								$(this).removeClass("input_error")
								$(this).next(".input_error").hide()
								}
								});
								$(".form-control").each(function() {
		var input = $(this)
		$(this).on("blur", function() {
			if ($(this).val().length) {
				$(this).addClass("val")
			} else {
				$(this).removeClass("val")
			}
		})
	})
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
				<textarea name="{name}" cols="{cols}" rows="{rows}" wrap="off" placeholder="{$placeholder}" class="form-control">
					<xsl:if test="obligatory = 1">
						<xsl:attribute name="class">form-control required</xsl:attribute>
						<xsl:attribute name="minlength">1</xsl:attribute>
						<xsl:attribute name="title">Заполните поле <xsl:value-of select="value" /></xsl:attribute>
					</xsl:if>
				</textarea>
			</xsl:if>
			
			<!-- Список -->
			<xsl:if test="type = 6">
				<select name="{name}" class="form-control relative">
					<xsl:if test="obligatory = 1">
						<xsl:attribute name="class">form-control required</xsl:attribute>
						<xsl:attribute name="title"><xsl:choose>
								<xsl:when test="name = 'name'">Напишите имя</xsl:when>
								<xsl:when test="name = 'phone'">Напишите номер телефона</xsl:when>
								<xsl:when test="name = 'vrach'">Выберите врача</xsl:when>
						</xsl:choose></xsl:attribute>
					</xsl:if>
					<option value="">*Выберите врача</option>
					<xsl:apply-templates select="list/list_item" />
				</select>
			</xsl:if>
		</div>
	</xsl:if>
	
	<!-- скрытое поле -->
	<xsl:if test="type = 7">
		<input type="hidden" name="{name}" value="{value}" />
	</xsl:if>
	
	<!-- Надпись -->
	<xsl:if test="type = 8">
		<div class="form-group relative">
			<strong><xsl:value-of select="caption" /></strong>
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