<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="wpfd-category-file-block-module-preview-template">

    <h4 class="fusion_module_title wpfd-category-file-title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
        <#
        var elementContent      = params.element_content;
        var categoryFilePreview = '';

        if ( '' !== elementContent ) {
            categoryFilePreview = jQuery( '<div></div>' ).html( elementContent ).text();
        }
        #>
        <# if ( '' !== elementContent ) { #>
        <span style="font-weight: bold">Category Title: </span>
        <# } #>
    <span class="category-title" style="font-style: italic"> {{{ categoryFilePreview }}} </span>

</script>
