<?php

/**
 * Helper class in plugin photogallery
 */
class helper_plugin_photogallery extends DokuWiki_Plugin
{
    /**
     * Callback for usort()
     * Sorts *_poster_.jpg after corresponding video files
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortFoundFiles($a, $b)
    {
        $a = $a['file'];
        $b = $b['file'];
        if ($a == $b) {
            return 0;
        }

        $thumbEnd = '_poster_.jpg';
        $strippedA = str_replace($thumbEnd, '', $a);

        return (strpos($b, $strippedA) === 0) ? 1 : -1;
    }
}
