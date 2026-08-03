<?php
include "config.php"
?>
<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <title>Welcome to <?php echo $name; ?></title>

    <!-- All in One SEO 4.1.0.2 -->
    <meta name="description"
        content="<?php echo $name; ?> is a regional offshore bank in <?php echo $country; ?>." />
    <link rel="canonical" href="index.php" />


    <!-- All in One SEO -->

    <link rel='dns-prefetch' href='http://fonts.googleapis.com/' />
    <link rel='dns-prefetch' href='http://s.w.org/' />
    <link rel="alternate" type="application/rss+xml" title="<?php echo $name; ?> &raquo; Feed"
        href="feed/index.php" />
    <link rel="alternate" type="application/rss+xml" title="<?php echo $name; ?> &raquo; Comments Feed"
        href="comments/feed/index.php" />
    <style>
        img.wp-smiley,
        img.emoji {
            display: inline !important;
            border: none !important;
            box-shadow: none !important;
            height: 1em !important;
            width: 1em !important;
            margin: 0 .07em !important;
            vertical-align: -0.1em !important;
            background: none !important;
            padding: 0 !important;
        }
    </style>
    <link rel='stylesheet' id='astra-theme-css-css'
        href='wp-content/themes/astra/assets/css/minified/style.mind617.css?ver=3.3.2' media='all' />
    <style id='astra-theme-css-inline-css'>
        html {
            font-size: 100%;
        }

        a,
        .page-title {
            color: #00548e;
        }

        a:hover,
        a:focus {
            color: #0099cc;
        }

        body,
        button,
        input,
        select,
        textarea,
        .ast-button,
        .ast-custom-button {
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            font-size: 16px;
            font-size: 1rem;
            line-height: 1.4;
        }

        blockquote {
            color: #000000;
        }

        p,
        .entry-content p {
            margin-bottom: 1.4em;
        }

        h1,
        .entry-content h1,
        .entry-content h1 a,
        h2,
        .entry-content h2,
        .entry-content h2 a,
        h3,
        .entry-content h3,
        .entry-content h3 a,
        h4,
        .entry-content h4,
        .entry-content h4 a,
        h5,
        .entry-content h5,
        .entry-content h5 a,
        h6,
        .entry-content h6,
        .entry-content h6 a,
        .site-title,
        .site-title a {
            text-transform: capitalize;
        }

        .site-title {
            font-size: 35px;
            font-size: 2.1875rem;
        }

        header .custom-logo-link img {
            max-width: 329px;
        }

        .astra-logo-svg {
            width: 329px;
        }

        .ast-archive-description .ast-archive-title {
            font-size: 40px;
            font-size: 2.5rem;
        }

        .site-header .site-description {
            font-size: 15px;
            font-size: 0.9375rem;
        }

        .entry-title {
            font-size: 30px;
            font-size: 1.875rem;
        }

        h1,
        .entry-content h1,
        .entry-content h1 a {
            font-size: 30px;
            font-size: 1.875rem;
            text-transform: capitalize;
        }

        h2,
        .entry-content h2,
        .entry-content h2 a {
            font-size: 24px;
            font-size: 1.5rem;
            line-height: 1;
            text-transform: capitalize;
        }

        h3,
        .entry-content h3,
        .entry-content h3 a {
            font-size: 18px;
            font-size: 1.125rem;
            text-transform: capitalize;
        }

        h4,
        .entry-content h4,
        .entry-content h4 a {
            font-size: 18px;
            font-size: 1.125rem;
        }

        h5,
        .entry-content h5,
        .entry-content h5 a {
            font-size: 18px;
            font-size: 1.125rem;
        }

        h6,
        .entry-content h6,
        .entry-content h6 a {
            font-size: 15px;
            font-size: 0.9375rem;
        }

        .ast-single-post .entry-title,
        .page-title {
            font-size: 30px;
            font-size: 1.875rem;
        }

        ::selection {
            background-color: #0099cc;
            color: #ffffff;
        }

        body,
        h1,
        .entry-title a,
        .entry-content h1,
        .entry-content h1 a,
        h2,
        .entry-content h2,
        .entry-content h2 a,
        h3,
        .entry-content h3,
        .entry-content h3 a,
        h4,
        .entry-content h4,
        .entry-content h4 a,
        h5,
        .entry-content h5,
        .entry-content h5 a,
        h6,
        .entry-content h6,
        .entry-content h6 a {
            color: #3a3a3a;
        }

        .tagcloud a:hover,
        .tagcloud a:focus,
        .tagcloud a.current-item {
            color: #ffffff;
            border-color: #00548e;
            background-color: #00548e;
        }

        input:focus,
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="url"]:focus,
        input[type="password"]:focus,
        input[type="reset"]:focus,
        input[type="search"]:focus,
        textarea:focus {
            border-color: #00548e;
        }

        input[type="radio"]:checked,
        input[type=reset],
        input[type="checkbox"]:checked,
        input[type="checkbox"]:hover:checked,
        input[type="checkbox"]:focus:checked,
        input[type=range]::-webkit-slider-thumb {
            border-color: #00548e;
            background-color: #00548e;
            box-shadow: none;
        }

        .site-footer a:hover+.post-count,
        .site-footer a:focus+.post-count {
            background: #00548e;
            border-color: #00548e;
        }

        .single .nav-links .nav-previous,
        .single .nav-links .nav-next {
            color: #00548e;
        }

        .entry-meta,
        .entry-meta * {
            line-height: 1.45;
            color: #00548e;
        }

        .entry-meta a:hover,
        .entry-meta a:hover *,
        .entry-meta a:focus,
        .entry-meta a:focus *,
        .page-links>.page-link,
        .page-links .page-link:hover,
        .post-navigation a:hover {
            color: #0099cc;
        }

        .widget-title {
            font-size: 22px;
            font-size: 1.375rem;
            color: #3a3a3a;
        }

        #cat option,
        .secondary .calendar_wrap thead a,
        .secondary .calendar_wrap thead a:visited {
            color: #00548e;
        }

        .secondary .calendar_wrap #today,
        .ast-progress-val span {
            background: #00548e;
        }

        .secondary a:hover+.post-count,
        .secondary a:focus+.post-count {
            background: #00548e;
            border-color: #00548e;
        }

        .calendar_wrap #today>a {
            color: #ffffff;
        }

        .page-links .page-link,
        .single .post-navigation a {
            color: #00548e;
        }

        .single .ast-author-details .author-title {
            color: #0099cc;
        }

        .main-header-menu .menu-link,
        .ast-header-custom-item a {
            color: #3a3a3a;
        }

        .main-header-menu .menu-item:hover>.menu-link,
        .main-header-menu .menu-item:hover>.ast-menu-toggle,
        .main-header-menu .ast-masthead-custom-menu-items a:hover,
        .main-header-menu .menu-item.focus>.menu-link,
        .main-header-menu .menu-item.focus>.ast-menu-toggle,
        .main-header-menu .current-menu-item>.menu-link,
        .main-header-menu .current-menu-ancestor>.menu-link,
        .main-header-menu .current-menu-item>.ast-menu-toggle,
        .main-header-menu .current-menu-ancestor>.ast-menu-toggle {
            color: #00548e;
        }

        .ast-header-break-point .ast-mobile-menu-buttons-minimal.menu-toggle {
            background: transparent;
            color: #8d2828;
        }

        .ast-header-break-point .ast-mobile-menu-buttons-outline.menu-toggle {
            background: transparent;
            border: 1px solid #8d2828;
            color: #8d2828;
        }

        .ast-header-break-point .ast-mobile-menu-buttons-fill.menu-toggle {
            background: #8d2828;
            color: #ffffff;
        }

        .ast-small-footer>.ast-footer-overlay {
            background-color: #00548e;
            ;
        }

        .footer-adv .footer-adv-overlay {
            border-top-style: solid;
            border-top-color: #00548e;
        }

        .footer-adv-overlay {
            background-color: #00548e;
            ;
        }

        .wp-block-buttons.aligncenter {
            justify-content: center;
        }

        @media (max-width:782px) {
            .entry-content .wp-block-columns .wp-block-column {
                margin-left: 0px;
            }
        }

        @media (max-width:768px) {

            .ast-separate-container .ast-article-post,
            .ast-separate-container .ast-article-single {
                padding: 1.5em 2.14em;
            }

            .ast-separate-container #primary,
            .ast-separate-container #secondary {
                padding: 1.5em 0;
            }

            #primary,
            #secondary {
                padding: 1.5em 0;
                margin: 0;
            }

            .ast-left-sidebar #content>.ast-container {
                display: flex;
                flex-direction: column-reverse;
                width: 100%;
            }

            .ast-author-box img.avatar {
                margin: 20px 0 0 0;
            }
        }

        @media (min-width:769px) {

            .ast-separate-container.ast-right-sidebar #primary,
            .ast-separate-container.ast-left-sidebar #primary {
                border: 0;
            }

            .search-no-results.ast-separate-container #primary {
                margin-bottom: 4em;
            }
        }

        @media (min-width:769px) {
            .ast-right-sidebar #primary {
                border-right: 1px solid #eee;
            }

            .ast-left-sidebar #primary {
                border-left: 1px solid #eee;
            }
        }

        .menu-toggle,
        button,
        .ast-button,
        .ast-custom-button,
        .button,
        input#submit,
        input[type="button"],
        input[type="submit"],
        input[type="reset"] {
            color: #ffffff;
            border-color: #8d2828;
            background-color: #8d2828;
            border-radius: 2px;
            padding-top: 15px;
            padding-right: 35px;
            padding-bottom: 15px;
            padding-left: 35px;
            font-family: inherit;
            font-weight: inherit;
        }

        button:focus,
        .menu-toggle:hover,
        button:hover,
        .ast-button:hover,
        .ast-custom-button:hover .button:hover,
        .ast-custom-button:hover,
        input[type=reset]:hover,
        input[type=reset]:focus,
        input#submit:hover,
        input#submit:focus,
        input[type="button"]:hover,
        input[type="button"]:focus,
        input[type="submit"]:hover,
        input[type="submit"]:focus {
            color: #ffffff;
            background-color: #1e73be;
            border-color: #1e73be;
        }

        @media (min-width:544px) {
            .ast-container {
                max-width: 100%;
            }
        }

        @media (max-width:544px) {

            .ast-separate-container .ast-article-post,
            .ast-separate-container .ast-article-single,
            .ast-separate-container .comments-title,
            .ast-separate-container .ast-archive-description {
                padding: 1.5em 1em;
            }

            .ast-separate-container #content .ast-container {
                padding-left: 0.54em;
                padding-right: 0.54em;
            }

            .ast-separate-container .ast-comment-list li.depth-1 {
                padding: 1.5em 1em;
                margin-bottom: 1.5em;
            }

            .ast-separate-container .ast-comment-list .bypostauthor {
                padding: .5em;
            }

            .ast-search-menu-icon.ast-dropdown-active .search-field {
                width: 170px;
            }
        }

        @media (max-width:768px) {
            .ast-mobile-header-stack .main-header-bar .ast-search-menu-icon {
                display: inline-block;
            }

            .ast-header-break-point.ast-header-custom-item-outside .ast-mobile-header-stack .main-header-bar .ast-search-icon {
                margin: 0;
            }

            .ast-comment-avatar-wrap img {
                max-width: 2.5em;
            }

            .ast-separate-container .ast-comment-list li.depth-1 {
                padding: 1.5em 2.14em;
            }

            .ast-separate-container .comment-respond {
                padding: 2em 2.14em;
            }

            .ast-comment-meta {
                padding: 0 1.8888em 1.3333em;
            }
        }

        @media (max-width:768px) {
            .ast-archive-description .ast-archive-title {
                font-size: 40px;
            }

            .entry-title {
                font-size: 30px;
            }

            h1,
            .entry-content h1,
            .entry-content h1 a {
                font-size: 30px;
            }

            h2,
            .entry-content h2,
            .entry-content h2 a {
                font-size: 25px;
            }

            h3,
            .entry-content h3,
            .entry-content h3 a {
                font-size: 20px;
            }

            .ast-single-post .entry-title,
            .page-title {
                font-size: 30px;
            }
        }

        @media (max-width:544px) {
            .ast-archive-description .ast-archive-title {
                font-size: 40px;
            }

            .entry-title {
                font-size: 30px;
            }

            h1,
            .entry-content h1,
            .entry-content h1 a {
                font-size: 30px;
            }

            h2,
            .entry-content h2,
            .entry-content h2 a {
                font-size: 25px;
            }

            h3,
            .entry-content h3,
            .entry-content h3 a {
                font-size: 20px;
            }

            .ast-single-post .entry-title,
            .page-title {
                font-size: 30px;
            }
        }

        @media (max-width:768px) {
            html {
                font-size: 91.2%;
            }
        }

        @media (max-width:544px) {
            html {
                font-size: 91.2%;
            }
        }

        @media (min-width:769px) {
            .ast-container {
                max-width: 1240px;
            }
        }

        @font-face {
            font-family: "Astra";
            src: url(wp-content/themes/astra/assets/fonts/astra.woff) format("woff"), url(<?php echo $url; ?>/wp-content/themes/astra/assets/fonts/astra.ttf) format("truetype"), url(<?php echo $url; ?>/wp-content/themes/astra/assets/fonts/astra.svg#astra) format("svg");
            font-weight: normal;
            font-style: normal;
            font-display: fallback;
        }

        @media (max-width:921px) {
            .main-header-bar .main-header-bar-navigation {
                display: none;
            }
        }

        .ast-desktop .main-header-menu.submenu-with-border .sub-menu,
        .ast-desktop .main-header-menu.submenu-with-border .astra-full-megamenu-wrapper {
            border-color: #eaeaea;
        }

        .ast-desktop .main-header-menu.submenu-with-border .sub-menu {
            border-top-width: 1px;
            border-right-width: 1px;
            border-left-width: 1px;
            border-bottom-width: 1px;
            border-style: solid;
        }

        .ast-desktop .main-header-menu.submenu-with-border .sub-menu .sub-menu {
            top: -1px;
        }

        .ast-desktop .main-header-menu.submenu-with-border .sub-menu .menu-link,
        .ast-desktop .main-header-menu.submenu-with-border .children .menu-link {
            border-bottom-width: 1px;
            border-style: solid;
            border-color: #eaeaea;
        }

        @media (min-width:769px) {

            .main-header-menu .sub-menu .menu-item.ast-left-align-sub-menu:hover>.sub-menu,
            .main-header-menu .sub-menu .menu-item.ast-left-align-sub-menu.focus>.sub-menu {
                margin-left: -2px;
            }
        }

        .ast-small-footer {
            border-top-style: solid;
            border-top-width: 0px;
            border-top-color: #fff;
        }

        .ast-breadcrumbs .trail-browse,
        .ast-breadcrumbs .trail-items,
        .ast-breadcrumbs .trail-items li {
            display: inline-block;
            margin: 0;
            padding: 0;
            border: none;
            background: inherit;
            text-indent: 0;
        }

        .ast-breadcrumbs .trail-browse {
            font-size: inherit;
            font-style: inherit;
            font-weight: inherit;
            color: inherit;
        }

        .ast-breadcrumbs .trail-items {
            list-style: none;
        }

        .trail-items li::after {
            padding: 0 0.3em;
            content: "\00bb";
        }

        .trail-items li:last-of-type::after {
            display: none;
        }

        .ast-header-break-point .main-header-bar {
            border-bottom-width: 0;
        }

        @media (min-width:769px) {
            .main-header-bar {
                border-bottom-width: 0;
            }
        }

        .ast-flex {
            -webkit-align-content: center;
            -ms-flex-line-pack: center;
            align-content: center;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .main-header-bar {
            padding: 1em 0;
        }

        .ast-site-identity {
            padding: 0;
        }

        .header-main-layout-1 .ast-flex.main-header-container,
        .header-main-layout-3 .ast-flex.main-header-container {
            -webkit-align-content: center;
            -ms-flex-line-pack: center;
            align-content: center;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .header-main-layout-1 .ast-flex.main-header-container,
        .header-main-layout-3 .ast-flex.main-header-container {
            -webkit-align-content: center;
            -ms-flex-line-pack: center;
            align-content: center;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .main-header-menu .sub-menu .menu-item.menu-item-has-children>.menu-link:after {
            position: absolute;
            right: 1em;
            top: 50%;
            transform: translate(0, -50%) rotate(270deg);
        }

        .ast-header-break-point .main-header-bar .main-header-bar-navigation .page_item_has_children>.ast-menu-toggle::before,
        .ast-header-break-point .main-header-bar .main-header-bar-navigation .menu-item-has-children>.ast-menu-toggle::before,
        .ast-mobile-popup-drawer .main-header-bar-navigation .menu-item-has-children>.ast-menu-toggle::before,
        .ast-header-break-point .ast-mobile-header-wrap .main-header-bar-navigation .menu-item-has-children>.ast-menu-toggle::before {
            font-weight: bold;
            content: "\e900";
            font-family: Astra;
            text-decoration: inherit;
            display: inline-block;
        }

        .ast-header-break-point .main-navigation ul.sub-menu .menu-item .menu-link:before {
            content: "\e900";
            font-family: Astra;
            font-size: .65em;
            text-decoration: inherit;
            display: inline-block;
            transform: translate(0, -2px) rotateZ(270deg);
            margin-right: 5px;
        }

        .widget_search .search-form:after {
            font-family: Astra;
            font-size: 1.2em;
            font-weight: normal;
            content: "\e8b6";
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translate(0, -50%);
        }

        .astra-search-icon::before {
            content: "\e8b6";
            font-family: Astra;
            font-style: normal;
            font-weight: normal;
            text-decoration: inherit;
            text-align: center;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .main-header-bar .main-header-bar-navigation .page_item_has_children>a:after,
        .main-header-bar .main-header-bar-navigation .menu-item-has-children>a:after,
        .site-header-focus-item .main-header-bar-navigation .menu-item-has-children>.menu-link:after {
            content: "\e900";
            display: inline-block;
            font-family: Astra;
            font-size: .6rem;
            font-weight: bold;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin-left: 10px;
            line-height: normal;
        }

        .ast-mobile-popup-drawer .main-header-bar-navigation .ast-submenu-expanded>.ast-menu-toggle::before {
            transform: rotateX(180deg);
        }

        .ast-header-break-point .main-header-bar-navigation .menu-item-has-children>.menu-link:after {
            display: none;
        }

        .ast-desktop .astra-menu-animation-slide-up>.menu-item>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item>.sub-menu .sub-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(.5em);
            transition: visibility .2s ease, transform .2s ease
        }

        .ast-desktop .astra-menu-animation-slide-up>.menu-item .menu-item.focus>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item .menu-item:hover>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item.focus>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item.focus>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item:hover>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-up>.menu-item:hover>.sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity .2s ease, visibility .2s ease, transform .2s ease
        }

        .ast-desktop .astra-menu-animation-slide-up>.full-width-mega.menu-item.focus>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-up>.full-width-mega.menu-item:hover>.astra-full-megamenu-wrapper {
            -js-display: flex;
            display: flex
        }

        .ast-desktop .astra-menu-animation-slide-down>.menu-item>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item>.sub-menu .sub-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-.5em);
            transition: visibility .2s ease, transform .2s ease
        }

        .ast-desktop .astra-menu-animation-slide-down>.menu-item .menu-item.focus>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item .menu-item:hover>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item.focus>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item.focus>.sub-menu,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item:hover>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-down>.menu-item:hover>.sub-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity .2s ease, visibility .2s ease, transform .2s ease
        }

        .ast-desktop .astra-menu-animation-slide-down>.full-width-mega.menu-item.focus>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-slide-down>.full-width-mega.menu-item:hover>.astra-full-megamenu-wrapper {
            -js-display: flex;
            display: flex
        }

        .ast-desktop .astra-menu-animation-fade>.menu-item>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-fade>.menu-item>.sub-menu,
        .ast-desktop .astra-menu-animation-fade>.menu-item>.sub-menu .sub-menu {
            opacity: 0;
            visibility: hidden;
            transition: opacity ease-in-out .3s
        }

        .ast-desktop .astra-menu-animation-fade>.menu-item .menu-item.focus>.sub-menu,
        .ast-desktop .astra-menu-animation-fade>.menu-item .menu-item:hover>.sub-menu,
        .ast-desktop .astra-menu-animation-fade>.menu-item.focus>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-fade>.menu-item.focus>.sub-menu,
        .ast-desktop .astra-menu-animation-fade>.menu-item:hover>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-fade>.menu-item:hover>.sub-menu {
            opacity: 1;
            visibility: visible;
            transition: opacity ease-in-out .3s
        }

        .ast-desktop .astra-menu-animation-fade>.full-width-mega.menu-item.focus>.astra-full-megamenu-wrapper,
        .ast-desktop .astra-menu-animation-fade>.full-width-mega.menu-item:hover>.astra-full-megamenu-wrapper {
            -js-display: flex;
            display: flex
        }
    </style>
    <link rel='stylesheet' id='astra-google-fonts-css'
        href='http://fonts.googleapis.com/css?family=Roboto%3A400%2C&amp;display=fallback&amp;ver=3.3.2'
        media='all' />
    <link rel='stylesheet' id='wp-block-library-css'
        href='wp-includes/css/dist/block-library/style.minc62d.css?ver=c4be1ef428378af680af545453e0eeea'
        media='all' />
    <link rel='stylesheet' id='tve_style_family_tve_flt-css'
        href='wp-content/plugins/thrive-visual-editor/editor/css/thrive_flata19e.css?ver=2.6.9' media='all' />
    <link rel='stylesheet' id='astra-addon-css-css'
        href='wp-content/uploads/astra-addon/astra-addon-6078642b290bc6-808814413d36.css?ver=3.3.1' media='all' />
    <style id='astra-addon-css-inline-css'>
        .ast-separate-container .blog-layout-1,
        .ast-separate-container .blog-layout-2,
        .ast-separate-container .blog-layout-3 {
            background-color: transparent;
            background-image: none;
        }

        .ast-separate-container .ast-article-post {
            background-color: #ffffff;
            ;
        }

        .ast-separate-container .ast-article-single,
        .ast-separate-container .comment-respond,
        .ast-separate-container .ast-comment-list li,
        .ast-separate-container .ast-woocommerce-container,
        .ast-separate-container .error-404,
        .ast-separate-container .no-results,
        .single.ast-separate-container .ast-author-meta,
        .ast-separate-container .related-posts-title-wrapper,
        .ast-separate-container.ast-two-container #secondary .widget,
        .ast-separate-container .comments-count-wrapper,
        .ast-box-layout.ast-plain-container .site-content,
        .ast-padded-layout.ast-plain-container .site-content {
            background-color: #ffffff;
            ;
        }

        .main-header-menu .menu-link:hover,
        .ast-header-custom-item a:hover,
        .main-header-menu .menu-item:hover>.menu-link,
        .main-header-menu .menu-item.focus>.menu-link {
            color: #0099cc;
        }

        .main-header-menu .ast-masthead-custom-menu-items a:hover,
        .main-header-menu .menu-item:hover>.ast-menu-toggle,
        .main-header-menu .menu-item.focus>.ast-menu-toggle {
            color: #0099cc;
        }

        .ast-above-header-menu .sub-menu .menu-item.menu-item-has-children>.menu-link::after {
            position: absolute;
            right: 1em;
            top: 50%;
            transform: translate(0, -50%) rotate(270deg);
        }

        .ast-desktop .ast-above-header .menu-item-has-children>.menu-link:after {
            content: "\e900";
            display: inline-block;
            font-family: 'Astra';
            font-size: .6rem;
            font-weight: bold;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin-left: 10px;
            line-height: normal;
        }

        .ast-header-break-point .ast-above-header-navigation .menu-item-has-children>.ast-menu-toggle::before {
            content: "\e900";
            font-family: 'Astra';
            text-decoration: inherit;
            display: inline-block;
        }

        .ast-header-break-point .ast-above-header-navigation .sub-menu .menu-item .menu-link:before {
            content: "\e900";
            font-family: 'Astra';
            text-decoration: inherit;
            display: inline-block;
            font-size: .65em;
            transform: translate(0, -2px) rotateZ(270deg);
            margin-right: 5px;
        }

        .ast-above-header {
            border-bottom-width: 0;
            line-height: 40px;
        }

        .ast-header-break-point .ast-above-header-merged-responsive .ast-above-header {
            border-bottom-width: 0;
        }

        .ast-above-header .ast-search-menu-icon .search-field {
            max-height: 34px;
            padding-top: .35em;
            padding-bottom: .35em;
        }

        .ast-above-header-section-wrap {
            min-height: 40px;
        }

        .ast-above-header-menu .sub-menu,
        .ast-above-header-menu .sub-menu .menu-link,
        .ast-above-header-menu .astra-full-megamenu-wrapper {
            border-color: #eaeaea;
        }

        .ast-header-break-point .ast-below-header-merged-responsive .below-header-user-select,
        .ast-header-break-point .ast-below-header-merged-responsive .below-header-user-select .widget,
        .ast-header-break-point .ast-below-header-merged-responsive .below-header-user-select .widget-title {
            color: #3a3a3a;
        }

        .ast-header-break-point .ast-below-header-merged-responsive .below-header-user-select a {
            color: #00548e;
        }

        .ast-above-header {
            background-color: #f6f5f5;
            ;
        }

        .ast-header-break-point .ast-above-header-merged-responsive .ast-above-header {
            background-color: #f6f5f5;
        }

        .ast-header-break-point .ast-above-header-section-separated .ast-above-header-navigation,
        .ast-header-break-point .ast-above-header-section-separated .ast-above-header-navigation ul {
            background-color: #f6f5f5;
        }

        .ast-above-header-section .user-select a,
        .ast-above-header-section .widget a {
            color: #333333;
        }

        .ast-above-header-section .search-field:focus {
            border-color: #333333;
        }

        .ast-above-header-section .user-select a:hover,
        .ast-above-header-section .widget a:hover {
            color: #0099cc;
        }

        .ast-above-header-navigation a {
            color: #666666;
        }

        @media (max-width:921px) {

            .ast-above-header-navigation,
            .ast-above-header-hide-on-mobile .ast-above-header-wrap {
                display: none;
            }
        }

        .ast-desktop .ast-above-header-menu.submenu-with-border .sub-menu .menu-link {
            border-bottom-width: 1px;
            border-style: solid;
            border-color: #eaeaea;
        }

        .ast-desktop .ast-above-header-menu.submenu-with-border .sub-menu .sub-menu {
            top: -1px;
        }

        .ast-desktop .ast-above-header-menu.submenu-with-border .sub-menu {
            border-top-width: 1px;
            border-left-width: 1px;
            border-right-width: 1px;
            border-bottom-width: 1px;
            border-style: solid;
        }

        @media (min-width:769px) {

            .ast-above-header-menu .sub-menu .menu-item.ast-left-align-sub-menu:hover>.sub-menu,
            .ast-above-header-menu .sub-menu .menu-item.ast-left-align-sub-menu.focus>.sub-menu {
                margin-left: -2px;
            }
        }

        #ast-scroll-top {
            background-color: #00548e;
            font-size: 15px;
            font-size: 0.9375rem;
        }

        .ast-scroll-top-icon::before {
            content: "\e900";
            font-family: Astra;
            text-decoration: inherit;
        }

        .ast-scroll-top-icon {
            transform: rotate(180deg);
        }

        .site-title,
        .site-title a {
            font-family: 'Roboto', sans-serif;
            text-transform: capitalize;
        }

        .site-header .site-description {
            text-transform: capitalize;
        }

        .secondary .widget-title {
            font-family: 'Roboto', sans-serif;
            text-transform: capitalize;
        }

        .secondary .widget>*:not(.widget-title) {
            font-family: 'Roboto', sans-serif;
        }

        .ast-single-post .entry-title,
        .page-title {
            font-family: 'Roboto', sans-serif;
            text-transform: capitalize;
        }

        .ast-archive-description .ast-archive-title {
            font-family: 'Roboto', sans-serif;
            text-transform: capitalize;
        }

        .blog .entry-title,
        .blog .entry-title a,
        .archive .entry-title,
        .archive .entry-title a,
        .search .entry-title,
        .search .entry-title a {
            font-family: 'Roboto', sans-serif;
            text-transform: capitalize;
        }

        h1,
        .entry-content h1,
        .entry-content h1 a {
            text-transform: capitalize;
        }

        h2,
        .entry-content h2,
        .entry-content h2 a {
            line-height: 1;
            text-transform: capitalize;
        }

        h3,
        .entry-content h3,
        .entry-content h3 a {
            text-transform: capitalize;
        }

        h4,
        .entry-content h4,
        .entry-content h4 a {
            text-transform: capitalize;
        }

        h5,
        .entry-content h5,
        .entry-content h5 a {
            text-transform: capitalize;
        }

        h6,
        .entry-content h6,
        .entry-content h6 a {
            text-transform: capitalize;
        }

        .ast-search-box.header-cover #close::before,
        .ast-search-box.full-screen #close::before {
            font-family: Astra;
            content: "\e5cd";
            display: inline-block;
            transition: transform .3s ease-in-out;
        }

        #masthead .site-logo-img .astra-logo-svg,
        .ast-header-break-point #ast-fixed-header .site-logo-img .custom-logo-link img {
            max-width: 329px;
        }

        .ast-sticky-main-shrink .ast-sticky-shrunk .main-header-bar {
            padding-top: 0.5em;
            padding-bottom: 0.5em;
        }

        .ast-sticky-main-shrink .ast-sticky-shrunk .main-header-bar .ast-site-identity {
            padding-top: 0;
            padding-bottom: 0;
        }

        .ast-transparent-header.ast-primary-sticky-header-active .main-header-bar-wrap .main-header-bar,
        .ast-primary-sticky-header-active .main-header-bar-wrap .main-header-bar,
        .ast-primary-sticky-header-active.ast-header-break-point .main-header-bar-wrap .main-header-bar,
        .ast-transparent-header.ast-primary-sticky-enabled .ast-main-header-wrap .main-header-bar.ast-header-sticked,
        .ast-primary-sticky-enabled .ast-main-header-wrap .main-header-bar.ast-header-sticked,
        .ast-primary-sticky-header-ast-primary-sticky-enabled .ast-main-header-wrap .main-header-bar.ast-header-sticked {
            background: rgba(255, 255, 255, 1);
        }

        .ast-primary-sticky-header-active .site-title a,
        .ast-primary-sticky-header-active .site-title a:focus,
        .ast-primary-sticky-header-active .site-title a:hover,
        .ast-primary-sticky-header-active .site-title a:visited {
            color: #222;
        }

        .ast-primary-sticky-header-active .site-header .site-description {
            color: #3a3a3a;
        }

        .ast-above-sticky-header-active .ast-above-header-wrap .ast-above-header {
            background: rgba(255, 255, 255, 1);
        }
    </style>
    <!--[if IE]>
