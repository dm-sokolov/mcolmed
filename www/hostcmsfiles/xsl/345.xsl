<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<xsl:template match="site">
		<nav class="navbar navbar-expand-lg ">
			<button class="navbar-toggler w-100 text-center" type="button" data-toggle="collapse" data-target="#navbarTogglerTopMenu" aria-controls="navbarTogglerTopMenu" aria-expanded="false" aria-label="Toggle navigation">
			<i class="fa fa-bars" aria-hidden="true"></i> <span class="text-uppercase font-weight-bold">Меню</span>
			</button>
			<div class="collapse navbar-collapse" id="navbarTogglerTopMenu">
				<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
					<xsl:apply-templates select="structure[show=1]" />
				</ul>
				<form action="/search/" class="form-inline my-2 my-lg-0">
					<input class="form-control mr-sm-2" type="text" name="text" placeholder="Поиск" aria-label="Search" />
				</form>
			</div>
		</nav>
	</xsl:template>
	
	<!-- Запишем в константу ID структуры, данные для которой будут выводиться пользователю -->
	<xsl:variable name="current_structure_id" select="/site/current_structure_id"/>
	
	<xsl:template match="structure">
		
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
		
		<xsl:variable name="active">
			<xsl:if test="$current_structure_id = @id or count(.//structure[@id=$current_structure_id]) = 1"> active</xsl:if>
		</xsl:variable>
		
		<xsl:variable name="dropdown">
			<xsl:if test="structure[show=1]"> dropdown</xsl:if>
			<xsl:if  test="informationsystem_item[show=1]">dropdown</xsl:if>
		</xsl:variable>
		
		<li class="nav-item text-center text-sm-left{$active}{$dropdown}">
			<a class="nav-link" href="{$link}" title="{name}">
				<xsl:if test="structure[show=1]">
					<xsl:attribute name="class">nav-link dropdown-toggle</xsl:attribute>
					<xsl:attribute name="data-toggle">dropdown</xsl:attribute>
					<xsl:attribute name="role">button</xsl:attribute>
					<xsl:attribute name="aria-haspopup">true</xsl:attribute>
					<xsl:attribute name="aria-expanded">false</xsl:attribute>
					<!--<xsl:attribute name="onclick">location.href = this.href</xsl:attribute>-->
				</xsl:if>
				<xsl:value-of select="name" />
			</a>
			<xsl:if test="structure[show=1]">
				<div class="dropdown-menu">
					<xsl:apply-templates select="structure[show=1]" mode="submenu" />
				</div>
			</xsl:if>
			<xsl:if test="informationsystem_item[show=1]">
				<div class="dropdown-menu">
					<xsl:apply-templates select="informationsystem_item[show=1]" mode="submenu" />
				</div>
			</xsl:if>
		</li>
	</xsl:template>
	
	<xsl:template match="structure" mode="submenu">
		
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
		
		<a class="dropdown-item text-center text-sm-left" href="{$link}">
			<xsl:value-of select="name" />
		</a>
	</xsl:template>
	
	<xsl:template match="informationsystem_item" mode="submenu">
		
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
		
		<a class="dropdown-item text-center text-sm-left" href="{$link}">
			<xsl:value-of select="name" />
		</a>
	</xsl:template>
	
</xsl:stylesheet>