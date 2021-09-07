<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml" />
	
	<!-- ФормаОтзывАяксNEW -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem" />
	</xsl:template>
	
	<xsl:template match="informationsystem">
		<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<script type="text/javascript">
				$(function() {
    /*dataLayer.push({'event': 'form<xsl:value-of select="@id" />.openMS'});
    $('#informationsystem<xsl:value-of select="@id" /> .btn-primary').on('click',function(a) {
    dataLayer.push({'event': 'form<xsl:value-of select="@id" />.sendMS'});
    });*/
    $('#informationsystem<xsl:value-of select="@id" />').validate({
    rules: {
        name: "email",
        email: {
            required: true,
            email: true
        }
    },
    onfocusout: function(element) { 
        // if ($(element).attr("name") == "email") {
        //     var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i
        //     var value = $(element).val()
        //     if 
        // }
        $(element).valid(); 
    },
    errorClass: "input_error",
    submitHandler: function(form) {
    $(".modal, .modal-backdrop").remove();
    var d = $(form).serialize();
    setTimeout(function() {
    $.showXslTemplate('<xsl:value-of select="url" />', <xsl:value-of select="@id" />, 321, d);
    }, 500);
    /*yaCounter20420221.reachGoal('SEND_INFORMATIONSYSTEM_<xsl:value-of select="@id" />');*/
    /*dataLayer.push({'event': 'form<xsl:value-of select="@id" />.successMS'});*/
    }
    });
    });
			</script>
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<form name="informationsystem{@id}" id="informationsystem{@id}" class="validate" method="post">
						<div class="modal-body">
							<button type="button" class="modal__close" data-dismiss="modal">
								<svg height="20px" viewBox="0 0 329.26933 329" width="20px" xmlns="http://www.w3.org/2000/svg"><path d="m194.800781 164.769531 128.210938-128.214843c8.34375-8.339844 8.34375-21.824219 0-30.164063-8.339844-8.339844-21.824219-8.339844-30.164063 0l-128.214844 128.214844-128.210937-128.214844c-8.34375-8.339844-21.824219-8.339844-30.164063 0-8.34375 8.339844-8.34375 21.824219 0 30.164063l128.210938 128.214843-128.210938 128.214844c-8.34375 8.339844-8.34375 21.824219 0 30.164063 4.15625 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921875-2.089844 15.082031-6.25l128.210937-128.214844 128.214844 128.214844c4.160156 4.160156 9.621094 6.25 15.082032 6.25 5.460937 0 10.921874-2.089844 15.082031-6.25 8.34375-8.339844 8.34375-21.824219 0-30.164063zm0 0"/></svg>
							</button>
							<div class="h4 mb-4 text-dark">Оставить отзыв</div>
							<xsl:choose>
								<xsl:when test="message/node()">
									<div class="text-center">
										<xsl:value-of disable-output-escaping="yes" select="message"/>
									</div>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<xsl:when test="error/node()">
											<div class="text-center">
												<xsl:value-of select="error"/>
											</div>
										</xsl:when>
										<xsl:otherwise>
											<div><xsl:value-of disable-output-escaping="yes" select="description"/></div>
										</xsl:otherwise>
									</xsl:choose>
									<div class="form-row">
										<div class="form-group col-md-6">
											<input type="text" size="70" name="author" class="form-control required" value="{/informationsystem/adding_item/author}" title="Напишите Ваше имя" placeholder="" />
											
											<label class="absolute-label" for="author"> *Как к Вам обращаться? </label>
										</div>
										<div class="form-group col-md-6">
											<input type="text" size="70" name="email" id="mailValidation" class="form-control required" value="{/informationsystem/adding_item/email}" title="Введите корректный Email" placeholder="" />
											<label class="absolute-label" for="email"> *Ваш Email </label>
										</div>
										<script type="text/javascript">
						
					</script>
									</div>
									<div class="form-group relative">
										<textarea  name="text" cols="68" rows="5" wrap="off" placeholder="" class="form-control required" title="Введите Ваше сообщение"></textarea>
											<label class="absolute-label" for="email"> *Ваш отзыв </label>
									</div><input type="text" class="autofocus" autofocus="autofocus" style="display:none" />
									<div class="form-group">
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
							<xsl:if test="not(message)">
								<button class="btn btn-primary autofocus-btn" type="submit" name="submit_question" value="submit_question" id="reviewBtn">Отправить отзыв</button>
							</xsl:if>
						</div>
					</form>
					<script type="text/javascript">
						<xsl:comment>
							<xsl:text disable-output-escaping="yes">
								<![CDATA[
								$(document).ready(function() {
								$("input[type='tel']").mask("+7 (999) 999-99-99", {placeholder:"_", completed: function() {
								$(this).removeClass("input_error")
								$(this).next(".input_error").hide()
								}, changed: function () {
									console.log($(this))
								}
								});
								$(".autofocus-btn").each(function () {
									$(this).click(function() {
										$(".autofocus").focus()
									})
								})
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
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>