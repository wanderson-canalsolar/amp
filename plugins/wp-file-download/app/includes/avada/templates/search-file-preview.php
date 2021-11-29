<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="wpfd-search-file-block-module-preview-template">

    <h4 class="fusion_module_title wpfd-search-file-title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
        <#
        var elementContent      = params.element_content;
        var searchFilePreview   = '';

        if ( '' !== elementContent ) {
            searchFilePreview = jQuery( '<div></div>' ).html( elementContent ).text();
        }
        #>
        <# if ( '' !== elementContent ) { #>
        <span class="search-pre-title" style="font-weight: bold">Search Shortcode: </span>
        <# } #>
    <span class="search-title" style="font-style: italic"> {{{ searchFilePreview }}} </span>

</script>
