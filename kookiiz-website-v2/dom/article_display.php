<?php
	/**********************************************************
    Title: Article display
    Authors: Kookiiz Team
    Purpose: HTML code of the full article display tab
	***********************************************************/
?>
<div class="column single">
	<h5 id="article_title"></h5>
	<p>
		<span class="bold"><?php $Lang->p('ARTICLE_DISPLAY_TEXT', 0); ?></span>
		<span id="article_keywords" class="italic"></span>
	</p>
	<hr />
</div>
<div class="column medium left_side">
	<p id="article_text"></p>
</div>
<div class="column medium">
	<div id="article_pictures"></div>
</div>
<div id="article_comments_module" class="column single">
	<h5>
		<img class="icon25 book" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('ARTICLE_DISPLAY_TEXT', 2); ?>" />
		<span><?php $Lang->p('ARTICLE_DISPLAY_TEXT', 2); ?></span>
	</h5>
	<p>
		<span><?php $Lang->p('COMMENTS_TEXT', 1); ?></span>
		<select id="article_comments_count" style="display:none">
			<option value="5" selected="selected">5</option>
			<option value="10">10</option>
			<option value="15">15</option>
			<option value="20">20</option>
		</select>
		<select id="article_comments_type">
			<option value="0"><?php $Lang->p('COMMENTS_TYPES', 0); ?></option>
			<option value="1"><?php $Lang->p('COMMENTS_TYPES', 1); ?></option>
			<option value="-1"><?php $Lang->p('COMMENTS_TEXT', 2); ?></option>
		</select>
		<span id="article_comments_perpage"><?php $Lang->p('COMMENTS_TEXT', 3); ?></span>
	</p>
	<p id="article_comments_index" class="comments_index"></p>
	<p id="article_comments"></p>
</div>