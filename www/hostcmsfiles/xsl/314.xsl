<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- ЕдиницыУслугиNEW -->
	<xsl:variable name="group" select="/informationsystem/group" />
	<xsl:variable name="count" select="count(/informationsystem//*[@id][parent_id = $group or informationsystem_group_id = $group])"/>
	<xsl:variable name="n" select="ceiling($count div 3)"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="/informationsystem/informationsystem_item"/>
	</xsl:template>
	
	<xsl:template match="/informationsystem/informationsystem_item">
		
		<xsl:choose>
			<xsl:when test="property_value[tag_name='alt-h1']/value !=''">
				<h1><xsl:value-of disable-output-escaping="yes" select="property_value[tag_name='alt-h1']/value"/></h1>
			</xsl:when>
			<xsl:otherwise>
				<h1><xsl:value-of select="name"/></h1>
			</xsl:otherwise>
		</xsl:choose>
		
		<xsl:if test="count(/informationsystem//*[@id][parent_id = $group or informationsystem_group_id = $group]) &gt; 0 and 1 = 0">
			
			<div class="gray-gradient mb-4">
				<div class="row">
					<div class="col-sm-4">
						<div class="padding-15">
							<xsl:apply-templates select="/informationsystem//*[@id][parent_id = $group or informationsystem_group_id = $group]" mode="category" />
						</div>
					</div>
				</div>
			</div>
			
		</xsl:if>
		
		<xsl:if test="description !=''">
			<div>
				<xsl:value-of disable-output-escaping="yes" select="description"/>
			</div>
		</xsl:if>
		
		<div>
			<xsl:value-of disable-output-escaping="yes" select="text"/>
		</div>
		
		<xsl:if test="property_value[tag_name='offers-left'] and property_value[tag_name='offers-left']/value != '' ">
			<input id="infosys-item-id" type="hidden" value="{@id}" />
			<xsl:variable name="offers-num" select="property_value[tag_name='offers-left']/value" />
			<script type="text/javascript">
				$(function() {
				if( $('*[class^="formBox"]').length) {
		$('*[class^="formBox"]').after('<div class="offers-left-text">Осталось предложений: <span class="offers-left"><xsl:value-of select="property_value[tag_name='offers-left']/value" /></span></div>');
				}
				});
			</script>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="*" mode="category">
		<div class="mb-1">
			<a href="{url}">
				<i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
				<xsl:text> </xsl:text>
				<xsl:value-of select="name"/>
			</a>
		</div>
		<xsl:if test="position() mod $n = 0 and position() != last()">
			<xsl:text disable-output-escaping="yes">
				&lt;/div&gt;
				&lt;/div&gt;
				&lt;div class="col-sm-4"&gt;
				&lt;div class="padding-15"&gt;
			</xsl:text>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>