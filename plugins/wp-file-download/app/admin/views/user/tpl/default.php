<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_5\Factory;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Application;

$modelConfig = Model::getInstance('config');
$params      = $modelConfig->getConfig();
$app         = Factory::getApplication();
if (!wpfd_can_edit_permission() || (isset($_REQUEST['wpfd_security']) && !wp_verify_nonce($_REQUEST['wpfd_security'], 'wpfd_users'))) {
    wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
}
$userSearch     = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';
$userRole       = isset($_REQUEST['role']) ? $_REQUEST['role'] : '';
$fieldtype      = isset($_REQUEST['fieldtype']) ? $_REQUEST['fieldtype'] : '';
$cataction      = isset($_REQUEST['cataction']) ? $_REQUEST['cataction'] : '';
$users_per_page = -1;
$pagenum        = isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : 0;
$fPaged         = max(1, $pagenum);
$listCanview    = isset($_REQUEST['listCanview']) ? ($_REQUEST['listCanview']) : 0;
$listCanview    = array_map('intval', explode(',', $listCanview));

if ('none' === $userRole) {
    $args = array(
        'number'  => $users_per_page,
        'offset'  => ($fPaged - 1) * $users_per_page,
        'include' => wp_get_users_with_no_role(),
        'search'  => $userSearch,
        'fields'  => 'all_with_meta'
    );
} else {
    $args = array(
        'number' => $users_per_page,
        'offset' => ($fPaged - 1) * $users_per_page,
        'role'   => $userRole,
        'search' => $userSearch,
        'fields' => 'all_with_meta'
    );
}
if ('' !== $args['search']) {
    $args['search'] = '*' . $args['search'] . '*';
}
if (isset($_REQUEST['orderby'])) {
    $args['orderby'] = $_REQUEST['orderby'];
}
if (isset($_REQUEST['order'])) {
    $args['order'] = $_REQUEST['order'];
}
// Query the user IDs for this page
$wp_user_search = new WP_User_Query($args);
$this->items    = $wp_user_search->get_results();
?>
    <div class="wfd-list-user">
        <form method="get">
            <input type="hidden" name="page" value="wpfd">
            <input type="hidden" name="task" value="user.display">
            <input type="hidden" name="noheader" value="true">
            <?php wp_nonce_field('wpfd_users', 'wpfd_security'); ?>
            <input type="hidden" name="fieldtype" class="fieldtype" value="<?php echo esc_html($fieldtype); ?>">
            <input type="hidden" name="cataction" class="cataction" value="<?php echo esc_html($cataction); ?>">
            <div class="search-box">
                <input title="" type="search" id="user-search-input" name="s" value="<?php echo esc_html($userSearch); ?>">
                <input type="submit" id="search-submit" class="button" value="<?php esc_html_e('Search Users', 'wpfd'); ?>">
            </div>
            <ul class="subsubsub">
                <?php
                $role_links = wpfd_filter_role_links($userRole, $cataction);
                foreach ($role_links as $userRole => $role_link) {
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape inside wpfd_filter_role_links()
                    echo '<li class="' . esc_attr($userRole) . '">' . $role_link . '</li>';
                }
                ?>
            </ul>
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- nothing need to escape
            echo (!$cataction) ?
                '<div class="insert-box">
                            <input type="button" class="button btn-insert-user" value="Insert">
                      </div>' : '';
            ?>
            <table class="widefat fixed">
                <thead>
                <tr>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- nothing need to escape
                    echo (!$cataction) ?
                        '<th scope="col" id="name" class="manage-column" width="8%">
                            <input type="checkbox" id="select_all" />
                          </th>' : '';
                    ?>
                    <th scope="col" id="name" class="manage-column"><span><?php esc_html_e('Name', 'wpfd'); ?></span></th>
                    <th scope="col" id="username" class="manage-column"><span><?php esc_html_e('Username', 'wpfd'); ?></span></th>
                    <th scope="col" id="email" class="manage-column"><span><?php esc_html_e('Email', 'wpfd'); ?></span></th>
                    <th scope="col" id="role" class="manage-column"><?php esc_html_e('Role', 'wpfd'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                if (is_countable($this->items) && count($this->items)) {
                    foreach ($this->items as $userid => $user_object) {
                        $checked = '';
                        if (in_array($user_object->ID, $listCanview)) {
                            $checked = 'checked';
                        }
                        echo '<tr>';
                        echo (!$cataction) ?
                            '<td><input ' . esc_attr($checked) . ' type="checkbox" name="cb-selected"
                              class="checkbox" value="' . (int) esc_attr($user_object->ID) . '"/>
                              </td>' : '';
                        echo '<td class="name column-name">
                                 <a class="pointer button-select-user" href="#" 
                                    data-name="' . esc_attr($user_object->display_name) . '"
                                    data-user-value="' . (int) esc_attr($user_object->ID) . '"
                                    data-user-name="' . esc_attr($user_object->user_login) . '">'
                             . esc_html($user_object->display_name) . '
                                   </a>
                              </td>';
                        echo '<td class="username column-username">
                                    <strong>' . esc_html($user_object->user_login) . '</strong>
                              </td>';
                        echo '<td class="email column-email">' . esc_html($user_object->user_email) . '</td>';
                        $role_list = array();
                        global $wp_roles;
                        foreach ($user_object->roles as $userRole) {
                            if (isset($wp_roles->role_names[$userRole])) {
                                $role_list[$userRole] = translate_user_role($wp_roles->role_names[$userRole]);
                            }
                        }
                        if (empty($role_list)) {
                            $role_list['none'] = _x('None', 'no user roles', 'wpfd');
                        }
                        $roles_list = implode(', ', $role_list);
                        echo '<td class="role column-role">' . esc_html($roles_list) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="4"> ';
                    esc_html_e('No users found.', 'wpfd');
                    echo '</td></tr>';
                }
                ?>
                </tbody>
            </table>
        </form>
    </div>
    <script type="text/javascript">
        wpfdajaxurl = "<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>";
    </script>

<?php
/**
 * Filter role links
 *
 * @param string $role      Role key
 * @param string $cataction Cat action
 *
 * @return array
 */
function wpfd_filter_role_links($role, $cataction)
{
    $wp_roles      = wp_roles();
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- it checked above
    $fieldtype     = isset($_REQUEST['fieldtype']) ? $_REQUEST['fieldtype'] : '';
    $catactionuUrl = '';
    if ($cataction && $cataction !== '') {
        $catactionuUrl = '&cataction=true';
    }
    $url           = admin_url() . 'admin.php?page=wpfd&task=user.display&noheader=true&fieldtype=' . esc_html($fieldtype) . $catactionuUrl;
    $users_of_blog = count_users();

    $total_users = $users_of_blog['total_users'];
    $avail_roles =& $users_of_blog['avail_roles'];
    unset($users_of_blog);

    $class             = empty($role) ? ' class="current"' : '';
    $role_links        = array();
    $role_links['all'] = '<a href="' . $url . '"' . $class. '>' . sprintf(_nx('All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users', 'wpfd'), number_format_i18n($total_users)) . '</a>';
    foreach ($wp_roles->get_names() as $this_role => $name) {
        if (!isset($avail_roles[$this_role])) {
            continue;
        }
        $class = '';
        if ($this_role === $role) {
            $class = ' class="current"';
        }
        $name = translate_user_role($name);
        /* translators: User role name with count */
        $name                   = sprintf(
            __('%1$s <span class="count">(%2$s)</span>', 'wpfd'),
            $name,
            number_format_i18n($avail_roles[$this_role])
        );
        $role_links[$this_role] = '<a href="' . esc_url(add_query_arg('role', $this_role, $url)) . '"' . $class. '>' . $name . '</a>';
    }

    if (!empty($avail_roles['none'])) {
        $class = '';
        if ('none' === $role) {
            $class = ' class="current"';
        }
        $name = esc_html__('No role', 'wpfd');
        /* translators: User role name with count */
        $name               = sprintf(
            __('%1$s <span class="count">(%2$s)</span>', 'wpfd'),
            $name,
            number_format_i18n($avail_roles['none'])
        );
        $role_links['none'] = '<a href="' . esc_url(add_query_arg('role', 'none', $url)) . '"' . $class. '>' . $name . '</a>';
    }

    return $role_links;
}
