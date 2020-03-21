<?php

/** @var rex_addon $this */

// inject addon ressources
if (rex::isBackend() && rex::getUser() && false !== strpos(rex_be_controller::getCurrentPage(), 'cache_warmup')) {
    if ('warmup' == rex_be_controller::getCurrentPagePart(2)) {
        rex_view::addJsFile($this->getAssetsUrl('js/handlebars.min.js'));
        rex_view::addJsFile($this->getAssetsUrl('js/timer.jquery.min.js'));
    }

    rex_view::addCssFile($this->getAssetsUrl('css/cache-warmup.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js'));
}

// switch REDAXO to frontend mode before generating cache files
// this is essential to include content modification by addons, e.g. slice status on/off
rex_extension::register('PACKAGES_INCLUDED', static function (rex_extension_point $ep) {
    if ('cache_warmup/generator' == rex_be_controller::getCurrentPage()) {
        rex::setProperty('redaxo', false);
    }
}, rex_extension::EARLY);
