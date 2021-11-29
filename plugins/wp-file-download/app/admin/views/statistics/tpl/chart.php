<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 4.6.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/* Our logic for chart here */
list($lables, $datas) = $this->getLablesDatas();
?>

<div class="wpfd-statistics--chart" id="wpfd-statistics-chart">
    <h3><?php echo esc_html(apply_filters('wpfd_statistics_download_count_text', esc_html__('Download Count', 'wpfd'))); ?></h3>
    <button type="button" class="ju-button orange-outline-button wpfd-statistics--export"><i class="material-icons">cloud_download</i></button>
    <canvas id="wpfd-chart" style="min-height: 50vh;height:500px; width:100%">
        <?php esc_html_e('Your browser does not support the canvas element.', 'wpfd'); ?>
    </canvas>
</div>
<script type="text/javascript">
    var lables = [<?php echo implode(',', $lables); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in view.php ?>];

    var dataSet = [
        <?php foreach ($datas as $data) : ?>
        {
        label: '<?php echo esc_html($data['label']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in view.php ?>',
        data: [<?php echo implode(',', $data['datas']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in view.php ?>],
        backgroundColor: 'rgba(<?php echo esc_attr($data['color']['r']); ?>, <?php echo esc_attr($data['color']['g']); ?>, <?php echo esc_attr($data['color']['b']); ?>, 0.2)',
        borderColor: 'rgba(<?php echo esc_attr($data['color']['r']); ?>, <?php echo esc_attr($data['color']['g']); ?>, <?php echo esc_attr($data['color']['b']); ?>, 1)',
        borderWidth: 1
    },
        <?php endforeach; ?>
    ];

    var options = {
        responsive: true,
        legend: {
            position: 'bottom'
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    maxTicksLimit: 25,
                    min: 0,
                    //stepSize: 1
                },
                scaleLabel: {
                    display: true,
                    labelString: '<?php echo esc_html(apply_filters('wpfd_statistics_download_count_text', esc_html__('Download Count', 'wpfd')));?>'
                }
            }],
            xAxes: [{
                ticks: {
                    maxTicksLimit: 25,
                },
                scaleLabel: {
                    display: true,
                    labelString: '<?php esc_html_e('Date', 'wpfd');?>'
                }
            }]
        },
        layout: {
            padding: {
                left: 25,
                right: 25,
                top: 25,
                bottom: 25
            }
        },
        elements: {
            line: {
                tension: 0, // disables bezier curves,
                fill: false
            }
        },
        tooltips: {
            mode: 'index',
            intersect: false,
        }
    };

    var wpfdChartElm = document.getElementById('wpfd-chart');
    var wpfdAjaxUrl = '<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>';
    var wpfdChart = new Chart(wpfdChartElm, {
        type: 'line',
        data: {
            labels: lables,
            datasets: dataSet
        },
        options: options
    });
</script>
