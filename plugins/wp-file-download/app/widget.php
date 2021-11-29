<?php

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Utilities;

/**
 * Class WPFDWidgetSearch
 */
class WPFDWidgetSearch extends WP_Widget
{

    /**
     * WPFDWidgetSearch constructor.
     */
    public function __construct()
    {
        $widget_ops = array('classname' => 'widget_wpfd_search', 'description' => esc_html__('A search form.', 'wpfd'));
        parent::__construct('wpfd_search', esc_html__('WP File Download Search', 'wpfd'), $widget_ops);
    }

    /**
     * Method display search files
     *
     * @param array $args     Options
     * @param array $instance Instance
     *
     * @return void
     */
    public function widget($args, $instance)
    {
        wpfd_enqueue_assets();
        wpfd_assets_search();
        $widget_title = empty($instance['title']) ? esc_html__('Search', 'wpfd') : $instance['title'];
        $title = apply_filters('widget_title', $widget_title, $instance, $this->id_base);

        $filters = array();
        $q = Utilities::getInput('q', 'GET', 'string');

        if (!empty($q)) {
            $filters['q'] = $q;
        }
        $catid = Utilities::getInput('catid', 'GET', 'string');
        if (!empty($catid)) {
            $filters['catid'] = $catid;
        }

        $ftags = Utilities::getInput('ftags', 'GET', 'none');
        if (is_array($ftags)) {
            $ftags = array_unique($ftags);
            $ftags = implode(',', $ftags);
        } else {
            $ftags = Utilities::getInput('ftags', 'GET', 'string');
        }

        if (!empty($ftags)) {
            $filters['ftags'] = $ftags;
        }
        $cfrom = Utilities::getInput('cfrom', 'GET', 'string');
        if (!empty($cfrom)) {
            $filters['cfrom'] = $cfrom;
        }
        $cto = Utilities::getInput('cto', 'GET', 'string');
        if (!empty($cto)) {
            $filters['cto'] = $cto;
        }
        $ufrom = Utilities::getInput('ufrom', 'GET', 'string');
        if (!empty($ufrom)) {
            $filters['ufrom'] = $ufrom;
        }
        $uto = Utilities::getInput('uto', 'GET', 'string');
        if (!empty($uto)) {
            $filters['uto'] = $uto;
        }

        $ordering = Utilities::getInput('ordering', 'GET', 'string');
        $dir = Utilities::getInput('dir', 'GET', 'string');
        $dir = $dir === null ? 'asc' : 'desc';
        $app = Application::getInstance('Wpfd');
        $baseUrl = $app->getBaseUrl();
        $modelCategories = Model::getInstance('categories');
        $model = Model::getInstance('search');
        $modelConfig = Model::getInstance('config');
        if (method_exists($modelConfig, 'getGlobalConfig')) {
            $config = $modelConfig->getGlobalConfig();
        } elseif (method_exists($modelConfig, 'getConfig')) {
            $config = $modelConfig->getConfig();
        } else {
            return;
        }
        $categories = $modelCategories->getLevelCategories();

        $tags = get_terms('wpfd-tag', array(
            'orderby' => 'count',
            'hide_empty' => 0,
        ));

        $allTagsFiles = '';
        $TagLabels = array();
        if ($tags) {
            $TagsFiles = array();
            foreach ($tags as $tag) {
                $TagsFiles[] = '' . esc_html($tag->slug);
                $TagLabels[$tag->term_id] = esc_html($tag->name);
            }
            $allTagsFiles = '["' . implode('","', $TagsFiles) . '"]';
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Allow html
        echo $args['before_widget'];
        if ($title) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Allow html
            echo $args['before_title'] . $title . $args['after_title'];
        }
        ?>

        <script>
            wpfdajaxurl = "<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>";
            var filterData = null;
            var defaultAllTags = <?php echo ($allTagsFiles !== '' ? $allTagsFiles : '[]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allready esc above?>;
            var tagsLabel = {<?php
            foreach ($TagLabels as $key => $value) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allready esc in view.php
                echo str_replace('-', '', 'wpfd' . esc_html($key)) . ' : "' . esc_html($value) . '",';
            }
            ?>};
            jQuery(document).ready(function ($) {
                $('.widget_wpfd_search #filter_catid_chzn').removeAttr('style');
                $('.widget_wpfd_search .chzn-search input').removeAttr('readonly');
                <?php if ((int)$instance['tag_filter'] === 1 && (string)$instance['display_tag'] === 'searchbox') : ?>
                var defaultTags = [];
                var availTags = [];
                    <?php if (isset($filters) && isset($filters['ftags'])) : ?>
                var ftags = '<?php echo esc_html($filters['ftags']);?>';
                defaultTags = ftags.split(',');
                    <?php endif; ?>
                    <?php if (!empty($allTagsFiles)) : ?>
                availTags = <?php echo $allTagsFiles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- allready esc above ?>;
                    <?php endif; ?>
                jQuery(".widget_wpfd_search .input_tags").tagit({
                    availableTags: availTags,
                    allowSpaces: true,
                    initialTags: defaultTags,
                    autocomplete: { source: function( request, response ) {
                            var filter = request.term.toLowerCase();
                            response( jQuery.grep(availTags, function(element) {
                                return (element.toLowerCase().indexOf(filter) === 0);
                            }));
                        }},
                    beforeTagAdded: function(event, ui) {
                        if (jQuery.inArray(ui.tagLabel, availTags) == -1) {
                            jQuery('.widget_wpfd_search span.error-message').css("display", "block").fadeOut(2000);
                            setTimeout(function() {
                                try {
                                    jQuery(".widget_wpfd_search .input_tags").tagit("removeTagByLabel", ui.tagLabel, 'fast');
                                } catch (e) {
                                    console.log(e);
                                }

                            }, 100);

                            return;
                        }
                        return true;
                    }
                });
                <?php endif; ?>

                <?php if (!empty($filters)) { ?>
                filterData = <?php echo json_encode($filters);?>;
                <?php } ?>
            $('.widget_wpfd_search .txtfilename').on('keydown', function (e) {
                if (e.keyCode === 13 || e.which === 13 || e.key === 'Enter')
                {
                    $(this).parent().parent().parent().parent().submit();
                }
                return;
            });
                window.history.pushState(filterData, '', window.location);
            });
        </script>

        <form action="<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>task=search.query"
              name="widget_search" id="widget_search" method="post">

            <div class="box-search-filter">
                <div class="searchSection">
                    <?php if ((int) $instance['cat_filter'] === 1) : ?>
                        <div class="categories-filtering" >
                            <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/menu.svg'); ?>" class="material-icons cateicon"/>
                            <div class="cate-lab"><?php esc_html_e('FILES CATEGORY', 'wpfd'); ?></div>
                            <div class="ui-widget wpfd-listCate" style="display: none">
                                <input title="" type="hidden" value="" id="filter_catid" class="chzn-select" name="catid"> </input>
                                <ul class="cate-list" id="cate-list">
                                    <?php
                                    if (count($categories) > 0) {
                                        $excludes = array();
                                        if (isset($filters['exclude']) && $filters['exclude'] !== '0') {
                                            $excludes = array_merge($excludes, explode(',', trim($filters['exclude'])));
                                        }
                                        ?>
                                        <li class="search-cate" >
                                            <input class="qCatesearch" id="wpfdCategorySearch" data-id="" placeholder="Search...">
                                        </li>
                                        <li class="cate-item" data-catid="">
                                            <span class="wpfd-toggle-expand"></span>
                                            <span class="wpfd-folder-search"></span>
                                            <label><?php esc_html_e('All', 'wpfd'); ?></label>
                                        </li>
                                        <?php

                                        foreach ($categories as $key => $category) {
                                            if ($category ->level > 1) {
                                                $downicon = '<span class="wpfd-toggle-expand child-cate"></span>';
                                            } else {
                                                $downicon = '<span class="wpfd-toggle-expand"></span>';
                                            }

                                            if (isset($filters['exclude']) && $filters['exclude'] !== '0') {
                                                // Remove exclude category and it children
                                                if (in_array((string) $category->term_id, $excludes) || in_array((string) $category->parent, $excludes)) {
                                                    // Add it id to excludes array
                                                    $excludes[] = (string) $category->term_id;
                                                    continue;
                                                }
                                            }
                                            if (isset($filters['catid']) && (int) $filters['catid'] === $category->term_id) {
                                                $echo = '<li class="cate-item choosed" data-catid="'.esc_attr($category->term_id).'" data-catlevel="'. esc_attr($category->level) .'">'
                                                        . '<span class="space-child">' . esc_html(str_repeat('-', $category->level - 1)) . '</span>'
                                                        . $downicon
                                                        . '<span class="wpfd-folder-search"></span>'
                                                        . '<label>' . esc_html($category->name) .'</label>'
                                                        . '</li>';
                                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc above
                                                echo $echo;
                                            } else {
                                                $echo = '<li class="cate-item" data-catid="'.esc_attr($category->term_id).'" data-catlevel="'. esc_attr($category->level) .'">'
                                                        . '<span class="space-child">'. esc_html(str_repeat('-', $category->level - 1)) .'</span>'
                                                        . $downicon
                                                        . '<span class="wpfd-folder-search"></span>'
                                                        . '<label>' . esc_html($category->name) .'</label>'
                                                        . '</li>';
                                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc above
                                                echo $echo;
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                    <?php elseif (isset($filters['catid']) && $filters['catid'] !== '0') : ?>
                        <input type="hidden" name="catid" value="<?php echo esc_html($filters['catid']); ?>" />
                    <?php endif; ?>
                    <div class="only-file input-group clearfix wpfd_search_input" id="Search_container">
                        <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/search-24.svg'); ?>" class="material-icons wpfd-icon-search"/>
                        <input type="text" class="pull-left required txtfilename" name="q" id="txtfilename"
                               placeholder="<?php esc_html_e('Search files...', 'wpfd'); ?>"
                               value="<?php echo esc_html(isset($filters['q']) ? $filters['q'] : ''); ?>"
                        />
                        <button type="submit" id="btnsearch" class="pull-left"><?php esc_html_e('Search', 'wpfd'); ?></button>
                    </div>
                </div>

                <?php if ((isset($instance['tag_filter']) && (int) $instance['tag_filter'] === 1) ||
                          (isset($instance['creation_date']) && (int) $instance['creation_date'] === 1) ||
                          (isset($instance['update_date']) && (int) $instance['update_date'] === 1)) : ?>
                    <div class="by-feature feature-border" id="Category_container">

                                                                                    <?php if ((int) $instance['creation_date'] === 1 && (int) $instance['update_date'] === 1 && (int) $instance['tag_filter'] === 1) : ?>
                        <!-- Tab links -->
                        <div class="wpfd_tab">
                            <button class="tablinks active" onclick="openSearchfilter(event, 'Filter')" id="defaultOpen"><?php esc_html_e('FILTER', 'wpfd') ?></button>
                            <button class="tablinks" onclick="openSearchfilter(event, 'Tags')"><?php esc_html_e('TAGS', 'wpfd'); ?></button>

                            <span class="feature-toggle toggle-arrow-up-alt"></span>
                        </div>
                                                                                    <?php endif; ?>

                                                                                    <?php
                                                                                    $span = 'span3';
                                                                                    if ((int) $instance['tag_filter'] === 1 && (int) $instance['display_tag'] === 'checkbox') {
                                                                                        $span = 'span4';
                                                                                    }
                                                                                    ?>
                        <div class="feature clearfix row-fluid wpfd_tabcontainer">
                            <!-- Tab content -->
                            <div id="Filter" class="wpfd_tabcontent active">
                                                                                    <?php if ((int) $instance['creation_date'] === 1) : ?>
                                    <div class="creation-date">
                                        <p class="date-info"><?php esc_html_e('CREATION DATE', 'wpfd'); ?></p>
                                        <div class="create-date-container">
                                            <div>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" type="text" data-min="cfrom" name="cfrom"
                                                           value="<?php echo esc_attr(isset($filters['cfrom']) ? $filters['cfrom'] : ''); ?>"
                                                           id="cfrom" placeholder="<?php esc_html_e('From', 'wpfd'); ?>"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="cfrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" data-min="cfrom" type="text" name="cto" id="cto"
                                                           value="<?php echo esc_attr(isset($filters['cto']) ? $filters['cto'] : ''); ?>"
                                                    placeholder="<?php esc_html_e('To', 'wpfd'); ?>" />
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="cto" data-min="cfrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                                                                    <?php endif; ?>
                                                                                    <?php if ((int) $instance['update_date'] === 1) : ?>
                                    <div class="update-date">
                                        <p class="date-info"><?php esc_html_e('UPDATE DATE', 'wpfd'); ?></p>
                                        <div class="update-date-container">
                                            <div>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" type="text" data-min="ufrom"
                                                           value="<?php echo esc_attr(isset($filters['ufrom']) ? $filters['ufrom'] : ''); ?>"
                                                           name="ufrom" id="ufrom" placeholder="<?php esc_html_e('From', 'wpfd'); ?>"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="ufrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="input-icon-date">
                                                    <input title="" class="input-date" type="text" data-min="ufrom"
                                                           value="<?php echo esc_attr(isset($filters['uto']) ? $filters['uto'] : ''); ?>"
                                                           name="uto" id="uto" placeholder="<?php esc_html_e('To', 'wpfd'); ?>"/>
                                                    <img src="<?php echo esc_url($baseUrl. '/app/site/assets/images/calendar_today.svg'); ?>" data-id="uto" data-min="ufrom" class="icon-date icon-calendar material-icons wpfd-range-icon"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                                                                    <?php endif; ?>
                            </div>
                            <div id="Tags" class="wpfd_tabcontent">
                                                                                    <?php if (!empty($allTagsFiles)) : ?>
                                                                                        <?php if ((int) $instance['tag_filter'] === 1 && $instance['display_tag'] === 'searchbox') : ?>
                                        <div class="span12 tags-filtering">
                                            <input title="" type="text" name="ftags" class="tagit input_tags"
                                                   value="<?php echo esc_attr(isset($filters['ftags']) ? $filters['ftags'] : ''); ?>"/>
                                        </div>
                                        <span class="error-message"><?php esc_html_e('No tag matching the query', 'wpfd'); ?></span>
                                                                                        <?php endif; ?>

                                                                                        <?php if ((int) $instance['tag_filter'] === 1 && $instance['display_tag'] === 'checkbox') : ?>
                                        <div class="clearfix row-fluid">
                                            <div class="span12 chk-tags-filtering">
                                                <p class="tags-info" style="text-align:left;"><?php esc_html_e('TAGS', 'wpfd'); ?></p>
                                                <input type="hidden" name="ftags" class="input_tags"
                                                       value="<?php echo esc_attr(isset($filters['ftags']) ? $filters['ftags'] : ''); ?>"/>
                                                                                            <?php
                                                                                            if (isset($filters['ftags'])) {
                                                                                                $selectedTags = explode(',', $filters['ftags']);
                                                                                            } else {
                                                                                                $selectedTags = array();
                                                                                            }
                                                                                            $allTags = str_replace(array('[', ']', '"'), '', $allTagsFiles);
                                                                                            if ($allTags !== '') {
                                                                                                $arrTags = explode(',', $allTags);
                                                                                                asort($arrTags);
                                                                                                echo '<ul>';
                                                                                                echo '<label class="labletags">';
                                                                                                esc_html_e('Filter by Tags', 'wpfd');
                                                                                                echo '</label>';
                                                                                                foreach ($arrTags as $key => $fileTag) {
                                                                                                    ?>
                                                        <li class="tags-item">
                                                            <span><?php echo esc_html($TagLabels['wpfd' . $key]); ?></span>
                                                            <input type="checkbox" name="chk_ftags[]" value="<?php echo esc_attr($fileTag);?>" class="ju-input chk_ftags" id="ftags<?php echo esc_attr($key); ?>">
                                                        </li>
                                                                                                <?php }
                                                                                                echo '</ul>';
                                                                                            }
                                                                                            ?>
                                            </div>
                                        </div>
                                                                                        <?php endif; ?>

                                                                                    <?php else : ?>
                                    <div class="no-tags"></div>
                                                                                    <?php endif; ?>
                            </div>
                            <div class="clearfix"></div>
                            <div class="box-btngroup-below">
                                <button class="btnsearchbelow" type="submit">
                                                                                    <?php esc_html_e('SEARCH', 'wpfd'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php elseif (isset($filters['catid']) && $filters['catid'] !== '0') : ?>
                    <input type="hidden" name="catid" value="<?php echo esc_html($filters['catid']); ?>" />
                <?php endif; ?>
                <?php if (isset($filters['exclude']) && $filters['exclude'] !== '0') : ?>
                    <input type="hidden" name="exclude" value="<?php echo esc_html($filters['exclude']); ?>" />
                <?php endif; ?>
                <div id="wpfd-results" class="list-results"></div>
            </div>
            <input type="hidden" name="limit" value="<?php echo esc_attr($instance['files_per_page']); ?>">
        </form>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Allow html
        echo $args['after_widget'];
    }

    /**
     * Method update instance
     *
     * @param array $new_instance Instance to replace
     * @param array $old_instance Old Instance
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['tag_filter'] = $new_instance['tag_filter'];
        $instance['cat_filter'] = $new_instance['cat_filter'];
        $instance['display_tag'] = $new_instance['display_tag'];
        $instance['creation_date'] = $new_instance['creation_date'];
        $instance['update_date'] = $new_instance['update_date'];
        $instance['files_per_page'] = $new_instance['files_per_page'];
        return $instance;
    }

    /**
     * Method form instance
     *
     * @param array $instance Instance
     *
     * @return string|void
     */
    public function form($instance)
    {
        $instance = wp_parse_args(
            (array)$instance,
            array(
                'title' => '',
                'tag_filter' => 1,
                'display_tag' => 'searchbox',
                'cat_filter' => 1,
                'creation_date' => 1,
                'update_date' => 1,
                'files_per_page' => 15
            )
        );
        $title = esc_attr($instance['title']);

        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'wpfd'); ?></label>
            <input class="widefat" id="<?php esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>"/>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('cat_filter')); ?>">
                <?php esc_html_e('Filter by category', 'wpfd'); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('cat_filter')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('cat_filter')); ?>" class="widefat">
                <option value="1"<?php selected($instance['cat_filter'], '1'); ?>><?php esc_html_e('Yes', 'wpfd'); ?></option>
                <option value="0"<?php selected($instance['cat_filter'], '0'); ?>><?php esc_html_e('No', 'wpfd'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('tag_filter')); ?>"><?php esc_html_e('Filter by tag', 'wpfd'); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('tag_filter')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('tag_filter')); ?>" class="widefat">
                <option value="1"<?php selected($instance['tag_filter'], '1'); ?>><?php esc_html_e('Yes', 'wpfd'); ?></option>
                <option value="0"<?php selected($instance['tag_filter'], '0'); ?>><?php esc_html_e('No', 'wpfd'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('display_tag')); ?>">
                <?php esc_html_e('Display tag as', 'wpfd'); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('display_tag')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('display_tag')); ?>" class="widefat">
                <option value="searchbox"<?php selected($instance['display_tag'], 'searchbox'); ?>>
                    <?php esc_html_e('Search box', 'wpfd'); ?></option>
                <option value="checkbox"<?php selected($instance['display_tag'], 'checkbox'); ?>>
                    <?php esc_html_e('Checkbox', 'wpfd'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('creation_date')); ?>">
                <?php esc_html_e('Filter by creation date', 'wpfd'); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('creation_date')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('creation_date')); ?>" class="widefat">
                <option value="1"<?php selected($instance['creation_date'], '1'); ?>>
                    <?php esc_html_e('Yes', 'wpfd'); ?></option>
                <option value="0"<?php selected($instance['creation_date'], '0'); ?>><?php esc_html_e('No', 'wpfd'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('update_date')); ?>">
                <?php esc_html_e('Filter by update date', 'wpfd'); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('update_date')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('update_date')); ?>" class="widefat">
                <option value="1"<?php selected($instance['update_date'], '1'); ?>><?php esc_html_e('Yes', 'wpfd'); ?></option>
                <option value="0"<?php selected($instance['update_date'], '0'); ?>><?php esc_html_e('No', 'wpfd'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('files_per_page')); ?>">
                <?php esc_html_e('# Files per page', 'wpfd'); ?></label>
            <select name="<?php echo esc_attr($this->get_field_name('files_per_page')); ?>"
                    id="<?php echo esc_attr($this->get_field_id('files_per_page')); ?>" class="widefat">
                <option value="5"<?php selected($instance['files_per_page'], '5'); ?>>5</option>
                <option value="10"<?php selected($instance['files_per_page'], '10'); ?>>10</option>
                <option value="15"<?php selected($instance['files_per_page'], '15'); ?>>15</option>
                <option value="20"<?php selected($instance['files_per_page'], '20'); ?>>20</option>
                <option value="25"<?php selected($instance['files_per_page'], '25'); ?>>25</option>
                <option value="30"<?php selected($instance['files_per_page'], '30'); ?>>30</option>
                <option value="50"<?php selected($instance['files_per_page'], '50'); ?>>50</option>
                <option value="100"<?php selected($instance['files_per_page'], '100'); ?>>100</option>
                <option value="-1"<?php selected($instance['files_per_page'], '-1'); ?>>All</option>
            </select>
        </p>
        <?php
    }
}

/**
 * Method widgets load
 *
 * @return void
 */
function wpfd_widgets_init()
{
    register_widget('WPFDWidgetSearch');
}

add_action('widgets_init', 'wpfd_widgets_init', 1);
