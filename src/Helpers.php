<?php

namespace WebDevEtc\BlogEtc;

use Session;

/**
 * Small little helper class of static methods.
 */
class Helpers
{
    /**
     * What key to use for the session::flash / pull / has.
     */
    const FLASH_MESSAGE_SESSION_KEY = 'WEBDEVETC_FLASH';

    /**
     * Set a new flash message - used in the BlogEtc Admin panel to flash messages to user
     * such as 'post created'.
     */
    public static function flashMessage(string $message): void
    {
        Session::flash(self::FLASH_MESSAGE_SESSION_KEY, $message);
    }

    /**
     * Is there a flashed message?
     */
    public static function hasFlashedMessage(): bool
    {
        return Session::has(self::FLASH_MESSAGE_SESSION_KEY);
    }

    /**
     * return the flashed message. Use with ::has_flashed_message() if you need to check if it has a value...
     *
     * @return string
     */
    public static function pullFlashedMessage(): ?string
    {
        return Session::pull(self::FLASH_MESSAGE_SESSION_KEY);
    }

    /**
     * Use this in your blade/template files, within <head> to auto insert the links to rss feed.
     */
    public static function rssHtmlTag(): string
    {
        return '<link rel="alternate" type="application/atom+xml" title="Atom RSS Feed" href="'
            .e(route('blogetc.feed')).'?type=atom" />'
            .'<link rel="alternate" type="application/rss+xml" title="XML RSS Feed" href="'
            .e(route('blogetc.feed')).'?type=rss" />';
    }

    //## Depreciated methods:

    /**
     * @deprecated use pullFlashedMessage() instead
     */
    public static function pull_flashed_message(): ?string
    {
        return self::pullFlashedMessage();
    }

    /**
     * @deprecated use hasFlashedMessage() instead
     */
    public static function has_flashed_message(): bool
    {
        return self::hasFlashedMessage();
    }

    /**
     * @deprecated use flashMessage() instead
     */
    public static function flash_message(string $message): void
    {
        self::flashMessage($message);
    }

    /**
     * @deprecated use rssHtmlTag() instead
     */
    public static function rss_html_tag(): string
    {
        return self::rssHtmlTag();
    }

    /**
     * This method is depreciated. Just use the config() directly.
     *
     * @deprecated
     */
    public static function image_sizes(): array
    {
        return config('blogetc.image_sizes');
    }
}
