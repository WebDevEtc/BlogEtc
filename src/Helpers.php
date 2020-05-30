<?php

namespace WebDevEtc\BlogEtc;

use Gate;
use Session;
use WebDevEtc\BlogEtc\Gates\GateTypes;

/**
 * Small little helper class of static methods.
 */
class Helpers
{
    /**
     * What key to use for the session::flash / pull / has.
     */
    const FLASH_MESSAGE_SESSION_KEY = 'WEBDEVETC_FLASH';

    public static function hasAdminGateAccess(): bool
    {
        return Gate::allows(GateTypes::MANAGE_ADMIN);
    }

    public static function hasCommentGateAccess(): bool
    {
        return Gate::allows(GateTypes::ADD_COMMENTS);
    }

    /**
     * @deprecated use pullFlashedMessage() instead
     */
    public static function pull_flashed_message(): ?string
    {
        return self::pullFlashedMessage();
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
     * @deprecated use hasFlashedMessage() instead
     */
    public static function has_flashed_message(): bool
    {
        return self::hasFlashedMessage();
    }

    /**
     * Is there a flashed message?
     */
    public static function hasFlashedMessage(): bool
    {
        return Session::has(self::FLASH_MESSAGE_SESSION_KEY);
    }

    //## Depreciated methods:

    /**
     * @deprecated use flashMessage() instead
     */
    public static function flash_message(string $message): void
    {
        self::flashMessage($message);
    }

    /**
     * Set a new flash message - used in the BlogEtc Admin panel to flash messages to user
     * such as 'post created'.
     */
    public static function flashMessage(string $message): void
    {
        Session::flash(self::FLASH_MESSAGE_SESSION_KEY, $message);
    }

    /**
     * @deprecated use rssHtmlTag() instead
     */
    public static function rss_html_tag(): string
    {
        return self::rssHtmlTag();
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
