<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
	<!-- NEW -->
	<!-- news new -->
	<xsl:template match="module[@name='blog' and @action='new']" mode="p-module">
		<xsl:apply-templates select="." mode="p-blog-form" />
	</xsl:template>
	<!-- news edit -->
	<xsl:template match="module[@name='blog' and @action='edit']" mode="p-module">
		<xsl:apply-templates select="blogpost" mode="p-blog-form" >
			<xsl:with-param name="h2" select="'Редактирование поста'" />
		</xsl:apply-templates>
	</xsl:template>
	<!-- news form -->
	<xsl:template match="*" mode="p-blog-form">
		<xsl:param name="h2" select="'Добавление поста'" />
		<form method="post" action="">
			<input type="hidden" name="writemodule" value="BlogsWriteModule" />
			<input type="hidden" name="id" value="{@id}" />
			<div class="form-group">
				<h2>
					<xsl:value-of select="$h2" />
				</h2>
				<div class="form-field">
					<label>Заголовок</label>
					<input name="title" value="{@title}" />
				</div>
				<div class="form-field description">
					<label>Текст</label>
					<textarea id="html" name="html">
						<xsl:value-of select="@html"/>
					</textarea>
				</div>
			</div>
			<div class="form-control">
				<input type="submit" value="Сохранить"/>
			</div>
		</form>
	</xsl:template>

	<!-- news item -->
	<xsl:template match="module[@name='news' and @action='show']" mode="p-module">
		<xsl:apply-templates select="newsitem " mode="p-news-show-item"/>
	</xsl:template>
	<!-- news item -->
	<xsl:template match="*" mode="p-news-show-item">

		<xsl:call-template name="adminEdit">
			<xsl:with-param name="document" select="." />
		</xsl:call-template>

		<div class="news_item_inner">
			<h2>
				<xsl:value-of select="@title" />sss
			</h2>
			<div class="date">
				<xsl:value-of select="@date" />
			</div>
			<div class="content">
				<xsl:value-of select="@html" disable-output-escaping="yes" />
			</div>
		</div>
	</xsl:template>

	<!-- LISTS -->
	<!-- news list with 2 columnss -->
	<xsl:template match="module[@name='news' and @action='list' and @mode='columns']" mode="p-module">
		<xsl:apply-templates select="news" mode="p-news-list-columns"/>
	</xsl:template>
	<!-- news list with 1 columnss -->
	<xsl:template match="module[@name='news' and @action='list' and not(@mode)]" mode="p-module">
		<xsl:apply-templates select="news" mode="p-news-list"/>
	</xsl:template>
	<!-- 2 columns -->
	<xsl:template match="*" mode="p-news-list-columns">
		<xsl:param name="pos" select="1" />
		<xsl:apply-templates select="." mode="p-news-list-columns-tr">
			<xsl:with-param name="item1" select="item[position() = $pos]"/>
			<xsl:with-param name="item2" select="item[position() = $pos+1]"/>
		</xsl:apply-templates>
		<xsl:if test="item[position() = $pos+2]">
			<xsl:apply-templates select="." mode="p-news-list-columns">
				<xsl:with-param name="pos" select="$pos+2"/>
			</xsl:apply-templates>
		</xsl:if>
	</xsl:template>
	<!-- one tr for column-->
	<xsl:template match="*" mode="p-news-list-columns-tr">
		<xsl:param name="item1"/>
		<xsl:param name="item2"/>
		<div class="news_item_left">
			<xsl:apply-templates select="$item1" mode="p-news-item"/>
		</div>
		<div class="news_item_right">
			<xsl:apply-templates select="$item2" mode="p-news-item"/>
		</div>
	</xsl:template>
	<!-- news item -->
	<xsl:template match="*" mode="p-news-item">
		<div>
			<div class="news_item">
				<div class="img">
					<a href="{@path}" title="{@title}" onfocus="this.blur()">
						<img border="0" alt="{@title}" src="{@image}" />
						<span class="comment_count">
							<xsl:value-of select="@comment_count" />
							<xsl:text>&nbsp;комментариев&nbsp;</xsl:text>
						</span>
					</a>
				</div>
				<div class="plank">
					<div class="title">
						<h2>
							<a href="{@path}">
								<xsl:value-of select="@title"/>
							</a>
						</h2>
					</div>
					<div class="date">
						<xsl:value-of select="@date"/>
					</div>
					<div class="anons">
						<xsl:value-of select="@anons" disable-output-escaping="yes"/>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
