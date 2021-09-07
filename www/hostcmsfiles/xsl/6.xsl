<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокУслугНаГлавной -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem"/>
	</xsl:template>
	
	<xsl:variable name="count" select="count(/informationsystem//informationsystem_group[parent_id=$group])"/>
	<xsl:variable name="n" select="ceiling($count div 3)"/>
	
	<xsl:template match="/informationsystem">
		
		<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<ul class="ls-none">
				<xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" mode="groups"/>
			</ul>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:variable name="current_group" select="/informationsystem/current_group"/>
	
	<xsl:template match="informationsystem_group" mode="groups">
		<li>
			<xsl:if test="$current_group = @id">
				<xsl:attribute name="class">kras</xsl:attribute>
			</xsl:if>
			<a href="{url}">
				<xsl:if test="property_value[tag_name = 'red']/value = 1">
					<xsl:attribute name="style">color:#BB2121;background-position:left -29px;font-weight:bold;</xsl:attribute>
				</xsl:if>
				<xsl:value-of select="name"/>
			</a>
		</li>
		<xsl:if test="position() mod $n = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/ul&gt;
				&lt;ul class="ls-none"&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>