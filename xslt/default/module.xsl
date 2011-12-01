<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<xsl:output omit-xml-declaration="yes"/>

	<xsl:template match="module">
		<xsl:apply-templates select="conditions/item[not(@mode='paging')]" mode="p-misc-condition"/>
		<div class="m-{@name}-{@action} module">
			<xsl:apply-templates select="." mode="p-module"/>
		</div>
		<xsl:apply-templates select="conditions/item[@mode='paging']" mode="p-misc-condition"/>
	</xsl:template>

</xsl:stylesheet>