<script src='<?php echo $url; ?>/wp-content/themes/astra/assets/js/minified/flexibility.min.js?ver=3.3.2' id='astra-flexibility-js'></script>
<script id='astra-flexibility-js-after'>
flexibility(document.documentElement);
</script>
<![endif]-->
    <script src='wp-includes/js/plupload/moxie.mine34c.js?ver=1.3.5' id='moxiejs-js'></script>
    <script src='wp-includes/js/plupload/plupload.min6c17.js?ver=2.1.9' id='plupload-js'></script>
    <script src='wp-includes/js/jquery/jquery.min9d52.js?ver=3.5.1' id='jquery-core-js'></script>
    <script src='wp-includes/js/jquery/jquery-migrate.mind617.js?ver=3.3.2' id='jquery-migrate-js'></script>
    <link rel="https://api.w.org/" href="wp-json/index.php" />
    <link rel="alternate" type="application/json" href="wp-json/wp/v2/pages/892.json" />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="xmlrpc0db0.php?rsd" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="wp-includes/wlwmanifest.xml" />

    <link rel='shortlink' href='index.php' />
    <link rel="alternate" type="application/json+oembed"
        href="wp-json/oembed/1.0/embed0667.json?url=https%3A%2F%2F<?php echo $url; ?>%2F" />
    <link rel="alternate" type="text/xml+oembed"
        href="wp-json/oembed/1.0/embedad10?url=https%3A%2F%2F<?php echo $url; ?>%2F&amp;format=xml" />
    <style type="text/css" id="tve_global_variables">
        :root {}
    </style>
    <style type="text/css" id="thrive-default-styles"></style>
    <link rel="icon" href="wp-content/uploads/2018/12/cropped-favicon-600x600-32x32.png" sizes="32x32" />
    <link rel="icon" href="wp-content/uploads/2018/12/cropped-favicon-600x600-192x192.png" sizes="192x192" />
    <link rel="apple-touch-icon" href="wp-content/uploads/2018/12/cropped-favicon-600x600-180x180.png" />
    <meta name="msapplication-TileImage"
        content="<?php echo $url; ?>/wp-content/uploads/2018/12/cropped-favicon-600x600-270x270.png" />
    <style type="text/css" class="tve_custom_style">
        @import url("http://fonts.googleapis.com/css?family=Quicksand:400,500,700,300&amp;subset=latin");
        @import url("http://fonts.googleapis.com/css?family=Montserrat:300,700,200,800,400,900,500&amp;subset=latin");

        @media (min-width: 300px) {
            [data-css="tve-u-1683f5effc7"] {
                max-width: 1200px;
                --tve-font-size: 17px;
                min-height: 300px !important;
            }

            [data-css="tve-u-1683f5effc8"] {
                clip-path: url("#clip-bottom-53290d25ea7d2");
                -webkit-clip-path: url("#clip-bottom-53290d25ea7d2");
                filter: grayscale(0%) blur(0px);
                opacity: 1;
                background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_HOME_PAGE_BANNER.png") !important;
                background-size: cover !important;
                background-position: 50% 50% !important;
                background-attachment: scroll !important;
                background-repeat: no-repeat !important;
                --tve-applied-background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_HOME_PAGE_BANNER.png") !important;
                --background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_HOME_PAGE_BANNER.png") !important;
                --background-size: cover !important;
                --background-position: 50% 50% !important;
                --background-attachment: scroll !important;
                --background-repeat: no-repeat !important;
            }

            [data-css="tve-u-1683f5effc9"] {
                --tve-border-width: 0px;
                border: none;
                --tve-applied-border: none;
                margin-top: -50px !important;
                padding: 0px 40px 0px 26px !important;
                margin-right: 0px !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effca"] {
                text-shadow: rgba(0, 0, 0, 0.4) 1px 1px 1px;
                text-transform: none !important;
                color: rgb(255, 255, 255) !important;
                font-size: 30px !important;
                font-family: Montserrat !important;
                font-weight: 300 !important;
            }

            [data-css="tve-u-1683f5effcb"] {
                border: medium none;
                display: block;
                left: 20px;
                top: 15px;
                margin-top: 1px !important;
                padding: 111px 0px 0px !important;
                margin-left: 0px !important;
                position: relative !important;
            }

            [data-css="tve-u-1683f5effca"] {
                line-height: 1em !important;
            }

            [data-css="tve-u-1683f5effcc"] {
                display: block;
                float: none;
                position: relative;
                min-height: 0px;
                left: 20px;
                top: -10px;
                padding-left: 0px !important;
                padding-top: 15px !important;
                z-index: 4 !important;
                margin: 4px auto 2px -10px !important;
                padding-bottom: 3px !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effcd"] {
                text-shadow: rgba(0, 0, 0, 0.4) 1px 1px 1px;
                letter-spacing: 0px;
                color: rgb(255, 255, 255) !important;
                font-size: 17px !important;
                font-family: Quicksand !important;
                font-weight: 400 !important;
            }

            [data-css="tve-u-1683f5effe6"] {
                box-shadow: rgba(0, 0, 0, 0.2) 3px 2px 10px 0px;
            }

            [data-css="tve-u-1683f5effe7"] {
                width: 800px;
                margin-top: 0px !important;
            }

            [data-css="tve-u-1683f5effe8"] {
                margin-top: 0px !important;
                margin-bottom: -35px !important;
            }

            [data-css="tve-u-1683f5effe9"]>.tcb-flex-col {
                padding-left: 30px;
            }

            [data-css="tve-u-1683f5effe9"] {
                margin-left: -30px;
            }

            :not(#tve) [data-css="tve-u-1683f5effec"] {
                text-transform: none !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effed"] {
                text-transform: none !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effee"] {
                text-transform: none !important;
            }

            [data-css="tve-u-1683f5effca"] strong {
                font-weight: 700 !important;
            }

            [data-css="tve-u-1683f7c971e"] {
                font-size: 60px;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 0px !important;
            }

            [data-css="tve-u-1683f7c971f"] {
                display: block;
                max-width: 280px;
                float: none;
                margin: -1px auto 0px !important;
            }

            [data-css="tve-u-1683f7c9720"] {
                font-size: 60px;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 0px !important;
            }

            [data-css="tve-u-1683f7c9721"] {
                font-size: 60px;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: -1px !important;
            }

            [data-css="tve-u-1683f7c9722"] {
                font-size: 60px;
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 0px !important;
            }

            [data-css="tve-u-1683f7c9723"] {
                display: inline-block;
                padding-top: 0px !important;
            }

            [data-css="tve-u-1683f7c9724"] {
                box-shadow: none;
            }

            [data-css="tve-u-1683f7c9725"] {
                padding-top: 0px !important;
            }

            [data-css="tve-u-1683f7c9726"] {
                padding-top: 0px !important;
            }

            [data-css="tve-u-1683f7c9727"] {
                padding-top: 0px !important;
            }

            [data-css="tve-u-168e2d3d1d8"] {
                background-image: none !important;
            }

            [data-css="tve-u-1683f5effcd"] strong {
                font-weight: 500 !important;
            }

            [data-css="tve-u-1683f5effcd"] {
                line-height: 1.05em !important;
            }

            [data-css="tve-u-170207107dd"] {
                width: 800px;
                margin-top: 0px !important;
            }

            [data-css="tve-u-170207107e1"] {
                margin-top: 0px;
                margin-left: 0px;
            }

            [data-css="tve-u-170207107e2"] {
                margin-top: 0px;
                margin-left: 0px;
            }

            [data-css="tve-u-1702071874a"] {
                width: 800px;
                margin-top: 0px !important;
            }

            [data-css="tve-u-1702071874d"] {
                margin-top: 0px;
                margin-left: 0px;
                width: 100% !important;
                max-width: none !important;
            }

            [data-css="tve-u-1702071874e"] {
                margin-top: 0px;
                margin-left: 0px;
                width: 100% !important;
                max-width: none !important;
            }

            [data-css="tve-u-1702072288e"] {
                margin-top: 0px;
                margin-left: 0px;
            }

            [data-css="tve-u-1702072288f"] {
                margin-top: 0px;
                margin-left: 0px;
            }

            [data-css="tve-u-1713d6c10da"] {
                max-width: 1200px;
            }

            [data-css="tve-u-1713d6c10db"] {
                box-shadow: rgba(0, 0, 0, 0.1) 0px 8px 12px 0px;
                background-image: none !important;
                background-color: transparent !important;
            }

            [data-css="tve-u-1713d6c10dc"] {
                border: 1px solid rgb(204, 204, 204);
                padding-left: 15px !important;
                padding-right: 15px !important;
                margin-top: 10px !important;
            }

            [data-css="tve-u-1713d6c10de"] {
                margin-top: 7px !important;
            }

            [data-css="tve-u-1713d6c10df"] {
                margin-top: 7px !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effc7"] p,
            :not(#tve) [data-css="tve-u-1683f5effc7"] li,
            :not(#tve) [data-css="tve-u-1683f5effc7"] blockquote,
            :not(#tve) [data-css="tve-u-1683f5effc7"] address,
            :not(#tve) [data-css="tve-u-1683f5effc7"] .tcb-plain-text,
            :not(#tve) [data-css="tve-u-1683f5effc7"] label {
                font-size: var(--tve-font-size, 17px);
            }

            [data-css="tve-u-172527de100"] {
                font-size: 9px !important;
            }

            [data-css="tve-u-1702071874a"] .tve_image_frame {
                height: 100%;
            }
        }

        @media (max-width: 1023px) {
            [data-css="tve-u-1683f5effc8"] {
                opacity: 1;
                filter: grayscale(0%) blur(0px);
                background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_HOME_PAGE_BANNER.png") !important;
                background-size: cover !important;
                background-position: 50% 50% !important;
                background-attachment: scroll !important;
                background-repeat: no-repeat !important;
                --tve-applied-background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_HOME_PAGE_BANNER.png") !important;
                --background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_HOME_PAGE_BANNER.png") !important;
            }
        }

        @media (max-width: 767px) {
            [data-css="tve-u-1683f5effc7"] {
                max-width: 305px;
                min-height: 36.5vh !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effca"] {
                font-size: 29px !important;
            }

            :not(#tve) [data-css="tve-u-1683f5effcd"] {
                font-size: 16px !important;
            }

            [data-css="tve-u-1683f5effc8"] {
                background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_phone_HOME_PAGE_BANNER.png") !important;
                background-position: 50% 50% !important;
                background-size: cover !important;
                background-attachment: scroll !important;
                background-repeat: no-repeat !important;
                --tve-applied-background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_phone_HOME_PAGE_BANNER.png") !important;
                --background-image: url("wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_phone_HOME_PAGE_BANNER.png") !important;
            }

            [data-css="tve-u-1683f5effcb"] {
                padding-bottom: 0px !important;
                padding-top: 37px !important;
            }

            [data-css="tve-u-1683f5effc9"] {
                margin-top: 20px !important;
            }

            [data-css="tve-u-1683f5effcc"] {
                margin-bottom: 3px !important;
                padding-bottom: 0px !important;
            }
        }
    </style>
    <style id="wp-custom-css">
        /*
You can add your own CSS here.

Click the help icon above to learn more.
*/




        /*
Blue color added for all H tags with links
*/
        .thrv_heading h1 a,
        .thrv_heading h2 a,
        .thrv_heading h3 a,
        .thrv_heading h4 a,
        .thrv_heading h5 a,
        .thrv_heading h6 a {
            color: #00548e;
        }

        .thrv_heading h1 a:hover,
        .thrv_heading h2 a:hover,
        .thrv_heading h3 a:hover,
        .thrv_heading h4 a:hover,
        .thrv_heading h5 a:hover,
        .thrv_heading h6 a:hover {
            color: #0099cc;
        }

        /*
Red button Access Your Account 
*/
        .menu-item-144 a {
            color: #cc0033 !important;
        }

        .menu-item-408 a,
        .menu-item-409 a,
        .menu-item-410 a,
        .menu-item-411 a,
        .menu-item-412 a,
        .menu-item-413 a,
        .menu-item-414 a,
        .menu-item-415 a,
        .menu-item-416 a,
        .menu-item-2296 a,
        .menu-item-4121 a {
            color: #333333 !important;
        }

        .menu-item-408 a:hover,
        .menu-item-409 a:hover,
        .menu-item-410 a:hover,
        .menu-item-411 a:hover,
        .menu-item-412 a:hover,
        .menu-item-413 a:hover,
        .menu-item-414 a:hover,
        .menu-item-415 a:hover,
        .menu-item-416 a:hover,
        .menu-item-2296 a:hover,
        .menu-item-4121 a:hover {
            color: #007cba !important;
        }

        /*
FAQs gray box background color
*/

        .tve_faq {
            background-color: #ffffff;
        }

        .tve_faq.tve_oFaq {
            background: #ffffff;
        }

        /*
bottom padding after press release post date feed
*/

        .wp-block-latest-posts__post-date {
            margin-bottom: 10px;
        }


        /*
Tab Mouseover Font Color
*/

        .thrv_wrapper.thrv-tabbed-content div.tve_scT>ul li span:hover {
            color: #3a3a3a !important;
        }

        /*
Makes bullets line up correctly
*/
        #tve_editor ul {
            list-style-position: outside !important;
            margin-left: 40px;
        }
    </style>
</head>

<body itemtype='https://schema.org/WebPage' itemscope='itemscope'
    class="home page-template-default page page-id-892 wp-custom-logo ast-desktop ast-plain-container ast-no-sidebar astra-3.3.2 ast-header-custom-item-inside group-blog ast-single-post ast-mobile-inherit-site-logo ast-inherit-site-logo-transparent above-header-nav-padding-support ast-sticky-main-shrink ast-sticky-header-shrink ast-inherit-site-logo-sticky ast-primary-sticky-enabled astra-addon-3.3.1">
    <div class="hfeed site" id="page">
        <a class="skip-link screen-reader-text" href="#content">Skip to content</a>
        <header
            class="site-header ast-primary-submenu-animation-fade header-main-layout-1 ast-primary-menu-enabled ast-menu-toggle-icon ast-mobile-header-inline ast-above-header-enabled ast-above-header-section-separated ast-above-header-mobile-inline ast-below-header-mobile-inline"
            id="masthead" itemtype="https://schema.org/WPHeader" itemscope="itemscope" itemid="#masthead">

            <div class="ast-above-header-wrap ast-above-header-1">
                <div class="ast-above-header">
                    <div class="ast-container">
                        <div class="ast-flex ast-above-header-section-wrap">

                            <div
                                class="ast-above-header-section ast-above-header-section-2 ast-flex ast-justify-content-flex-end widget-above-header">
                                <div class="above-header-widget above-header-user-select">
                                    <div id="custom_html-9" class="widget_text widget widget_custom_html">
                                        <div class="textwidget custom-html-widget"><span style="font-size:.8em">
                                                <a href="<?php echo $login; ?>">Login</a>  |  <a
                                                    href="<?php echo $register; ?>">Register</a>  |  <a
                                                    href="about/contact/index.php">Contact Us</a></span></div>
                                    </div>
                                    <div id="search-4" class="widget widget_search">
                                        <div id="google_translate_element"></div>
                                        <script type="text/javascript">
                                            function googleTranslateElementInit() {
                                                new google.translate.TranslateElement({
                                                    pageLanguage: 'en',
                                                    includedLanguages: 'ar,en,es,jv,ko,pa,pt,ru,zh-CN,zh-TW,ja',
                                                    layout: google.translate.TranslateElement.InlineLayout
                                                        .SIMPLE
                                                }, 'google_translate_element');
                                            }
                                        </script>
                                        <script type="text/javascript"
                                            src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
                                        </script>


                                    </div>
                                </div> <!-- .above-header-widget -->
                            </div>
                        </div>
                    </div><!-- .ast-container -->
                </div><!-- .ast-above-header -->
            </div><!-- .ast-above-header-wrap -->

            <div class="main-header-bar-wrap">
                <div class="main-header-bar">
                    <div class="ast-container">

                        <div class="ast-flex main-header-container">

                            <div class="site-branding">
                                <div class="ast-site-identity" itemtype="https://schema.org/Organization"
                                    itemscope="itemscope">
                                    <span class="site-logo-img"><a href="index.php" class="custom-logo-link"
                                            rel="home" aria-current="page"><img width="329" height="56"
                                                src="<?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?>"
                                                class="custom-logo" alt="<?php echo $name; ?> Logo"
                                                srcset="<?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 329w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 300w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 1024w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 768w, <?php echo $url; ?>/admin/assets/images/logo/<?php echo $image; ?> 1047w"
                                                sizes="(max-width: 329px) 100vw, 329px" /></a></span>
                                </div>
                            </div>

                            <!-- .site-branding -->
                            <div class="ast-mobile-menu-buttons">


                                <div class="ast-button-wrap">
                                    <button type="button"
                                        class="menu-toggle main-header-menu-toggle  ast-mobile-menu-buttons-fill "
                                        aria-controls='primary-menu' aria-expanded='false'>
                                        <span class="screen-reader-text">Main Menu</span>
                                        <span class="ast-icon icon-menu-bars"><span
                                                class="menu-toggle-icon"></span></span> </button>
                                </div>


                            </div>
                            <div class="ast-main-header-bar-alignment">
                                <div class="main-header-bar-navigation">
                                    <nav class="ast-flex-grow-1 navigation-accessibility" id="site-navigation"
                                        aria-label="Site Navigation"
                                        itemtype="https://schema.org/SiteNavigationElement" itemscope="itemscope">
                                        <div class="main-navigation">
                                            <ul id="primary-menu"
                                                class="main-header-menu ast-nav-menu ast-flex ast-justify-content-flex-end  submenu-with-border astra-menu-animation-fade ">
                                                <li id="menu-item-1065"
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-1065">
                                                    <a href="business/index.php"
                                                        class="menu-link">Business</a><button
                                                        class="ast-menu-toggle" aria-expanded="false"><span
                                                            class="screen-reader-text">Menu Toggle</span><span
                                                            class="ast-icon icon-arrow"></span></button>
                                                    <ul class="sub-menu">
                                                        <li id="menu-item-440"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-440">
                                                            <a href="business/checking/index.php"
                                                                class="menu-link">Business Checking</a>
                                                        </li>
                                                        <li id="menu-item-448"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-448">
                                                            <a href="business/savings/index.php"
                                                                class="menu-link">Business Savings</a>
                                                        </li>
                                                        <li id="menu-item-435"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-435">
                                                            <a href="business/online-banking/index.php"
                                                                class="menu-link">Business Online Banking</a>
                                                        </li>
                                                        <li id="menu-item-446"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-446">
                                                            <a href="business/loans-and-credit/index.php"
                                                                class="menu-link">Business Loans and Credit</a>
                                                        </li>

                                                        <li id="menu-item-445"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-445">
                                                            <a href="business/services/index.php"
                                                                class="menu-link">Business Services</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li id="menu-item-398"
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-398">
                                                    <a href="personal/index.php"
                                                        class="menu-link">Personal</a><button
                                                        class="ast-menu-toggle" aria-expanded="false"><span
                                                            class="screen-reader-text">Menu Toggle</span><span
                                                            class="ast-icon icon-arrow"></span></button>
                                                    <ul class="sub-menu">
                                                        <li id="menu-item-484"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-484">
                                                            <a href="personal/checking/index.php"
                                                                class="menu-link">Personal Checking</a>
                                                        </li>
                                                        <li id="menu-item-485"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-485">
                                                            <a href="personal/savings/index.php"
                                                                class="menu-link">Personal Savings</a>
                                                        </li>
                                                        <li id="menu-item-483"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-483">
                                                            <a href="personal/online-banking/index.php"
                                                                class="menu-link">Personal Online Banking</a>
                                                        </li>
                                                        <li id="menu-item-487"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-487">
                                                            <a href="personal/loans-and-credit/index.php"
                                                                class="menu-link">Personal Loans and Credit</a>
                                                        </li>

                                                        <li id="menu-item-488"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-488">
                                                            <a href="personal/services/index.php"
                                                                class="menu-link">Personal Services</a>
                                                        </li>
                                                        <li id="menu-item-4813"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-4813">
                                                            <a href="investing/index.php"
                                                                class="menu-link">Investing</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li id="menu-item-401"
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-401">
                                                    <a href="about/index.php" class="menu-link">About Us</a><button
                                                        class="ast-menu-toggle" aria-expanded="false"><span
                                                            class="screen-reader-text">Menu Toggle</span><span
                                                            class="ast-icon icon-arrow"></span></button>
                                                    <ul class="sub-menu">
                                                        <li id="menu-item-395"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-395">
                                                            <a href="about/shareholders/index.php"
                                                                class="menu-link">Shareholders</a>
                                                        </li>
                                                        <li id="menu-item-2711"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2711">
                                                            <a href="about/corporate-contributions/index.php"
                                                                class="menu-link">Corporate Contributions</a>
                                                        </li>
                                                        <li id="menu-item-2713"
                                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2713">
                                                            <a href="about/community/index.php"
                                                                class="menu-link">Community Programs</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li id="menu-item-144"
                                                    class="menu-item menu-item-type-custom menu-item-object-custom  menu-item-144">
                                                    <a href="<?php echo $login; ?>" class="menu-link">Access Your
                                                        Account</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                        </div><!-- Main Header Container -->
                    </div><!-- ast-row -->
                </div> <!-- Main Header Bar -->
            </div> <!-- Main Header Bar Wrap -->
        </header><!-- #masthead -->
        <div id="content" class="site-content">
            <div class="ast-container">


                <div id="primary" class="content-area primary">


                    <main id="main" class="site-main">
                        <article class="post-892 page type-page status-publish ast-article-single" id="post-892"
                            itemtype="https://schema.org/CreativeWork" itemscope="itemscope">
                            <header class="entry-header ast-header-without-markup">

                            </header><!-- .entry-header -->

                            <div class="entry-content clear" itemprop="text">


                                <div id="tve_flt" class="tve_flt tcb-style-wrap">
                                    <div id="tve_editor" class="tve_shortcode_editor tar-main-content"
                                        data-post-id="892">
                                        <div class="thrv_wrapper thrv-page-section"
                                            style="--tve-border-width:0px; border: none;" data-value-type="percent"
                                            data-css="tve-u-1683f5effc9">
                                            <div class="tve-page-section-out" data-css="tve-u-1683f5effc8" style="">
                                            </div>
                                            <div class="tve-page-section-in tve_empty_dropzone"
                                                data-css="tve-u-1683f5effc7" style="">
                                                <div class="thrv_wrapper thrv_text_element" data-tag="h1"
                                                    data-css="tve-u-1683f5effcb" style="">
                                                    <h1 class="" data-css="tve-u-1683f5effca"
                                                        style="text-align: center;"><strong><span
                                                                data-css="tve-u-168f2b24710"><strong
                                                                    class=""><?php echo $sliderBOLD; ?></strong></span></strong><span
                                                            data-css="tve-u-168f36bacbc"><sup
                                                                class=""></sup></span><span
                                                            data-css="tve-u-168f28215c2"><sup class=""><sup
                                                                    class=""><span data-css="tve-u-172527de100"
                                                                        style="font-size: 9px;">TM</span></sup></sup></span>
                                                    </h1>
                                                </div>
                                                <div class="thrv_wrapper thrv-plain-text"
                                                    data-css="tve-u-1683f5effcc" style="">
                                                    <div class="tcb-plain-text" data-css="tve-u-1683f5effcd"
                                                        style="text-align: center;">
                                                        <strong><?php echo $slidertext; ?> </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="thrv_wrapper thrv-page-section" style=""
                                            data-css="tve-u-1713d6c10dc">
                                            <div class="tve-page-section-out" data-css="tve-u-1713d6c10db"></div>
                                            <div class="tve-page-section-in tve_empty_dropzone"
                                                data-css="tve-u-1713d6c10da">
                                                <div class="thrv_wrapper thrv-columns">
                                                    <div class="tcb-flex-row v-2 tcb--cols--2">
                                                        <div class="tcb-flex-col">
                                                            <div class="tcb-col">
                                                                <div class="thrv_wrapper thrv_text_element tve-froala fr-box fr-basic"
                                                                    style="" data-css="tve-u-1713d6c10df">
                                                                    <div class="fr-wrapper" dir="auto">
                                                                        <div class="fr-element fr-view" dir="auto"
                                                                            spellcheck="true">
                                                                            <div class="tcb-plain-text"
                                                                                style="text-align: center;"><a
                                                                                    class="tve-froala fr-basic"
                                                                                    href="important-information-about-covid-19/index.php"
                                                                                    style="outline: none;">Important
                                                                                    Information About Covid-19</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tcb-flex-col">
                                                            <div class="tcb-col">
                                                                <div class="thrv_wrapper thrv_text_element tve-froala fr-box fr-basic"
                                                                    style="" data-css="tve-u-1713d6c10de">
                                                                    <div class="tcb-plain-text"
                                                                        style="text-align: center;"><a
                                                                            class="tve-froala fr-basic"
                                                                            href="about/corporate-contributions/index.php"
                                                                            style="outline: none;">Corporate
                                                                            Contributions</a></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="thrv_wrapper thrv-columns" data-css="tve-u-167f6acc9c4">
                                            <div class="tcb-flex-row tcb--cols--4">
                                                <div class="tcb-flex-col">
                                                    <div class="tcb-col">
                                                        <div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box"
                                                            data-css="tve-u-1683f7c971f">
                                                            <div class="tve-content-box-background"
                                                                data-css="tve-u-1683f7c9724"></div>
                                                            <div class="tve-cb"><a href="business/index.php" rel="">
                                                                    <div class="thrv_wrapper thrv_icon tcb-icon-display"
                                                                        data-css="tve-u-1683f7c971e"
                                                                        data-link-wrap="1"><svg class="tcb-icon"
                                                                            viewBox="0 0 640 512"
                                                                            data-id="icon-user-cog-light"
                                                                            data-name="">
                                                                            <path
                                                                                d="M628.3 358.3l-16.5-9.5c.8-8.5.8-17.1 0-25.6l16.6-9.5c9.5-5.5 13.8-16.7 10.5-27-7.2-23.4-19.9-45.4-36.7-63.5-7.4-8.1-19.3-9.9-28.7-4.4l-16.5 9.5c-7-5-14.4-9.3-22.2-12.8v-19c0-11-7.5-20.3-18.2-22.7-23.9-5.4-49.3-5.4-73.2 0-10.7 2.4-18.2 11.8-18.2 22.7v19c-7.8 3.5-15.2 7.8-22.2 12.8l-16.5-9.5c-9.5-5.5-21.3-3.7-28.7 4.4-16.7 18.1-29.4 40.1-36.7 63.4-3.3 10.4 1.2 21.8 10.6 27.2l16.5 9.5c-.8 8.5-.8 17.1 0 25.6l-16.6 9.5c-9.3 5.4-13.8 16.9-10.5 27.1 7.2 23.4 19.9 45.4 36.7 63.5 7.4 8 19.2 9.8 28.7 4.4l16.5-9.5c7 5 14.4 9.3 22.2 12.8v19c0 11 7.5 20.3 18.2 22.7 12 2.7 24.3 4 36.6 4s24.7-1.3 36.6-4c10.7-2.4 18.2-11.8 18.2-22.7v-19c7.8-3.5 15.2-7.8 22.2-12.8l16.5 9.5c9.4 5.4 21.3 3.6 28.7-4.4 16.7-18.1 29.4-40.1 36.7-63.4 3.3-10.4-1.2-21.9-10.6-27.3zm-51.6 7.2l29.4 17c-5.2 14.3-13 27.8-22.8 39.5l-29.4-17c-21.4 18.3-24.5 20.1-51.1 29.5v34c-15.1 2.6-30.6 2.6-45.6 0v-34c-26.9-9.5-30.2-11.7-51.1-29.5l-29.4 17c-9.8-11.8-17.6-25.2-22.8-39.5l29.4-17c-4.9-26.8-5.2-30.6 0-59l-29.4-17c5.2-14.3 13-27.7 22.8-39.5l29.4 17c21.4-18.3 24.5-20.1 51.1-29.5v-34c15.1-2.5 30.7-2.5 45.6 0v34c26.8 9.5 30.2 11.6 51.1 29.5l29.4-17c9.8 11.8 17.6 25.2 22.8 39.5l-29.4 17c4.9 26.8 5.2 30.6 0 59zm-96.7-94c-35.6 0-64.5 29-64.5 64.5s28.9 64.5 64.5 64.5 64.5-29 64.5-64.5-28.9-64.5-64.5-64.5zm0 97c-17.9 0-32.5-14.6-32.5-32.5s14.6-32.5 32.5-32.5 32.5 14.6 32.5 32.5-14.6 32.5-32.5 32.5zM224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm0-224c52.9 0 96 43.1 96 96s-43.1 96-96 96-96-43.1-96-96 43.1-96 96-96zM48 480c-8.8 0-16-7.2-16-16v-41.6C32 365.9 77.9 320 134.4 320c19.6 0 39.1 16 89.6 16 19.2 0 38-3.3 56.5-8.7.5-11.6 1.8-23 4.2-34-8.9 2.7-30.1 10.7-60.7 10.7-47.1 0-60.8-16-89.6-16C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h337c-16-8.6-30.6-19.5-43.5-32H48z">
                                                                            </path>
                                                                        </svg></div>
                                                                </a></div>
                                                        </div>
                                                        <div class="thrv_wrapper thrv-plain-text"
                                                            data-css="tve-u-1683f7c9723">
                                                            <div class="tcb-plain-text" style="text-align: center;">
                                                                <a href="business/index.php">Business</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tcb-flex-col">
                                                    <div class="tcb-col"><a href="personal/index.php" rel="">
                                                            <div class="thrv_wrapper thrv_icon tcb-icon-display"
                                                                data-css="tve-u-1683f7c9720" data-link-wrap="1"><svg
                                                                    class="tcb-icon" viewBox="0 0 640 512"
                                                                    data-id="icon-user-edit-light" data-name="">
                                                                    <path
                                                                        d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm0-224c52.9 0 96 43.1 96 96s-43.1 96-96 96-96-43.1-96-96 43.1-96 96-96zm406.6 204.1l-34.7-34.7c-6.3-6.3-14.5-9.4-22.8-9.4-8.2 0-16.5 3.1-22.8 9.4L327.8 424l-7.6 68.2c-1.2 10.7 7.2 19.8 17.7 19.8.7 0 1.3 0 2-.1l68.2-7.6 222.5-222.5c12.5-12.7 12.5-33.1 0-45.7zM393.3 473.7l-39.4 4.5 4.4-39.5 156.9-156.9 35 35-156.9 156.9zm179.5-179.5l-35-35L573 224h.1l.2.1 34.7 35-35.2 35.1zM134.4 320c19.6 0 39.1 16 89.6 16 50.4 0 70-16 89.6-16 20.7 0 39.9 6.3 56 16.9l22.8-22.8c-22.2-16.2-49.3-26-78.8-26-28.7 0-42.5 16-89.6 16-47.1 0-60.8-16-89.6-16C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h243.5c-2.8-7.4-4.1-15.4-3.2-23.4l1-8.6H48c-8.8 0-16-7.2-16-16v-41.6C32 365.9 77.9 320 134.4 320z">
                                                                    </path>
                                                                </svg></div>
                                                        </a>
                                                        <div class="thrv_wrapper thrv-plain-text"
                                                            data-css="tve-u-1683f7c9725">
                                                            <div class="tcb-plain-text" style="text-align: center;">
                                                                <a href="personal/index.php">Personal</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tcb-flex-col">
                                                    <div class="tcb-col"><a href="about/locations/index.php" rel="">
                                                            <div class="thrv_wrapper thrv_icon tcb-icon-display"
                                                                data-css="tve-u-1683f7c9721" data-link-wrap="1"><svg
                                                                    class="tcb-icon" viewBox="0 0 640 512"
                                                                    data-id="icon-door-open-light" data-name="">
                                                                    <path
                                                                        d="M288 288c13.25 0 24-14.33 24-32s-10.75-32-24-32-24 14.33-24 32 10.75 32 24 32zm344 192H512V96c0-17.67-14.33-32-32-32h-96V33.18C384 14.42 369.2 0 352.06 0c-2.57 0-5.19.32-7.83 1.01l-192 49.74C137.99 54.44 128 67.7 128 82.92V480H8c-4.42 0-8 3.58-8 8v16c0 4.42 3.58 8 8 8h624c4.42 0 8-3.58 8-8v-16c0-4.42-3.58-8-8-8zM352 33.18V480H160l.26-398.27 191.8-49.69-.06 1.14zM480 480h-96V96h96v384z">
                                                                    </path>
                                                                </svg></div>
                                                        </a>
                                                        <div class="thrv_wrapper thrv-plain-text"
                                                            data-css="tve-u-1683f7c9726">
                                                            <div class="tcb-plain-text" style="text-align: center;">
                                                                <a href="about/locations/index.php">Locations</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tcb-flex-col">
                                                    <div class="tcb-col"><a href="personal/services/index.php"
                                                            rel="">
                                                            <div class="thrv_wrapper thrv_icon tcb-icon-display"
                                                                data-css="tve-u-1683f7c9722" data-link-wrap="1"><svg
                                                                    class="tcb-icon" viewBox="0 0 576 512"
                                                                    data-id="icon-folder-open-light" data-name="">
                                                                    <path
                                                                        d="M527.95 224H480v-48c0-26.51-21.49-48-48-48H272l-64-64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h385.057c28.068 0 54.135-14.733 68.599-38.84l67.453-112.464C588.24 264.812 565.285 224 527.95 224zM48 96h146.745l64 64H432c8.837 0 16 7.163 16 16v48H171.177c-28.068 0-54.135 14.733-68.599 38.84L32 380.47V112c0-8.837 7.163-16 16-16zm493.695 184.232l-67.479 112.464A47.997 47.997 0 0 1 433.057 416H44.823l82.017-136.696A48 48 0 0 1 168 256h359.975c12.437 0 20.119 13.568 13.72 24.232z">
                                                                    </path>
                                                                </svg></div>
                                                        </a>
                                                        <div class="thrv_wrapper thrv-plain-text"
                                                            data-css="tve-u-1683f7c9727">
                                                            <div class="tcb-plain-text" style="text-align: center;">
                                                                <a href="personal/services/index.php">Services</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="thrv_wrapper thrv-columns" data-css="tve-u-1683f5effe8">
                                            <div class="tcb-flex-row tcb--cols--3" data-css="tve-u-1683f5effe9">
                                                <div class="tcb-flex-col" data-css="tve-u-17619e05a99" style="">
                                                    <div class="tcb-col">
                                                        <div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box"
                                                            data-link-wrap="1">
                                                            <div class="tve-content-box-background"
                                                                data-css="tve-u-1683f5effe6"></div>
                                                            <div class="tve-cb">
                                                                <div class="thrv_wrapper tve_image_caption"
                                                                    data-css="tve-u-170207107dd"><span
                                                                        class="tve_image_frame"
                                                                        style="width: 100%;"><a
                                                                            href="personal/loans-and-credit/index.php"
                                                                            rel=""><img
                                                                                class="tve_image jetpack-lazy-image jetpack-lazy-image--handled wp-image-5297"
                                                                                alt="Credit Cards"
                                                                                title="WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT"
                                                                                data-id="5297"
                                                                                src="wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT.png"
                                                                                style="" scale="0"
                                                                                data-lazy-loaded="1"
                                                                                data-link-wrap="1"
                                                                                data-css="tve-u-170207107e1"
                                                                                data-width="378" data-height="165"
                                                                                data-init-width="800"
                                                                                data-init-height="350"
                                                                                loading="lazy" width="378"
                                                                                height="165"
                                                                                srcset="<?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT.png 800w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT-300x131.png 300w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT-768x336.png 768w"
                                                                                sizes="(max-width: 378px) 100vw, 378px" /><code
                                                                                class="tve_js_placeholder tve_noscript"><noscript><img class="tve_image jetpack-lazy-image wp-image-5297" alt="Credit Cards" title="WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT" data-id="5297" src="wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT.png" style="" scale="0" data-lazy-src="/wp-content/uploads/2018/12/background-mobile-deposit.jpg?is-pending-load=1" data-css="tve-u-170207107e2" data-width="378" data-height="165" data-init-width="800" data-init-height="350" loading="lazy" width="378" height="165" srcset="<?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT.png 800w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT-300x131.png 300w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_CREDIT-768x336.png 768w" sizes="(max-width: 378px) 100vw, 378px" /><noscript><img loading="lazy" class="tve_image wp-image-288" alt="Mobile Deposit" width="2000" height="465" title="Mobile Deposit" data-id="288" src="wp-content/uploads/2018/12/background-mobile-deposit.php" style="width: 100%;" scale="0"></noscript></noscript></code></a></span>
                                                                </div>
                                                                <div class="thrv_wrapper thrv_text_element tve-froala fr-box fr-basic"
                                                                    data-tag="h2" data-css="tve-u-168e2d3d1d8">
                                                                    <h2 class="" data-css="tve-u-1683f5effec"
                                                                        style="text-align: center;"><a
                                                                            class="tve-froala fr-basic"
                                                                            href="personal/loans-and-credit/index.php"
                                                                            style="outline: none;">Personal Loans
                                                                            and Credit</a></h2>
                                                                </div>
                                                                <div class="thrv_wrapper thrv_text_element">
                                                                    <p style="text-align: center;">Benefits to Meet
                                                                        Your Needs</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tcb-flex-col">
                                                    <div class="tcb-col">
                                                        <div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box"
                                                            data-link-wrap="1">
                                                            <div class="tve-content-box-background"
                                                                data-css="tve-u-1683f5effe6"></div>
                                                            <div class="tve-cb">
                                                                <div class="thrv_wrapper tve_image_caption"
                                                                    data-css="tve-u-1702071874a"><span
                                                                        class="tve_image_frame"
                                                                        style="width: 100%;"><a
                                                                            href="personal/online-banking/index.php"
                                                                            rel=""><img
                                                                                class="tve_image jetpack-lazy-image jetpack-lazy-image--handled wp-image-5298"
                                                                                alt="Mobile Banking"
                                                                                title="WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE BANKING"
                                                                                data-id="5298"
                                                                                src="wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING.png"
                                                                                style="" scale="0"
                                                                                data-lazy-loaded="1"
                                                                                data-link-wrap="1"
                                                                                data-css="tve-u-1702071874d"
                                                                                data-width="378" data-height="165"
                                                                                data-init-width="800"
                                                                                data-init-height="350"
                                                                                loading="lazy" mt-d="0" ml-d="0"
                                                                                width="378" height="165"
                                                                                srcset="<?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING.png 800w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING-300x131.png 300w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING-768x336.png 768w"
                                                                                sizes="(max-width: 378px) 100vw, 378px" /><code
                                                                                class="tve_js_placeholder tve_noscript"><noscript><img class="tve_image jetpack-lazy-image wp-image-5298" alt="Mobile Banking" title="WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE BANKING" data-id="5298" src="wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING.png" style="" scale="0" data-lazy-src="/wp-content/uploads/2019/01/business-phone.jpg?is-pending-load=1" data-css="tve-u-1702071874e" data-width="378" data-height="165" data-init-width="800" data-init-height="350" loading="lazy" mt-d="0" ml-d="0" width="378" height="165" srcset="<?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING.png 800w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING-300x131.png 300w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_MOBILE-BANKING-768x336.png 768w" sizes="(max-width: 378px) 100vw, 378px" /><noscript><img loading="lazy" class="tve_image wp-image-544" alt="" width="2000" height="465" title="Business Phone" data-id="544" src="wp-content/uploads/2019/01/business-phone.php" style="width: 100%;" scale="0"></noscript></noscript></code></a></span>
                                                                </div>
                                                                <div class="thrv_wrapper thrv_text_element tve-froala fr-box"
                                                                    data-tag="h2">
                                                                    <h2 class="" data-css="tve-u-1683f5effed"
                                                                        style="text-align: center;"><a
                                                                            class="tve-froala fr-basic"
                                                                            href="personal/online-banking/mobile-banking/index.php"
                                                                            style="outline: none;">Mobile
                                                                            Banking</a></h2>
                                                                </div>
                                                                <div class="thrv_wrapper thrv_text_element">
                                                                    <p style="text-align: center;">Account
                                                                        Management At Your Fingertips</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tcb-flex-col" data-css="tve-u-176ba314138" style="">
                                                    <div class="tcb-col">
                                                        <div class="thrv_wrapper thrv_contentbox_shortcode thrv-content-box"
                                                            data-link-wrap="1">
                                                            <div class="tve-content-box-background"
                                                                data-css="tve-u-1683f5effe6"></div>
                                                            <div class="tve-cb">
                                                                <div class="thrv_wrapper tve_image_caption"
                                                                    data-css="tve-u-1683f5effe7"><span
                                                                        class="tve_image_frame"
                                                                        style="width: 100%;"><a
                                                                            href="business/services/index.php"><img
                                                                                class="tve_image jetpack-lazy-image jetpack-lazy-image--handled wp-image-5296"
                                                                                alt="Business Services"
                                                                                title="WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES"
                                                                                data-id="5296"
                                                                                src="wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES.png"
                                                                                style="" scale="0"
                                                                                data-lazy-loaded="1"
                                                                                data-css="tve-u-1702072288e"
                                                                                data-width="378" data-height="165"
                                                                                data-init-width="800"
                                                                                data-init-height="350"
                                                                                loading="lazy" width="378"
                                                                                height="165"
                                                                                srcset="<?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES.png 800w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES-300x131.png 300w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES-768x336.png 768w"
                                                                                sizes="(max-width: 378px) 100vw, 378px" /></a><code
                                                                            class="tve_js_placeholder tve_noscript"><noscript><img class="tve_image jetpack-lazy-image wp-image-5296" alt="Business Services" title="WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES" data-id="5296" src="wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES.png" style="" scale="0" data-lazy-src="/wp-content/uploads/2018/12/background-visa-pay-down.jpg?is-pending-load=1" data-css="tve-u-1702072288f" data-width="378" data-height="165" data-init-width="800" data-init-height="350" loading="lazy" width="378" height="165" srcset="<?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES.png 800w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES-300x131.png 300w, <?php echo $url; ?>/wp-content/uploads/2021/01/WAB_SITE_MERCHANDISING_BOTTOM_IMAGES_BUSINESS-SERVICES-768x336.png 768w" sizes="(max-width: 378px) 100vw, 378px" /><noscript><img loading="lazy" class="tve_image wp-image-367" alt="" width="2000" height="465" title="VISA" data-id="367" src="wp-content/uploads/2018/12/background-visa-pay-down.php" style="width: 100%;" scale="0"></noscript></noscript></code></span>
                                                                </div>
                                                                <div class="thrv_wrapper thrv_text_element tve-froala fr-box"
                                                                    data-tag="h2">
                                                                    <h2 class="" data-css="tve-u-1683f5effee"
                                                                        style="text-align: center;"><a
                                                                            class="tve-froala"
                                                                            href="business/services/index.php"
                                                                            style="outline: none;">Business
                                                                            Services</a></h2>
                                                                </div>
                                                                <div class="thrv_wrapper thrv_text_element">
                                                                    <p style="text-align: center;">Helping
                                                                        Successful Businesses Thrive</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tcb_flag" style="display: none"></div>



                            </div><!-- .entry-content .clear -->



                        </article><!-- #post-## -->

                    </main><!-- #main -->


                </div><!-- #primary -->


            </div> <!-- ast-container -->
        </div><!-- #content -->

        <footer class="site-footer" id="colophon" itemtype="https://schema.org/WPFooter" itemscope="itemscope"
            itemid="#colophon">



            <div class="footer-adv footer-adv-layout-4">
                <div class="footer-adv-overlay">
                    <div class="ast-container">
                        <div class="ast-row">
                            <div
                                class="ast-col-lg-3 ast-col-md-3 ast-col-sm-12 ast-col-xs-12 footer-adv-widget footer-adv-widget-1">
                                <div id="custom_html-2" class="widget_text widget widget_custom_html">
                                    <h2 class="widget-title">Business</h2>
                                    <div class="textwidget custom-html-widget"><a
                                            href="business/online-banking/index.php">Business Online
                                            Banking</a><br />
                                        <a href="business/checking/index.php">Business Checking</a><br />
                                        <a href="business/savings/index.php">Business Savings</a><br />

                                        <a href="business/loans-and-credit/index.php">Business Loans and
                                            Credit</a><br />
                                        <a href="business/services/index.php">Business Services</a>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="ast-col-lg-3 ast-col-md-3 ast-col-sm-12 ast-col-xs-12 footer-adv-widget footer-adv-widget-2">
                                <div id="custom_html-3" class="widget_text widget widget_custom_html">
                                    <h2 class="widget-title">Personal</h2>
                                    <div class="textwidget custom-html-widget"><a
                                            href="personal/online-banking/index.php">Personal Online
                                            Banking</a><br />
                                        <a href="personal/checking/index.php">Personal Checking</a><br />
                                        <a href="personal/savings/index.php">Personal Savings</a><br />

                                        <a href="personal/loans-and-credit/index.php">Personal Loans and
                                            Credit</a><br />
                                        <a href="personal/services/index.php">Personal Services</a>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="ast-col-lg-3 ast-col-md-3 ast-col-sm-12 ast-col-xs-12 footer-adv-widget footer-adv-widget-3">
                                <div id="custom_html-4" class="widget_text widget widget_custom_html">
                                    <h2 class="widget-title">Info</h2>
                                    <div class="textwidget custom-html-widget"><a
                                            href="about/privacy/index.php">Privacy Policies</a><br />
                                        <a href="about/patriot-act-policy/index.php">Patriot Act Policy</a><br />
                                        <a href="about/accessibility/index.php">Web Accessibility</a>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="ast-col-lg-3 ast-col-md-3 ast-col-sm-12 ast-col-xs-12 footer-adv-widget footer-adv-widget-4">
                                <div id="custom_html-5" class="widget_text widget widget_custom_html">
                                    <h2 class="widget-title"><?php echo $name; ?></h2>
                                    <div class="textwidget custom-html-widget"><strong>Corporate
                                            Headquarters</strong><br />
                                        <?php echo $addr; ?><br /><br />

                                        <a href="https://www.facebook.com/" target="_blank" rel="noopener"><img
                                                src="wp-content/uploads/2019/01/social-fb-20x20.png"
                                                alt="Facebook Icon" /></a> &nbsp;<a href="https://www.linkedin.com/"
                                            target="_blank" rel="noopener"><img
                                                src="wp-content/uploads/2019/01/social-li-20x20.png"
                                                alt="LinkedIn Icon" /></a>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .ast-row -->
                    </div><!-- .ast-container -->
                </div><!-- .footer-adv-overlay-->
            </div><!-- .ast-theme-footer .footer-adv-layout-4 -->

            <div class="ast-small-footer footer-sml-layout-2">
                <div class="ast-footer-overlay">
                    <div class="ast-container">
                        <div class="ast-small-footer-wrap">
                            <div class="ast-row ast-flex">

                                <div
                                    class="ast-small-footer-section ast-small-footer-section-1 ast-small-footer-section-equally ast-col-md-6">
                                    <span style="font-size:11px"><img
                                            src="wp-content/uploads/2019/01/equal_housing_logo.png"
                                            alt="Equal Housing Lender Logo"><?php echo $footertext; ?> </span>
                                </div>

                                <div
                                    class="ast-small-footer-section ast-small-footer-section-2 ast-small-footer-section-equally ast-col-md-6">
                                    <span style="font-size:11px">Copyright © <?php echo date("Y"); ?>
                                        <?php echo $name; ?>. All Rights Reserved.</span>
                                </div>

                            </div> <!-- .ast-row.ast-flex -->
                        </div><!-- .ast-small-footer-wrap -->
                    </div><!-- .ast-container -->
                </div><!-- .ast-footer-overlay -->
            </div><!-- .ast-small-footer-->


        </footer><!-- #colophon -->
    </div><!-- #page -->
    <a id="ast-scroll-top" class="ast-scroll-top-icon ast-scroll-to-top-right" data-on-devices="both">
        <span class="ast-icon icon-arrow"></span> <span class="screen-reader-text">Scroll to Top</span>
    </a>
    <script id='astra-theme-js-js-extra'>
        var astra = {
            "break_point": "921",
            "isRtl": ""
        };
    </script>
    <script src='wp-content/themes/astra/assets/js/minified/style.mind617.js?ver=3.3.2' id='astra-theme-js-js'>
    </script>
    <script src='wp-includes/js/imagesloaded.mineda1.js?ver=4.1.4' id='imagesloaded-js'></script>
    <script src='wp-includes/js/masonry.min3a05.js?ver=4.2.2' id='masonry-js'></script>
    <script src='wp-includes/js/jquery/jquery.masonry.minef70.js?ver=3.1.2b' id='jquery-masonry-js'></script>

    <script src='wp-content/plugins/thrive-visual-editor/editor/js/dist/frontend.mina19e.js?ver=2.6.9'
        id='tve_frontend-js'></script>
    <script id='tve-dash-frontend-js-extra'>
        var tve_dash_front = {
            "ajaxurl": "https:\/\/<?php echo $url; ?>\/wp-admin\/admin-ajax.php",
            "force_ajax_send": "",
            "is_crawler": "1",
            "recaptcha": []
        };
    </script>
    <script src='wp-content/plugins/thrive-visual-editor/thrive-dashboard/js/dist/frontend.mina305.js?ver=2.4.2'
        id='tve-dash-frontend-js'></script>
    <script id='astra-addon-js-js-extra'>
        var astraAddon = {
            "sticky_active": "1",
            "svgIconClose": "<span class=\"ast-icon icon-close\"><\/span>",
            "header_main_stick": "1",
            "header_above_stick": "1",
            "header_below_stick": "0",
            "stick_header_meta": "",
            "header_main_stick_meta": "",
            "header_above_stick_meta": "",
            "header_below_stick_meta": "",
            "sticky_header_on_devices": "desktop",
            "sticky_header_style": "none",
            "sticky_hide_on_scroll": "",
            "break_point": "921",
            "tablet_break_point": "768",
            "mobile_break_point": "544",
            "header_main_shrink": "1",
            "header_logo_width": "",
            "responsive_header_logo_width": {
                "desktop": "329",
                "tablet": "",
                "mobile": ""
            },
            "stick_origin_position": "",
            "site_layout": "",
            "site_content_width": "1240",
            "site_layout_padded_width": "1200",
            "site_layout_box_width": "1200",
            "header_builder_active": "",
            "component_limit": "10",
            "is_header_builder_active": ""
        };
    </script>
    <script src='wp-content/uploads/astra-addon/astra-addon-6078642b295655-171800443d36.js?ver=3.3.1'
        id='astra-addon-js-js'></script>
    <script src='wp-includes/js/wp-embed.minc62d.js?ver=c4be1ef428378af680af545453e0eeea' id='wp-embed-js'></script>
    <script type="text/javascript">
        var tcb_post_lists = JSON.parse('[]');
    </script>
    <script>
        /(trident|msie)/i.test(navigator.userAgent) && document.getElementById && window.addEventListener && window
            .addEventListener("hashchange", function() {
                var t, e = location.hash.substring(1);
                /^[A-z0-9_-]+$/.test(e) && (t = document.getElementById(e)) && (
                    /^(?:a|select|input|button|textarea)$/i.test(t.tagName) || (t.tabIndex = -1), t.focus())
            }, !1);
    </script>
</body>
<?php echo $livechat; ?>
<!-- Mirrored from <?php echo $url; ?>/ by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 09 May 2021 16:49:14 GMT -->

</html>