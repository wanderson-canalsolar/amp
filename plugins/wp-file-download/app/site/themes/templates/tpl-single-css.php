<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

//-- No direct access
defined('ABSPATH') || die();
?>
.wpfd-single-file--icon .wpfd-icon-placeholder {
margin: {{icon_margin_top}}px {{icon_margin_right}}px {{icon_margin_bottom}}px {{icon_margin_left}}px;
width: {{icon_size}}px;
height: {{icon_size}}px;
}
.wpfd-single-file--icon {
flex-basis: {{icon_size}}px;
}
.wpfd-single-file--details {
{{#if icon}}
flex-basis: calc(100% - {{icon_size}}px - {{icon_margin_right}}px - {{icon_margin_left}}px);
{{else}}
flex-basis: 100%;
{{/if}}
}
{{#unless icon}}
.wpfd-single-file .wpfd-file-content {
padding-left: 0;
}
{{/unless}}
.wpfd-single-file .wpfd-file-content--meta > div {
font-size: {{file_information_font_size}}px;
padding: {{file_information_padding_top}}px {{file_information_padding_right}}px {{file_information_padding_bottom}}px {{file_information_padding_left}}px;
margin: {{file_information_margin_top}}px {{file_information_margin_right}}px {{file_information_margin_bottom}}px {{file_information_margin_left}}px;
}
.wpfd-single-file .wpfd-file-content--title {
font-size: {{title_font_size}}px;
line-height: {{title_font_size}}px;
padding: {{title_padding_top}}px {{title_padding_right}}px {{title_padding_bottom}}px {{title_padding_left}}px;
margin: {{title_margin_top}}px {{title_margin_right}}px {{title_margin_bottom}}px {{title_margin_left}}px;
}
.wpfd-single-file .wpfd-file-content--description {
font-size: {{description_font_size}}px;
padding: {{description_padding_top}}px {{description_padding_right}}px {{description_padding_bottom}}px {{description_padding_left}}px;
margin: {{description_margin_top}}px {{description_margin_right}}px {{description_margin_bottom}}px {{description_margin_left}}px;
}
.wpfd-single-file-button.wpfd-button-download {
{{#xif download_background 'solid'}}
background: {{download_background_solid}};
{{else}}
background: {{download_background_start}};
background: -moz-linear-gradient(90deg, {{download_background_start}} 0%, {{download_background_end}} 100%);
background: -webkit-linear-gradient(90deg, {{download_background_start}} 0%, {{download_background_end}} 100%);
background: linear-gradient(90deg, {{download_background_start}} 0%, {{download_background_end}} 100%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="{{download_background_start}}",endColorstr="{{download_background_end}}",GradientType=1);
{{/xif}}
color: {{download_font_color}};
border-radius: {{download_border_radius}}px;
{{#if download_border_size}}
border: {{download_border_size}}px solid {{download_border_color}};
{{else}}
border: 0;
{{/if}}
width: {{download_width}}px;
font-size: {{download_font_size}}px;
padding: {{download_padding_top}}px {{download_padding_right}}px {{download_padding_bottom}}px {{download_padding_left}}px;
margin: {{download_margin_top}}px {{download_margin_right}}px {{download_margin_bottom}}px {{download_margin_left}}px;
box-shadow: {{download_boxshadow_horizontal}}px {{download_boxshadow_vertical}}px {{download_boxshadow_blur}}px {{download_boxshadow_spread}}px {{download_boxshadow_color}};
}
{{#if download_icon_spacing}}
.wpfd-single-file a.wpfd-single-file-button.wpfd-button-download svg {
{{#xif download_icon_position 'left'}}
margin-left: {{download_icon_spacing}}px;
{{else}}
margin-right: {{download_icon_spacing}}px;
{{/xif}}
}
{{/if}}
.wpfd-single-file-button.wpfd-button-download:hover {
border-color: {{download_border_color}};
color: {{download_hover_font_color}};
{{#xif download_hover_background 'solid'}}
background: {{download_hover_background_solid}};
{{else}}
background: {{download_hover_background_start}};
background: -moz-linear-gradient(90deg, {{download_hover_background_start}} 0%, {{download_hover_background_end}} 100%);
background: -webkit-linear-gradient(90deg, {{download_hover_background_start}} 0%, {{download_hover_background_end}} 100%);
background: linear-gradient(90deg, {{download_hover_background_start}} 0%, {{download_hover_background_end}} 100%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="{{download_hover_background_start}}",endColorstr="{{download_hover_background_end}}",GradientType=1);
{{/xif}}
}

.wpfd-button-download:hover,
.wpfd-single-file-button + a.added_to_cart:hover {
border-color: {{download_border_color}};
}

.wpfd-single-file-button.wpfd-button-preview {
{{#xif preview_background 'solid'}}
background: {{preview_background_solid}};
{{else}}
background: {{preview_background_start}};
background: -moz-linear-gradient(90deg, {{preview_background_start}} 0%, {{preview_background_end}} 100%);
background: -webkit-linear-gradient(90deg, {{preview_background_start}} 0%, {{preview_background_end}} 100%);
background: linear-gradient(90deg, {{preview_background_start}} 0%, {{preview_background_end}} 100%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="{{preview_background_start}}",endColorstr="{{preview_background_end}}",GradientType=1);
{{/xif}}
color: {{preview_font_color}};
border-radius: {{preview_border_radius}}px;
{{#if preview_border_size}}
border: {{preview_border_size}}px solid {{preview_border_color}};
{{else}}
border: 0;
{{/if}}
width: {{preview_width}}px;
font-size: {{preview_font_size}}px;
padding: {{preview_padding_top}}px {{preview_padding_right}}px {{preview_padding_bottom}}px {{preview_padding_left}}px;
margin: {{preview_margin_top}}px {{preview_margin_right}}px {{preview_margin_bottom}}px {{preview_margin_left}}px;
box-shadow: {{preview_boxshadow_horizontal}}px {{preview_boxshadow_vertical}}px {{preview_boxshadow_blur}}px {{preview_boxshadow_spread}}px {{preview_boxshadow_color}};
}
{{#if preview_icon_spacing}}
.wpfd-single-file a.wpfd-single-file-button.wpfd-button-preview svg {
{{#xif preview_icon_position 'left'}}
margin-left: {{preview_icon_spacing}}px;
{{else}}
margin-right: {{preview_icon_spacing}}px;
{{/xif}}
}
{{/if}}
.wpfd-single-file-button.wpfd-button-preview:hover {
border-color: {{preview_border_color}};
color: {{preview_hover_font_color}};
{{#xif preview_hover_background 'solid'}}
background: {{preview_hover_background_solid}};
{{else}}
background: {{preview_hover_background_start}};
background: -moz-linear-gradient(90deg, {{preview_hover_background_start}} 0%, {{preview_hover_background_end}} 100%);
background: -webkit-linear-gradient(90deg, {{preview_hover_background_start}} 0%, {{preview_hover_background_end}} 100%);
background: linear-gradient(90deg, {{preview_hover_background_start}} 0%, {{preview_hover_background_end}} 100%);
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="{{preview_hover_background_start}}",endColorstr="{{preview_hover_background_end}}",GradientType=1);
{{/xif}}
}

.wpfd-button-preview:hover,
.wpfd-single-file-button + a.added_to_cart:hover {
border-color: {{preview_border_color}};
}