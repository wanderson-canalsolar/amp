<?php
if(!defined("ABSPATH")) die("Shit happens!");

$pm_user = new \PrivateMessage\User\User();
$following = $pm_user->isFollowing($user->ID);
$whitelist = $pm_user->inContactList($user->ID);
$blocked = $pm_user->isBlocked($user->ID);
?>
<div class="card text-center wpdm-profile-card">
    <div class="card-body">
        <a class="card-link" href="<?= home_url("profile/{$user->user_nicename}/"); ?>">
        <?php echo get_avatar($user->ID, 512, '', $user->display_name, ['force_display' => true, 'class' => 'wpdm-profile-card-pic']); ?>
        <h3><?= $user->display_name; ?></h3>
        </a>
        <small><?= get_user_meta($user->ID, '__wpdm_title', true); ?></small>
        <small class="text-secondary d-block"><?= \PrivateMessage\__\__::valueof($user->roles, 0); ?></small>

    </div>
    <div class="card-footer text-center">
        <div class="d-inline-block">
            <div class="dropdown dropup">
                <button class="btn btn-secondary btn-sm dropdown-toggle" aria-haspopup="true" type="button" id="dropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="far fa-sun"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= home_url("profile/{$user->user_nicename}/"); ?>"><?= __('View Profile', WPDM_TEXT_DOMAIN); ?></a></li>
                    <li><a class="dropdown-item action-contact" data-action="<?= $whitelist ? 'remove' : 'add'; ?>_contact" data-user="<?= $user->ID; ?>" href="#"><?= $whitelist ? __('Cancel Whitelist', WPDM_TEXT_DOMAIN) : __('Whitelist Contact', WPDM_TEXT_DOMAIN); ?></a></li>
                    <li><a class="dropdown-item action-contact" data-action="<?= $blocked ? 'unblock' : 'block'; ?>_contact" data-user="<?= $user->ID; ?>" href="#"><?= $blocked ? __('Unblock Contact', WPDM_TEXT_DOMAIN) : __('Block Contact', WPDM_TEXT_DOMAIN); ?></a></li>
                </ul>
            </div>
        </div>
        <!-- button class="btn btn-<?= $following ? 'danger' : 'info' ?> btn-sm action-contact" data-action="<?= $following ? 'un' : '' ?>follow_user" data-user="<?= $user->ID; ?>"><?= $following ? __('Unfollow', WPDM_TEXT_DOMAIN) : __('Follow', WPDM_TEXT_DOMAIN); ?></button -->
        <?= do_shortcode("[pm_to_user color='primary btn-sm' id={$user->ID} title='Contact {$user->display_name}' label='<i class=\"fa fa-paper-plane\"></i> Message']"); ?>

    </div>
</div>
