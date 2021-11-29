<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="wpfd-single-file-block-module-preview-template">

    <h4 class="fusion_module_title wpfd-single-file-title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
        <#
        var elementContent      = params.element_content;
        var singleFilePreview   = '';

        if ( '' !== elementContent ) {
            singleFilePreview = jQuery( '<div></div>' ).html( elementContent ).text();
        }
        #>

        <# if ( '' !== elementContent ) { #>
            <span style="font-weight: bold">File Title: </span>
        <# } #>

    <span class="file-title" style="font-style: italic"> {{{ singleFilePreview }}} </span>

</script>
