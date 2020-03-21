<?php

/* set up base structure */

$body = '
    <h3 class="cache-warmup__target__title"></h3>
    <hr>
    <div class="cache-warmup__target__content row"></div>
    <div class="cache-warmup__target__progressbar"></div>';

$footer = '
    <div class="row">
        <div class="col-xs-12 cache-warmup__target__footer"></div>
    </div>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'cache-warmup');
$fragment->setVar('body', $body, false);
$fragment->setVar('footer', $footer, false);
echo $fragment->parse('core/page/section.php');

/* cache warmup items JSON */
echo '<script>var cacheWarmupItems = ' . cache_warmup_writer::buildJSON(cache_warmup_selector::prepareCacheItems(true)) . ';</script>';

/* CSRF token (REX 5.5+) */
if (class_exists('rex_csrf_token')) {
    echo '<script>var cacheWarmupToken = "' . rex_csrf_token::factory('cache_warmup_generator')->getValue() . '";</script>';
}

/* disable minibar (REX 5.7+) */
if (class_exists('rex_minibar') && null === rex_minibar::getInstance()->isActive()) {
    rex_minibar::getInstance()->setActive(false);
}
