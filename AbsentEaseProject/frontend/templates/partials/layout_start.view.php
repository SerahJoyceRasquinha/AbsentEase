<?php
/**
 * Top of every logged-in page: <head>, the top bar with navigation, and <main>.
 * Variables: $page_title, $active_nav (key from backend/core/navigation.php), $stylesheets (optional)
 * Close it with include_partial('layout_end', ['scripts' => [...]]).
 */
$role  = current_role();
$user  = current_user($role);
$links = navigation_for($role, $user);
?>
<?php include_partial('html_head', ['page_title' => $page_title, 'stylesheets' => $stylesheets ?? [], 'body_class' => 'role-' . $role]); ?>

<header class="topbar">
    <div class="topbar__inner">
        <a class="brand" href="<?= e(app_url($links[0]['page'])) ?>">
            <span class="brand__mark"><?= icon('shield') ?></span>
            AbsentEase
        </a>

        <nav class="nav" id="mainNav">
            <?php foreach ($links as $link): ?>
                <a class="nav__link <?= $link['key'] === $active_nav ? 'is-active' : '' ?>" href="<?= e(app_url($link['page'])) ?>">
                    <?= icon($link['icon']) ?>
                    <?= e($link['label']) ?>
                    <?php if (!empty($link['count'])): ?><span class="nav__count"><?= (int)$link['count'] ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <button type="button" class="btn btn--ghost btn--sm nav-toggle" data-nav-toggle aria-label="Menu"><?= icon('menu') ?></button>

        <div class="user-menu">
            <div class="user-menu__who">
                <div class="user-menu__name"><?= e($user) ?></div>
                <div class="user-menu__role"><?= e(ROLE_LABELS[$role]) ?></div>
            </div>
            <span class="avatar"><?= e(mb_substr($user, 0, 1)) ?></span>
            <form action="<?= e(app_url('pages/logout.php')) ?>" method="post">
                <button type="submit" class="btn btn--ghost btn--sm" title="Log out"><?= icon('logout') ?><span class="sr-label">Log out</span></button>
            </form>
        </div>
    </div>
</header>

<main class="page">
