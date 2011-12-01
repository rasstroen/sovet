<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="module[@name='banners' and @action='show']" mode="p-module">
		<xsl:apply-templates select="banner" mode="p-banner-show-item"/>
	</xsl:template>
	<!-- banner item -->
	<xsl:template match="*" mode="p-banner-show-item">
		<script type="text/javascript">
			<xsl:text>swfobject.embedSWF("</xsl:text>
			<xsl:value-of select="@file"/>
			<xsl:text>", "</xsl:text>
			<xsl:text>banner</xsl:text>
			<xsl:value-of select="@id"/>
			<xsl:text>", "</xsl:text>
			<xsl:value-of select="@width"/>
			<xsl:text>", "</xsl:text>
			<xsl:value-of select="@height"/>
			<xsl:text>", "9.0.0");</xsl:text>
		</script>
		<div class="banner_item" id="banner{@id}">
			<xsl:text>Loading banner</xsl:text>
		</div>
	</xsl:template>
</xsl:stylesheet>
