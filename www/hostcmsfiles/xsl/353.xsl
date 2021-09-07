<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- СписокПрайсNEW -->
	<xsl:variable name="group" select="/informationsystem/group"/>
	<xsl:variable name="city">
		<xsl:if test="/informationsystem/site_id = 1"> в Екатеринбурге</xsl:if>
		<xsl:if test="/informationsystem/site_id = 8"> в Нижней Туре</xsl:if>
		<xsl:if test="/informationsystem/site_id = 7"> в Североуральске</xsl:if>
		<xsl:if test="/informationsystem/site_id = 6"> в Краснотурьинске</xsl:if>
		<xsl:if test="/informationsystem/site_id = 5"> в Серове</xsl:if>
		<xsl:if test="/informationsystem/site_id = 4"> в Нижнем Тагиле</xsl:if>
		<xsl:if test="/informationsystem/site_id = 13"> в Асбесте</xsl:if>
	</xsl:variable>
	
	<xsl:template match="/">
		<xsl:apply-templates select="informationsystem"/>
	</xsl:template>
	
	<xsl:variable name="n" select="number(3)"/>
	
	<xsl:template match="informationsystem">
		
		<!-- Если в находимся корне - выводим название информационной системы -->
		<xsl:choose>
			<xsl:when test="$group = 0">
				<h1><xsl:value-of select="name"/><xsl:value-of select="$city" /></h1>
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
		<!--xsl:if test="count(tag) = 0 and count(.//informationsystem_group[parent_id=$group]) &gt; 0">
		<div class="group_list">
			<ul><xsl:apply-templates select=".//informationsystem_group[parent_id=$group]" /></ul>
		</div>
	</xsl:if-->
	
	<!-- Отображение записи информационной системы -->
	<div id="accordion" role="tablist">
		<xsl:apply-templates select="informationsystem_group[parent_id = 0]"/>
	</div>
	
</xsl:template>

<!-- Шаблон выводит ссылки подгруппы информационного элемента -->
<xsl:template match="informationsystem_group">
	
	<xsl:variable name="current_group_id" select="@id" />
	
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0"><xsl:value-of select="name"/></h5>
		</div>
		
		<div class="card-body">
			<table class="table table-striped table-responsive">
				<thead>
					<tr>
						<td>Код</td>
						<td>Название медицинской услуги</td>
						<td>Стоимость, руб.</td>
					</tr>
				</thead>
				<tbody>
					<xsl:choose>
						<xsl:when test="informationsystem_group">
							<xsl:for-each select="informationsystem_group">
							<tr><td colspan="3"><xsl:value-of select="name"/></td></tr>
								<xsl:variable name="current_subgroup_id" select="@id" />
								<xsl:apply-templates select="/informationsystem/informationsystem_item[informationsystem_group_id = $current_subgroup_id]"/>
							</xsl:for-each>
						</xsl:when>
						<xsl:otherwise>
							<xsl:apply-templates select="/informationsystem/informationsystem_item[informationsystem_group_id = $current_group_id]"/>
						</xsl:otherwise>
						
					</xsl:choose>
				</tbody>
				
				
			</table>
		</div>
	</div>
	
</xsl:template>



<!-- Шаблон вывода информационного элемента -->
<xsl:template match="informationsystem_item">
	<tr>
		<td><xsl:value-of disable-output-escaping="yes" select="property_value[tag_name='kod_uslugi']/value" /></td>
		<td><xsl:value-of disable-output-escaping="yes" select="name"/></td>
		<td><xsl:value-of disable-output-escaping="yes" select="description"/></td>
	</tr>
</xsl:template>

</xsl:stylesheet>