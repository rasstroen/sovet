<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="module[@name='features' and @action='new']" mode="p-module">
    <xsl:apply-templates select="." mode="p-feature-form"/>
	</xsl:template>

	<xsl:template match="module[@name='features' and @action='edit']" mode="p-module">
    <xsl:apply-templates select="feature" mode="p-feature-form">
      <xsl:with-param select="groups" name="groups"/>
    </xsl:apply-templates>
	</xsl:template>

  <xsl:template match="*" mode="p-feature-form">
    <xsl:param select="groups" name="groups"></xsl:param>
		<form method="post" action="">
			<input type="hidden" name="writemodule" value="FeaturesWriteModule" />
      <input type="hidden" name="id" value="{@id}" />
      <input type="hidden" value="{@file_modify}" name="file_modify" />
			<div class="form-group">
				<h2>Добавление теста</h2>
        <xsl:apply-templates select="." mode="h-field-input">
          <xsl:with-param select="'title'" name="name"/>
          <xsl:with-param select="'Название теста'" name="label"/>
        </xsl:apply-templates>
				<div class="form-field">
					<label>Группа</label>
          <select name="group_id">
            <xsl:apply-templates select="$groups/item" mode="p-feature-group-option">
              <xsl:with-param select="@group_id" name="group_id"/>
            </xsl:apply-templates>
          </select>
				</div>
				<div class="form-field description">
					<label>Текст файла</label>
          <textarea name="description"><xsl:value-of select="@description"/></textarea>
          <div id="description"/>
				</div>
				<div class="form-field">
					<label>Путь до файла (относительно папки features/)</label>
          <input name="filepath" value="{@filepath}"/>
				</div>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить информацию"/>
			</div>
		</form>
  </xsl:template>

  <xsl:template match="*" mode="p-feature-group-option">
    <xsl:param select="group_id" name="group_id"/>
    <option value="{@id}">
      <xsl:if test="@id=$group_id or @id=&page;/variables/@group_id"><xsl:attribute name="selected" select="'selected'"/></xsl:if>
      <xsl:value-of select="@title"/>
    </option>
  </xsl:template>

	<xsl:template match="*" mode="p-feature-groups">
    <xsl:apply-templates select="item" mode="p-feature-group-list-item"/>
	</xsl:template>
	
	<xsl:template match="*" mode="p-feature-group-list-item">
    <div class="p-feature-group" id="{@id}">
      <h2 class="p-feature-group-title">
        <em class="p-feature-group-show"><a href="#"><xsl:value-of select="@title"/></a></em>
        <em class="p-feature-group-add_feature"><a href="{&prefix;}features/new?group_id={@id}">+1</a></em>
        <xsl:if test="@path_edit">
          <em class="p-feature-group-edit"><a href="{@path_edit}">ред.</a></em>
          <em class="p-feature-group-delete"><a href="#">x</a></em>
        </xsl:if>
      </h2>
      <table class="p-feature-group-table">
        <xsl:apply-templates select="features" mode="p-feature-list" />		
      </table>
    </div>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-show">
		<ul>
			<xsl:apply-templates select="." mode="p-feature-item" />
		</ul>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-list">
		<xsl:apply-templates select="item" mode="p-feature-list-item" />
	</xsl:template>

	<xsl:template match="*" mode="p-feature-list-item">
    <tr class="p-feature-list {@status_description}" id="{@id}">
			<td class="p-feature-title">
        <a href="{@path}" class="show-feature-description"><xsl:value-of select="@title"/></a>
        <a href="{@path_edit}" class="p-feature-list-edit" >ред.</a>
        <a href="#" class="p-feature-list-delete" >x</a>
        <div class="p-feature-description">
          <xsl:value-of select="@description"/>
        </div>
			</td>
			<td class="p-feature-last_run">
        <xsl:call-template name="helpers-abbr-time">
          <xsl:with-param select="@last_run" name="time"/>
        </xsl:call-template>
			</td>
			<td class="p-feature-control">
        <a href="#" class="pause-feature">=</a>
        <a href="#" class="run-feature">→</a>
        <noscript>
          <form method="post">
            <input type="hidden" value="FeaturesWriteModule" name="writemodule" />
            <input type="hidden" value="run" name="action" />
            <input type="hidden" value="{@id}" name="id" />
	    <input type="hidden" value="{@file_modify}" name="file_modify" />
            <input type="submit" value="run" />
          </form>
        </noscript>
      </td>
		</tr>
	</xsl:template>

	<xsl:template match="*" mode="p-feature-item">
		<tr>
			<td><a href="{@path}"><xsl:value-of select="@title"/></a></td>
			<td><xsl:value-of select="@status"/></td>
			<td><xsl:value-of select="@filepath"/></td>
			<td>
        <xsl:call-template name="helpers-abbr-time">
          <xsl:with-param select="@last_run" name="time"/>
        </xsl:call-template>
			</td>
		</tr>
		<div>
			<h3>описание теста</h3>
			<pre>
			<xsl:value-of select="@description" disable-output-escaping="yes"/>
			</pre>
		</div>
		<div>
			<h3>последний результат тестирования</h3>
			<pre>
				<xsl:value-of select="@last_message" disable-output-escaping="yes"/>
			</pre>
		</div>
	</xsl:template>

</xsl:stylesheet>
