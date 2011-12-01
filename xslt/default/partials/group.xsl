<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="module[@name='groups' and @action='edit']" mode="p-module">
		<xsl:apply-templates select="group" mode="p-group-form"/>
	</xsl:template>

	<xsl:template match="module[@name='groups' and @action='new']" mode="p-module">
		<xsl:apply-templates select="group" mode="p-group-form"/>
	</xsl:template>

	<xsl:template match="group" mode="p-group-form">
		<form method="post" action="">
			<input type="hidden" name="writemodule" value="GroupsWriteModule" />
			<input type="hidden" name="id" value="{@id}" />
			<div class="form-group">
				<h2>Редактирование группы</h2>
				<div class="form-field">
					<label>Название группы</label>
					<input name="title" value="{@title}"/>
				</div>
				<div class="form-field">
					<label>Папка</label>
					<input name="folder" value="{@folder}"/>
				</div>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить информацию"/>
			</div>
		</form>
	</xsl:template>

</xsl:stylesheet>
