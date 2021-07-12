<?php

namespace App\Helpers;

/**
 * @see https://github.com/fzaninotto/Faker/issues/1884
 */
class FakerHelper
{
    /**
     * Generate the URL that will return a random image
     *
     * Set randomize to false to remove the random GET parameter at the end of the url.
     *
     * @example 'https://picsum.photos/id/237/200/300'
     *
     * @param integer $width
     * @param integer $height
     * @param string|null $_category NOT USED
     * @param bool $randomize
     * @param string|null $_word NOT USED
     * @param bool $gray
     *
     * @return string
     * @see vendor/fzaninotto/faker/src/Faker/Provider/Image.php > imageUrl
     */
    public static function getImageUrl($width = 640, $height = 480, $_category = null, $randomize = true, $_word = null, $gray = false)
    {
        $baseUrl = 'https://picsum.photos/';
        $url = '';

        if (!$randomize) {
            $url .= 'id/' . random_int(1, 1000) . '/';
        }

        $url .= "$width/$height/";

        if ($gray) {
            $url .= '?grayscale';
        }

        if ($randomize) {
            $url .= str_contains($url, '?') ? '&' : '?';
            $url .= 'random=' . random_int(1, 1000);
        }

        return $baseUrl . $url;
    }

    public function imageUrl($width = 640, $height = 480, $_category = null, $randomize = true, $_word = null, $gray = false)
    {
        return self::getImageUrl($width, $height, $_category, $randomize, $_word, $gray);
    }

    public static function changeUrl($original_url)
    {
        if (str_starts_with($original_url, 'https://lorempixel.com/')) {
            $o = str_replace('https://lorempixel.com/', '', $original_url);

            $parts = explode('/', $o);
            $width = $parts[0];
            $height = $parts[1];
            $gray = str_contains($original_url, '/gray/');
            $randomize = str_contains($original_url, '?');

            return self::getImageUrl($width, $height, null, $randomize, null, $gray);
        }

        return $original_url;
    }
}
