<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 4.6.0
 */

use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

$fdate = Utilities::getInput('fdate', 'POST', 'string');
$tdate = Utilities::getInput('tdate', 'POST', 'string');
$fdate = ($fdate !== '') ? $fdate : '';
$tdate = ($tdate !== '') ? $tdate : '';

$momentDateFormat = wpfdPhpToMomentDateFormat($this->dateFormat);
?>

<div class="wpfd-statistics--form-row">
    <div class="wpfd-statistics--form-col wpfd-statistics--type">
        <?php
        $placeholders = array(
            'category' => esc_html__('Select categories', 'wpfd'),
            'files'    => esc_html__('Select files', 'wpfd'),

        );
        $options      = array(
            ''         => esc_html__('Total downloads', 'wpfd'),
            'category' => esc_html__('Category', 'wpfd'),
            'files'    => esc_html__('Files', 'wpfd')
        );
        if ($this->allowTrackUserDownload === 1) {
            $options['users']      = esc_html__('Download per users', 'wpfd');
            $placeholders['users'] = esc_html__('Select users', 'wpfd');
        }
        /**
         * Statistics filter by
         *
         * @param array
         */
        $options = apply_filters('wpfd_statistics_filter_by', $options);

        /**
         * Statistics filter by placeholders
         *
         * @param array
         */
        $placeholders = apply_filters('wpfd_statistics_filter_by_placeholders', $placeholders);
        $selection    = esc_html(Utilities::getInput('selection', 'POST', 'string'));
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape on wpfd_select()
        echo wpfd_select(
            $options,
            'selection',
            $selection,
            'id="selection" class="ju-input"'
        );
        ?>
    </div>
    <?php if (Utilities::getInput('selection', 'POST', 'string') !== '') :
        $placeholder = '';
        if (isset($placeholders[$selection])) {
            $placeholder = ' data-placeholder="' . $placeholders[$selection] . '"';
        }
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        $selection_value = (is_countable($this->selectionValues) && count($this->selectionValues)) ? $this->selectionValues : array();
        $select          = Utilities::getInput('selection_value', 'POST', 'none');
        $selectionHtml   = wpfd_select(
            $selection_value,
            'selection_value[]',
            $select,
            'class="ju-input chosen" multiple="true"' . $placeholder
        );
        ?>
        <div class="wpfd-statistics--form-col wpfd-statistics--additional">
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape on wpfd_select()
            echo $selectionHtml;
            ?>
        </div>
    <?php endif; ?>
    <div class="wpfd-statistics--form-col wpfd-statistics--date-filter">
        <label rel="wpfd-statistics--range"><?php esc_html_e('Date range', 'wpfd'); ?>:</label>
        <input id="wpfd-statistics--range" name="wpfd-statistics-range" class="ju-input"/>
        <input type="hidden" name="fdate" id="fdate" value="<?php echo esc_attr($fdate); ?>"/>
        <input type="hidden" name="tdate" id="tdate" value="<?php echo esc_attr($tdate); ?>"/>
        <i class="material-icons wpfd-range-icon">calendar_today</i>
    </div>
    <div class="wpfd-statistics--form-col wpfd-statistics--submit">
        <button type="submit" class="ju-button orange-button"><?php esc_html_e('Apply Filter', 'wpfd'); ?></button>
    </div>
</div>
<script type="application/javascript">
    jQuery(function () {
        var fdate = '<?php echo esc_attr($fdate); ?>';
        var tdate = '<?php echo esc_attr($tdate); ?>';

        if (fdate === '' || tdate === '') {
            fdate = moment().subtract(29, 'days');
            tdate = moment();
        }

        fdate = moment(fdate).format('<?php echo esc_html($momentDateFormat); ?>');
        tdate = moment(tdate).format('<?php echo esc_html($momentDateFormat); ?>');

        var locale = {
            "format": "<?php echo esc_html($momentDateFormat); ?>",
            "separator": " - ",
            "applyLabel": "<?php esc_html_e('Apply', 'wpfd'); ?>",
            "cancelLabel": "<?php esc_html_e('Cancel', 'wpfd'); ?>",
            "fromLabel": "<?php esc_html_e('From', 'wpfd'); ?>",
            "toLabel": "<?php esc_html_e('To', 'wpfd'); ?>",
            "customRangeLabel": "<?php esc_html_e('Custom', 'wpfd'); ?>",
            "weekLabel": "<?php esc_html_e('W', 'wpfd'); ?>",
            "daysOfWeek": [
                "<?php esc_html_e('Su', 'wpfd'); ?>",
                "<?php esc_html_e('Mo', 'wpfd'); ?>",
                "<?php esc_html_e('Tu', 'wpfd'); ?>",
                "<?php esc_html_e('We', 'wpfd'); ?>",
                "<?php esc_html_e('Th', 'wpfd'); ?>",
                "<?php esc_html_e('Fr', 'wpfd'); ?>",
                "<?php esc_html_e('Sa', 'wpfd'); ?>",
            ],
            "monthNames": [
                "<?php esc_html_e('January', 'wpfd'); ?>",
                "<?php esc_html_e('February', 'wpfd'); ?>",
                "<?php esc_html_e('March', 'wpfd'); ?>",
                "<?php esc_html_e('April', 'wpfd'); ?>",
                "<?php esc_html_e('May', 'wpfd'); ?>",
                "<?php esc_html_e('June', 'wpfd'); ?>",
                "<?php esc_html_e('July', 'wpfd'); ?>",
                "<?php esc_html_e('August', 'wpfd'); ?>",
                "<?php esc_html_e('September', 'wpfd'); ?>",
                "<?php esc_html_e('October', 'wpfd'); ?>",
                "<?php esc_html_e('November', 'wpfd'); ?>",
                "<?php esc_html_e('December', 'wpfd'); ?>",
            ],
            "firstDay": 1,
        };
        var ranges = {
            '<?php esc_html_e('Today', 'wpfd'); ?>': [moment(), moment()],
            '<?php esc_html_e('Yesterday', 'wpfd'); ?>': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '<?php esc_html_e('Last 7 Days', 'wpfd'); ?>': [moment().subtract(6, 'days'), moment()],
            '<?php esc_html_e('Last 30 Days', 'wpfd'); ?>': [moment().subtract(29, 'days'), moment()],
            '<?php esc_html_e('This Month', 'wpfd'); ?>': [moment().startOf('month'), moment().endOf('month')],
            '<?php esc_html_e('Last Month', 'wpfd'); ?>': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        };
        var cb = function (start, end, label) {
            jQuery("#fdate").val(start.format('YYYY-MM-DD')); // Keep this format
            jQuery("#tdate").val(end.format('YYYY-MM-DD')); // Keep this format
        };
        jQuery('#wpfd-statistics--range').daterangepicker({
            "startDate": fdate,
            "endDate": tdate,
            "showDropdowns": true,
            ranges: ranges,
            locale: locale,
            "alwaysShowCalendars": true,
            "linkedCalendars": false,
            "opens": "center",
        }, cb);
    });
</script>
