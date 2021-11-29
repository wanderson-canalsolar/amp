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
if (Utilities::getInput('selection', 'POST', 'string') !== '') : ?>
<!-- Search and Filter -->
<div class="wpfd-statistics--form-row">
    <div class="wpfd-statistics--form-col wpfd-statistics--search">
        <input type="text"
               class="ju-input"
               placeholder="<?php esc_html_e('Search', 'wpfd'); ?>"
               value="<?php echo esc_attr(Utilities::getInput('query', 'POST', 'string')); ?>"
               id="query"
               name="query"
               />
        <button class="ju-button ju-icon-button" type="submit"><i class="material-icons">search</i>
    </div>
    <div class="wpfd-statistics--form-col wpfd-statistics--reset">
        <button type="button" id="wpfd-statistics-reset" class="ju-button orange-outline-button"><?php esc_html_e('Reset', 'wpfd'); ?></button>
    </div>
    <div class="wpfd-statistics--form-col wpfd-statistics--limit">
        <label for="file_per_page"><?php esc_html_e('Filter', 'wpfd'); ?>&nbsp</label>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape on wpfd_select()
        echo wpfd_select(
            array(
                '5'  => 5,
                '10' => 10,
                '15' => 15,
                '20' => 20,
                '25' => 25,
                '30' => 30,
                'all' => 'All',
            ),
            'limit',
            Utilities::getInput('limit', 'POST', 'string'),
            'id="file_per_page" onchange="this.form.submit();" style="width: 150px;" class="ju-input"'
        );
        ?>
    </div>
</div>
<?php endif; ?>

<?php if (is_countable($this->files) && count($this->files)) : ?>
    <?php
    $itemsThead = array(
        'post_title' => esc_html__('File title', 'wpfd'),
        'cattitle'   => esc_html__('Category', 'wpfd'),
        'user'       => esc_html__('Download by', 'wpfd'),
        'count_hits' => apply_filters('wpfd_statistics_download_count_text', esc_html__('Download Count', 'wpfd')),
    );
    $sortable = array('count_hits');
    ?>
    <input type="hidden" name="paged" value="0">
    <input type="hidden" name="filter_order" value="<?php echo esc_attr($this->ordering); ?>"/>
    <input type="hidden" name="filter_order_dir" value="<?php echo esc_attr($this->orderingdir); ?>"/>
    <!-- Results table -->
    <div class="wpfd-statistics--form-row">
        <div class="wpfd-statistics--table">
            <table class="ju-table">
                <thead>
                <tr>
                    <?php
                    $selection = Utilities::getInput('selection', 'POST', 'string');
                    $selection_value = Utilities::getInput('selection_value', 'POST', 'none');
                    foreach ($itemsThead as $theadKey => $theadText) :
                        if ($selection !== 'users' || ($selection === 'users' && empty($selection_value))) {
                            if ($theadKey === 'user') {
                                continue;
                            }
                        }

                        $icon = '';
                        $dir  = $this->orderingdir;
                        if ($theadKey === $this->ordering) {
                            $icon = '<i class="dashicons dashicons-arrow-';
                            $icon .= ($this->orderingdir === 'asc' ? 'up' : 'down') . '"></i>';
                            if ($dir === 'asc') {
                                $dir = 'desc';
                            } elseif ($dir === 'desc') {
                                $dir = 'asc';
                            }
                        }
                        $currentOrderingCol = ($this->ordering === $theadKey) ? 'currentOrderingCol' : '';
                        ?>
                        <th class="wpfd-statistics--thead <?php echo esc_attr(sanitize_title($theadText)); ?>">
                            <a href="#"
                               class="wpfd-ordering <?php echo esc_attr($currentOrderingCol); ?>"
                               data-ordering="<?php echo esc_attr($theadKey); ?>"
                               data-direction="<?php echo esc_attr($dir); ?>">
                                <?php echo esc_html($theadText); ?>
                                <?php
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- nothing need to be escape
                                echo $icon;
                                ?>
                                </a>
                        </th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($this->files as $file) {
                    ?>
                    <tr class="wpfd-statistics--table-row file">
                        <td class="first"><?php echo esc_html($file->post_title); ?></td>
                        <td class=""><?php echo esc_html($file->cattitle); ?></td>
                        <?php if (isset($file->display_name) && isset($file->uid)) : ?>
                        <td class="">
                            <?php
                            if ((int) $file->uid === 0) {
                                esc_html_e('Guess', 'wpfd');
                            } else {
                                echo esc_html($file->display_name);
                            }
                            ?>
                        </td>
                        <?php endif; ?>
                        <td class="last"><?php echo esc_html($file->count_hits); ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div class="wpfd-statistics--pagination">
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- This value come from pagination builder of wordpress
                echo $this->pagination;
                ?>
            </div>
        </div>
    </div>
<?php else : ?>
    <?php if (Utilities::getInput('selection', 'POST', 'string') !== ''
          || Utilities::getInput('query', 'POST', 'string') !== ''
          || Utilities::getInput('fdate', 'POST', 'string') !== ''
          || Utilities::getInput('tdate', 'POST', 'string') !== '') : ?>
    <h3 class="message"><?php esc_html_e("There's no statistics available for the filters selected", 'wpfd'); ?></h3>
    <?php endif; ?>
<?php endif; ?>
