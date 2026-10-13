<?php namespace Hampel\PrivateSite;

class Listener
{
    /**
     * Declares this add-on's secrets to the Admin API. The home message is not one, so the list
     * is empty; the entry itself is what lets the Admin API show the add-on's options.
     */
    public static function adminApiRedaction(array &$declarations)
    {
        $declarations['Hampel/PrivateSite'] = [];
    }
}
