<?php
namespace WebDevEtc\BlogEtc;

use Session;

/**
 * Class Helpers
 * @package WebDevEtc\BlogEtc
 */
class Helpers
{
    /**
     * What key to use for the session::flash / pull / has
     */
    const FLASH_MESSAGE_SESSION_KEY = "WEBDEVETC_FLASH";

    /**
     * Set a new message
     *
     * @param string $message
     */
    public static function flash_message(string $message)
    {
        Session::flash(Helpers::FLASH_MESSAGE_SESSION_KEY, $message);
    }

    /**
     * Is there a flashed message?
     *
     * @return bool
     */
    public static function has_flashed_message()
    {
        return Session::has(self::FLASH_MESSAGE_SESSION_KEY);
    }

    /**
     * return the flashed message. Use with ::has_flashed_message() if you need to check if it has a value...
     * @return string
     */
    public static function pull_flashed_message()
    {
        return Session::pull(self::FLASH_MESSAGE_SESSION_KEY);
    }

    /**
     * @return string
     */
    public static function rss_html_tag()
    {


        return '<link rel="alternate" type="application/rss+xml"
  title="Atom RSS Feed"
  href="' . e(route("blogetc.feed")) . '?type=atom" />

  <link rel="alternate" type="application/rss+xml"
  title="XML RSS Feed"
  href="' . e(route("blogetc.feed")) . '?type=rss" />

  ';


    }

    /**
     * Thie method is depreciated. Just use the config() directly.
     * @return array
     */
    public static function image_sizes(){
        return config("blogetc.image_sizes");
    }

}
