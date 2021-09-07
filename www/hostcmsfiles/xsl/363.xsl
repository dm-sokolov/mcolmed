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
				
				$('#form<xsl:value-of select="@id" />').validate({
				focusInvalid: true,
				errorClass: "input_error",
				submitHandler: function(form) {
				$(".modal, .modal-backdrop").remove();
				let d = $(form).serialize();
				setTimeout(function() {
				$.showXslTemplate('/callback/', <xsl:value-of select="@id" />, 363, d);
				}, 500);
				}
				});
				});
				
			</script>
			<div class="modal-dialog" role="document">
				
				<div id="formBox{@id}">
					<div class="blue-gradient sign-up-box p-4">
						
						
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
								
								<div class="h3 text-center">Отклик на вакансию</div>
							<div class="text-center mb-3"><a href="/personalnie-dannie/" target="_blank" class="">Политика обработки персональных данных</a></div>
								<form name="form{@id}" id="form{@id}" class="validate" action="./" method="post" enctype="multipart/form-data">
									<div class="form-row">
										<!-- Вывод разделов формы 0-го уровня -->
										<xsl:apply-templates select="form_field_dir" />
										
										<!-- Вывод списка полей формы 0-го уровня -->
										<xsl:apply-templates select="form_field" />
										
										<div class="col-sm-6 mx-auto" style="margin:0 auto;">
											<button name="{button_name}" value="{button_value}" type="submit" class="form-control btn btn-primary">
												<xsl:value-of disable-output-escaping="yes" select="button_value" /> <img src="/i/ico-subs.png" />
											</button>
										</div>
									</div>
								</form>
								
							</xsl:otherwise>
						</xsl:choose>
					</div>
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
			
			
			<div style="width:100%;margin-bottom:20px;">
				<xsl:variable name="name">
					<xsl:choose>
						<xsl:when test="description!=''">
							<xsl:value-of disable-output-escaping="yes" select="description" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="caption" />
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:if test="obligatory = 1">
						<xsl:text> *</xsl:text>
					</xsl:if>
				</xsl:variable>
				<!-- Текстовые поля -->
				<xsl:if test="type = 0 or type = 1 or type = 2 or type = 16 or type = 18">
					<input id="form_field_{@id}" type="text" name="{name}" value="{value}" size="{size}" class="form-control" placeholder="{$name}">
						<xsl:choose>
							<!-- Поле для ввода пароля -->
							<xsl:when test="type = 1">
								<xsl:attribute name="type">password</xsl:attribute>
							</xsl:when>
							<!-- Поле загрузки файла -->
							<xsl:when test="type = 2">
								<xsl:attribute name="type">file</xsl:attribute>
							</xsl:when>
							<xsl:when test="type = 16">
								<xsl:attribute name="type">email</xsl:attribute>
							</xsl:when>
							<xsl:when test="type = 18">
								<xsl:attribute name="type">tel</xsl:attribute>
							</xsl:when>
							<!-- Текстовое поле -->
							<xsl:otherwise>
								<xsl:attribute name="type">text</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="obligatory = 1">
							<xsl:attribute name="class">form-control required</xsl:attribute>
							<xsl:attribute name="minlength">1</xsl:attribute>
							<xsl:attribute name="title">Заполните поле <xsl:value-of select="$name" /></xsl:attribute>
						</xsl:if>
						<xsl:if test="name = 'vphone'">
							<xsl:attribute name="type">tel</xsl:attribute>
						</xsl:if>
					</input>
					
					<xsl:if test="name = 'vphone'">
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
				
				<!-- Список -->
				<xsl:if test="type = 6">
					<select name="{name}">
						<xsl:if test="obligatory = 1">
							<xsl:attribute name="class">form-control required</xsl:attribute>
							<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
						</xsl:if>
						<option value="">-- Выберите вакансию --</option>
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
			<div class="col-md-12">
				<strong><xsl:value-of select="caption" /></strong>
			</div>
		</xsl:if>
		
	</xsl:template>
	
	<!-- Формируем радиогруппу или выпадающий список -->
	<xsl:template match="list/list_item">
		<xsl:choose>
			<xsl:when test="../../type = 3">
				<input id="{../../name}_{@id}" type="radio" name="{../../name}" value="{value}" class="styled">
					<xsl:if test="value = ../../value">
						<xsl:attribute name="checked">checked</xsl:attribute>
					</xsl:if>
					<xsl:if test="../../obligatory = 1">
						<xsl:attribute name="class">form-control required</xsl:attribute>
						<xsl:attribute name="minlength">1</xsl:attribute>
						<xsl:attribute name="title">Заполните поле <xsl:value-of select="caption" /></xsl:attribute>
					</xsl:if>
			</input><xsl:text> </xsl:text>
			<label for="{../../name}_{@id}"><span></span><xsl:value-of disable-output-escaping="yes" select="value" /></label>
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