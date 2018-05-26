<?php
	/**********************************************************
    Title: Footer
    Authors: Kookiiz Team
    Purpose: HTML code of the footer area
    ***********************************************************/

    //Find current lang name
    $languages          = C::get('LANGUAGES');
    $languages_names    = C::get('LANGUAGES_NAMES');
    $current_lang_id    = array_search(Session::get('lang'), $languages);
    $current_lang_name  = $languages_names[$current_lang_id];

    //Find current year
    $year = date('Y');
?>
<div class="center">
    
    <!-- Various links -->
    <span>©Kookiiz 2009-<?php echo $year; ?></span>

    <?php if(PAGE_NAME == 'main'): ?>
    <span> | </span>
    <a class="text_default" href="mailto:info@kookiiz.com">
        <?php $Lang->p('FOOTER_TEXT', 0); ?>
    </a>
    <span> | </span>
    <a id="footer_link_sources" class="text_default" href="javascript:Utilities.sources_display();">
        <?php $Lang->p('FOOTER_TEXT', 1); ?>
    </a>
    <span> | </span>
    <a id="footer_link_terms"  class="text_default" href="javascript:Utilities.terms_display();">
        <?php $Lang->p('FOOTER_TEXT', 2); ?>
    </a>
    <?php endif; ?>
        
    <!-- Full website link (DISABLED) -->
    <?php if(false): ?>
    <span> | </span>
    <a href="/" class="text_default"><?php $Lang->p('FOOTER_TEXT', 3); ?></a>
    <?php endif; ?>
        
    <!-- Language link -->
    <span> | <?php echo $Lang->get('KEYWORDS', 15), ': '; ?></span>
    <a class="text_default" href="javascript:Kookiiz.lang.popup();">
        <?php echo $current_lang_name; ?>
    </a>
</div>