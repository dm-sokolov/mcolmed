<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокПрайсNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:variable name="n" select="number(3)"/>
	
	<xsl:template match="informationsystem">
		
		<!-- Если в находимся корне - выводим название информационной системы -->
		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1><xsl:value-of select="name"/></h1>
				<!-- Описание выводится при отсутствии фильтрации по тэгам -->
				<xsl:if test="count(tag) = 0 and page = 0 and description != ''">
					<div><xsl:value-of disable-output-escaping="yes" select="description"/></div>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<h1><xsl:value-of select=".//informationsystem_group[@id=$group]/name"/></h1>
				
				<!-- Описание выводим только на первой странице -->
				<xsl:if test="page = 0 and .//informationsystem_group[@id=$group]/description != ''">
					<div><xsl:value-of disable-output-escaping="yes" select=".//informationsystem_group[@id=$group]/description"/></div>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
		
		<!-- Отображение подгрупп данной группы, только если подгруппы есть и не идет фильтра по меткам -->
		<xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
			<div class="group_list">
				<ul><xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" /></ul>
			</div>
		</xsl:if>
		
		<!-- Отображение записи информационной системы -->
		<div id="accordion" role="tablist">
			<xsl:apply-templates select="informationsystem_item"/>
		</div>
		
	</xsl:template>
	
	<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
	<xsl:template match="informationsystem_group">
		<li>
	<a href="{url}"><xsl:value-of select="name"/></a><xsl:text> </xsl:text><span class="count">(<xsl:value-of select="items_total_count"/>)</span>
		</li>
	</xsl:template>
	
	<!-- Шаблон вывода информационного элемента -->
	<xsl:template match="informationsystem_item">
		<div class="card">
			<div class="card-header" role="tab" id="heading{@id}">
				<h5 class="mb-0">
					<a data-toggle="collapse" href="#collapse{@id}" aria-expanded="true" aria-controls="collapse{@id}">
						<xsl:value-of select="name"/>
					</a>
				</h5>
			</div>
			<div id="collapse{@id}" class="collapse" role="tabpanel" aria-labelledby="heading{@id}" data-parent="#accordion">
				<div class="card-body"><xsl:value-of disable-output-escaping="yes" select="description"/></div>
			</div>
		</div>
	</xsl:template>
	
</xsl:stylesheet>