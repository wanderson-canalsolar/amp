<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

// No direct access.
defined('ABSPATH') || die();

?>

<div id="wpfdViewer">
    <?php if ($this->mediaType === 'image') { ?>
        <img src="<?php echo esc_url($this->downloadLink); ?>" alt="" title=""/>
    <?php } elseif ($this->mediaType === 'video') { ?>
        <video class="lazy" width="100%" height="100%" src="<?php echo esc_url($this->downloadLink); ?>"
               type="<?php echo esc_attr($this->mineType); ?>"
               class="mejs-player" data-mejsoptions='{"alwaysShowControls": true}'
               id="playerVid" controls="controls" preload="none" autoplay="true" playsinline>
            <source type="<?php echo esc_attr($this->mineType); ?>" src="<?php echo esc_url($this->downloadLink); ?>"/>
            <?php esc_html_e('Your browser does not support the video element.', 'wpfd'); ?>
        </video>
    <?php } elseif ($this->mediaType === 'audio') { ?>
        <audio src="<?php echo esc_html($this->downloadLink); ?>" type="<?php echo esc_attr($this->mineType); ?>"
               id="playerAud" controls="controls" preload="none" autoplay="true"></audio>
    <?php } ?>
</div>
<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet,WordPress.WP.EnqueuedResources.NonEnqueuedScript -- require in popup ?>
<link rel='stylesheet' href="<?php echo esc_url(plugins_url('app/site/assets/css/mediaelementplayer.min.css', WPFD_PLUGIN_FILE)); ?>" type='text/css' media='all' />
<script>
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
<script src="<?php echo esc_url(plugins_url('app/site/assets/js/mediaelement.min.js', WPFD_PLUGIN_FILE)); ?>" ></script>
<script src="<?php echo esc_url(plugins_url('app/site/assets/js/mediaelement-and-player.js', WPFD_PLUGIN_FILE)); ?>" ></script>
<?php // phpcs:enable ?>
<script type="text/javascript">
  jQuery(document).ready(function ($) {
    var w = $('#wpfdViewer').width();
    var h = $('#wpfdViewer').height();
    var vid = document.getElementById("playerVid");
    var aud = document.getElementById("playerAud");
    if (vid !== null) {
      vid.onloadeddata = function () {
        // Browser has loaded the current frame
        var vW = $(vid).width();
        var vH = $(vid).height();

        if (vH > h) {
          var newH = h - 10;
          newW = newH / vH * vW;
          $(vid).attr('width', newW).attr('height', newH);
          $(vid).width(newW);
          $(vid).height(newH);

          $(".mejs-video").width(newW);
          $(".mejs-video").height(newH);

          var barW = newW - 150;
          $(".mejs-time-rail").width(barW).css('padding-right', '5px');
          $(".mejs-time-total").width(barW);
        }

      };

    }

    $('video,audio').mediaelementplayer(/* Options */);
  });

</script>

<style>
    .wpfdviewer::before {
        content: none;
    }

    #wpfdViewer {
        text-align: center;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100%;
    }

    #wpfdViewer img {
        max-width: 100%;
        height: auto;
        max-height: 100%;
    }

    #wpfdViewer audio, #wpfdViewer video {
        display: inline-block;
    }

    #wpfdViewer .mejs-container {
        margin: 0 auto;
        max-width: 100%;
    }

    #wpfdViewer video {
        width: 100% !important;
        max-width: 100%;
        height: auto !important;
        max-height: 100% !important;
    }

    #wpfdViewer .mejs-container.mejs-video {
        margin: 0 auto;
    }

    #wpfdViewer .mejs-container.mejs-audio {
        top: 50%;
        margin-top: -15px;
    }

    .wpfdviewer #wpadminbar {
        display: none;
    }
</style>    
