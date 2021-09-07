<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="/site">
		<!-- Выбираем узлы структуры первого уровня -->
		<xsl:apply-templates select="structure[show=1]"/>
	</xsl:template>
	
	<!-- Запишем в константу ID структуры, данные для которой будут выводиться пользователю -->
	<xsl:variable name="current_structure_id" select="/site/current_structure_id"/>
	
	<xsl:template match="structure">
		<div class="menu_item">
			<div class="inner_menu_item ">
				<!-- Определяем адрес ссылки -->
				<xsl:variable name="link">
					<xsl:choose>
						<!-- Если внешняя ссылка -->
						<xsl:when test="url != ''">
							<xsl:value-of disable-output-escaping="yes" select="url"/>
						</xsl:when>
						<!-- Иначе если внутренняя ссылка -->
						<xsl:otherwise>
							<xsl:value-of disable-output-escaping="yes" select="link"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<!-- Ссылка на пункт меню -->
				<a href="{$link}" title="{name}" style="background-image: url('/images/site15/but1.jpg')" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="structure"><xsl:value-of select="name"/></a>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>