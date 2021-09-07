<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ВыводЕдиницыИнформационнойСистемы  -->
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem/informationsystem_item">
		
		<!--h1><xsl:value-of select="name"/></h1-->
	
	<!-- Выводим сообщение -->
	<xsl:if test="/informationsystem/message/node()">
		<xsl:value-of disable-output-escaping="yes" select="/informationsystem/message"/>
	</xsl:if>
	
	<!-- Фотогафия к информационному элементу -->
	<xsl:if test="image_small!=''">
		<!-- Проверяем задан ли путь к файлу большого изображения -->
		<xsl:choose>
			<xsl:when test="image_large!=''">
				<div id="gallery">
					<a href="{dir}{image_large}" target="_blank" data-fancybox="">
						<img src="{dir}{image_small}" align="left" class="mr-4 mb-4" />
					</a>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<img src="{dir}{image_small}" align="left" class="mr-4 mb-4" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
	
	<!-- Текст информационного элемента -->
	<xsl:choose>
		<xsl:when test="text =''">
			<div>
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</div>
		</xsl:when>
		<xsl:otherwise>
			<div>
				<xsl:value-of disable-output-escaping="yes" select="text"/>
			</div>
		</xsl:otherwise>
	</xsl:choose>
	
	<p class="tags">
		<!-- Дата информационного элемента -->
		<xsl:value-of select="date"/>, <span hostcms:id="{@id}" hostcms:field="showed" hostcms:entity="informationsystem_item"><xsl:value-of select="showed"/></span>
		<xsl:text> </xsl:text>
		<xsl:call-template name="declension">
			<xsl:with-param name="number" select="showed"/>
	</xsl:call-template><xsl:text>. </xsl:text>
	</p>
</xsl:template>

<!-- Склонение после числительных -->
<xsl:template name="declension">
	
	<xsl:param name="number" select="number"/>
	
	<!-- Именительный падеж -->
<xsl:variable name="nominative"><xsl:text>просмотр</xsl:text></xsl:variable>
	
	<!-- Родительный падеж, единственное число -->
<xsl:variable name="genitive_singular"><xsl:text>просмотра</xsl:text></xsl:variable>
	
<xsl:variable name="genitive_plural"><xsl:text>просмотров</xsl:text></xsl:variable>
	<xsl:variable name="last_digit"><xsl:value-of select="$number mod 10"/></xsl:variable>
	<xsl:variable name="last_two_digits"><xsl:value-of select="$number mod 100"/></xsl:variable>
	
	<xsl:choose>
		<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
			<xsl:value-of select="$nominative"/>
		</xsl:when>
		<xsl:when test="$last_digit = 2 and $last_two_digits != 12
			or $last_digit = 3 and $last_two_digits != 13
			or $last_digit = 4 and $last_two_digits != 14">
			<xsl:value-of select="$genitive_singular"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$genitive_plural"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>
</xsl:stylesheet>