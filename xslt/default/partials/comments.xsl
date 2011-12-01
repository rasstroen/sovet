<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "../entities.dtd">
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>

	<xsl:template match="module[@name='comments' and @action='list']" mode="p-module">
		<script>
		function show_add_comments(id){
			$('#comment_form_'+id).toggle();
		}
		</script>
		<xsl:variable name="doc_id" select="comments/@doc_id" />
		<xsl:variable name="table" select="comments/@table" />
		<xsl:apply-templates select="comments/item" mode="p-comments-list">
			<xsl:with-param name="module" select="." />
			<xsl:with-param name="table" select="$table" />
			<xsl:with-param name="doc_id" select="$doc_id" />
		</xsl:apply-templates>
		<xsl:if test="&access;/add_comments">
			<xsl:call-template name="comment_form">
				<xsl:with-param name="doc_id" select="$doc_id" />
				<xsl:with-param name="reply_to" select="0" />
				<xsl:with-param name="table" select="$table" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<xsl:template name="comment_form">
		<xsl:param name="doc_id" select="0" />
		<xsl:param name="reply_to" select="0" />
		<xsl:param name="table" select="0" />
		<xsl:if test="$reply_to = 0">
			<a href="javascript:void(0)" onclick="show_add_comments(0)">оставить комментарий</a>
		</xsl:if>
		<xsl:if test="$reply_to > 0">
			<a href="javascript:void(0)" onclick="show_add_comments({$reply_to})">ответить</a>
		</xsl:if>
		<div style="display:none" class="comment_left" id="comment_form_{$reply_to}">
			<form method="post">
				<input type="hidden" value="CommentsWriteModule" name="writemodule" />
				<input id="reply_to" type="hidden" value="{$reply_to}" name="reply_to" />
				<input id="table" type="hidden" value="{$table}" name="table" />
				<input type="hidden" value="{$doc_id}" name="doc_id" />
				<textarea name="comment"></textarea>
			
				<xsl:if test="$reply_to = 0">
					<input type="submit" value="оставить комментарий"/>
				</xsl:if>
				<xsl:if test="$reply_to > 0">
					<input type="submit" value="ответить"/>
				</xsl:if>
			</form>
		</div>
	</xsl:template>
	<!-- comments list -->
	<xsl:template match="*" mode="p-comments-list">
		<xsl:apply-templates select="./item" mode="comments-level">
			<xsl:with-param name="level" select="1" />
			<xsl:with-param name="module" />
		</xsl:apply-templates>
	</xsl:template>

	<!-- comments level -->
	<xsl:template match="*" mode="p-comments-list">
		<xsl:param name="level" select="1" />
		<xsl:param name="module" select="."/>
		<xsl:param name="table" select="0"/>
		<xsl:param name="doc_id" select="0"/>
		<xsl:for-each select=".">
			<xsl:variable name="item" select="." />
			<div class="comment_item" id="comment-{@id}" >
				<xsl:attribute name="class">
					<xsl:if test="@mod = 0">
						<xsl:text>comment_item mod</xsl:text>
					</xsl:if>
					<xsl:if test="@mod = 1">
						<xsl:text>comment_item</xsl:text>
					</xsl:if>
				</xsl:attribute>
				<xsl:attribute name="style">
					<xsl:text>margin-left:</xsl:text>
					<xsl:value-of select="$level*20" />
					<xsl:text>px;</xsl:text>
				</xsl:attribute>
				<a name="comment_{@id}"></a>
				<div class="avatar">
					<img src="{$module/users/item[@id = $item/@id_author]/@picture}"></img>
				</div>
				<div class="comment_time">
					<xsl:value-of select="$item/@date" />
				</div>
				<a onfocus="this.blur()" href="{$module/users/item[@id = $item/@id_author]/@path}">
					<div class="comment_nick">
						<xsl:value-of select="$module/users/item[@id = $item/@id_author]/@nickname" />
					</div>
				</a>
				<br class="clearme" />

				<div class="comment_text">
					<xsl:value-of select="$item/@comment" disable-output-escaping="yes"/>
				</div>
				<div class="comment_href">
					<a title="ссылка на комментарий" href="#comment_{$item/@id}">#</a>
				</div>
				<xsl:if test="@parent != 0">
					<div class="up_to_parent">
						<a title="Ответ на" href="#comment_{$item/@parent}">↑</a>
					</div>
				</xsl:if>
				<xsl:if test="&access;/add_comments">
					<xsl:call-template name="comment_form">
						<xsl:with-param name="doc_id" select="@doc_id" />
						<xsl:with-param name="reply_to" select="@id" />
						<xsl:with-param name="table" select="$table" />
					</xsl:call-template>
				</xsl:if>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
			<xsl:apply-templates select="item" mode="p-comments-list">
				<xsl:with-param name="level" select="$level + 1" />
				<xsl:with-param name="module" select="$module" />
				<xsl:with-param name="table" select="$table" />
				<xsl:with-param name="doc_id" select="$doc_id" />
			</xsl:apply-templates>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
