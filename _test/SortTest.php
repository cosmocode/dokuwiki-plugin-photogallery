<?php

namespace dokuwiki\plugin\photogallery\test;


use dokuwiki\Utf8\Sort;

/**
 * Sort test for the photogallery plugin
 *
 * @group plugin_photogallery
 * @group plugins
 */
class SortTest extends \DokuWikiTest
{
    protected $pluginsEnabled = ['photogallery'];

    public function testSort()
    {
        $files = array (
            0 =>
                array (
                    'id' => 'test:kwa:piketty_-_brahmin_left_vs_merchant_right.pdf',
                    'perm' => 255,
                    'file' => 'piketty_-_brahmin_left_vs_merchant_right.pdf',
                    'size' => 1398231,
                    'mtime' => 1579624636,
                    'writable' => true,
                    'isimg' => false,
                ),
            1 =>
                array (
                    'id' => 'test:kwa:testkwa4.svg',
                    'perm' => 255,
                    'file' => 'testkwa4.svg',
                    'size' => 1346,
                    'mtime' => 1609958973,
                    'writable' => true,
                    'isimg' => false,
                ),
            2 =>
                array (
                    'id' => 'test:kwa:testkwa5.svg',
                    'perm' => 255,
                    'file' => 'testkwa5.svg',
                    'size' => 1129,
                    'mtime' => 1609960004,
                    'writable' => true,
                    'isimg' => false,
                ),
            3 =>
                array (
                    'id' => 'test:kwa:testkwa6.svg',
                    'perm' => 255,
                    'file' => 'testkwa6.svg',
                    'size' => 1175,
                    'mtime' => 1609961416,
                    'writable' => true,
                    'isimg' => false,
                ),
            4 =>
                array (
                    'id' => 'test:kwa:testkwa10.svg',
                    'perm' => 255,
                    'file' => 'testkwa10.svg',
                    'size' => 2327,
                    'mtime' => 1609963037,
                    'writable' => true,
                    'isimg' => false,
                ),
            5 =>
                array (
                    'id' => 'test:kwa:testkwa11.svg',
                    'perm' => 255,
                    'file' => 'testkwa11.svg',
                    'size' => 2290,
                    'mtime' => 1609963463,
                    'writable' => true,
                    'isimg' => false,
                ),
            6 =>
                array (
                    'id' => 'test:kwa:testkwa12.svg',
                    'perm' => 255,
                    'file' => 'testkwa12.svg',
                    'size' => 2196,
                    'mtime' => 1609963528,
                    'writable' => true,
                    'isimg' => false,
                ),
            7 =>
                array (
                    'id' => 'test:kwa:testkwa13.svg',
                    'perm' => 255,
                    'file' => 'testkwa13.svg',
                    'size' => 2194,
                    'mtime' => 1609963643,
                    'writable' => true,
                    'isimg' => false,
                ),
            8 =>
                array (
                    'id' => 'test:kwa:testkwa14.svg',
                    'perm' => 255,
                    'file' => 'testkwa14.svg',
                    'size' => 2292,
                    'mtime' => 1611425259,
                    'writable' => true,
                    'isimg' => false,
                ),
            9 =>
                array (
                    'id' => 'test:kwa:testkwa15.svg',
                    'perm' => 255,
                    'file' => 'testkwa15.svg',
                    'size' => 2472,
                    'mtime' => 1610033061,
                    'writable' => true,
                    'isimg' => false,
                ),
            10 =>
                array (
                    'id' => 'test:kwa:testkwa16.svg',
                    'perm' => 255,
                    'file' => 'testkwa16.svg',
                    'size' => 2253,
                    'mtime' => 1611425248,
                    'writable' => true,
                    'isimg' => false,
                ),
            11 =>
                array (
                    'id' => 'test:photogallery:flaming.jpg',
                    'perm' => 255,
                    'file' => 'flaming.jpg',
                    'size' => 477068,
                    'mtime' => 1673893608,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/photogallery/flaming.jpg',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            12 =>
                array (
                    'id' => 'test:photogallery:jackson_five_-_can_you_feel_it.mp4',
                    'perm' => 255,
                    'file' => 'jackson_five_-_can_you_feel_it.mp4',
                    'size' => 22833147,
                    'mtime' => 1673459617,
                    'writable' => true,
                    'isimg' => false,
                ),
            13 =>
                array (
                    'id' => 'test:photogallery:jackson_five_-_can_you_feel_it_poster_.jpg',
                    'perm' => 255,
                    'file' => 'jackson_five_-_can_you_feel_it_poster_.jpg',
                    'size' => 94839,
                    'mtime' => 1673566925,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/photogallery/jackson_five_-_can_you_feel_it_poster_.jpg',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            14 =>
                array (
                    'id' => 'test:plugins:chicago.jpg',
                    'perm' => 255,
                    'file' => 'chicago.jpg',
                    'size' => 2205407,
                    'mtime' => 1606330345,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/plugins/chicago.jpg',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            15 =>
                array (
                    'id' => 'test:plugins:dram.svg',
                    'perm' => 255,
                    'file' => 'dram.svg',
                    'size' => 1311,
                    'mtime' => 1615823685,
                    'writable' => true,
                    'isimg' => false,
                ),
            16 =>
                array (
                    'id' => 'test:plugins:drawing.svg',
                    'perm' => 255,
                    'file' => 'drawing.svg',
                    'size' => 235,
                    'mtime' => 1605641645,
                    'writable' => true,
                    'isimg' => false,
                ),
            17 =>
                array (
                    'id' => 'test:plugins:fri.svg',
                    'perm' => 255,
                    'file' => 'fri.svg',
                    'size' => 2386,
                    'mtime' => 1609697637,
                    'writable' => true,
                    'isimg' => false,
                ),
            18 =>
                array (
                    'id' => 'test:plugins:hel.svg',
                    'perm' => 255,
                    'file' => 'hel.svg',
                    'size' => 2269,
                    'mtime' => 1614286409,
                    'writable' => true,
                    'isimg' => false,
                ),
            19 =>
                array (
                    'id' => 'test:plugins:loglog.svg',
                    'perm' => 255,
                    'file' => 'loglog.svg',
                    'size' => 10437,
                    'mtime' => 1611425299,
                    'writable' => true,
                    'isimg' => false,
                ),
            20 =>
                array (
                    'id' => 'test:plugins:mytest.svg',
                    'perm' => 255,
                    'file' => 'mytest.svg',
                    'size' => 1098,
                    'mtime' => 1605729042,
                    'writable' => true,
                    'isimg' => false,
                ),
            21 =>
                array (
                    'id' => 'test:plugins:njuio.svg',
                    'perm' => 255,
                    'file' => 'njuio.svg',
                    'size' => 8039,
                    'mtime' => 1604354894,
                    'writable' => true,
                    'isimg' => false,
                ),
            22 =>
                array (
                    'id' => 'test:plugins:notroot.svg',
                    'perm' => 255,
                    'file' => 'notroot.svg',
                    'size' => 2189,
                    'mtime' => 1614284195,
                    'writable' => true,
                    'isimg' => false,
                ),
            23 =>
                array (
                    'id' => 'test:plugins:refactored.svg',
                    'perm' => 255,
                    'file' => 'refactored.svg',
                    'size' => 2505,
                    'mtime' => 1611344521,
                    'writable' => true,
                    'isimg' => false,
                ),
            24 =>
                array (
                    'id' => 'test:plugins:test_organigramm.svg',
                    'perm' => 255,
                    'file' => 'test_organigramm.svg',
                    'size' => 24096,
                    'mtime' => 1614544010,
                    'writable' => true,
                    'isimg' => false,
                ),
            25 =>
                array (
                    'id' => 'test:plugins:testdiagr.svg',
                    'perm' => 255,
                    'file' => 'testdiagr.svg',
                    'size' => 2471,
                    'mtime' => 1605733798,
                    'writable' => true,
                    'isimg' => false,
                ),
            26 =>
                array (
                    'id' => 'test:plugins:testplugins1.svg',
                    'perm' => 255,
                    'file' => 'testplugins1.svg',
                    'size' => 2615,
                    'mtime' => 1612289525,
                    'writable' => true,
                    'isimg' => false,
                ),
            27 =>
                array (
                    'id' => 'test:plugins:very.svg',
                    'perm' => 255,
                    'file' => 'very.svg',
                    'size' => 2646,
                    'mtime' => 1606330600,
                    'writable' => true,
                    'isimg' => false,
                ),
            28 =>
                array (
                    'id' => 'test:aclcheck.svg',
                    'perm' => 255,
                    'file' => 'aclcheck.svg',
                    'size' => 1102,
                    'mtime' => 1649255517,
                    'writable' => true,
                    'isimg' => false,
                ),
            29 =>
                array (
                    'id' => 'test:aclcheck2.svg',
                    'perm' => 255,
                    'file' => 'aclcheck2.svg',
                    'size' => 1233,
                    'mtime' => 1649255808,
                    'writable' => true,
                    'isimg' => false,
                ),
            30 =>
                array (
                    'id' => 'test:bildschirmfoto_2023-01-11_um_20.46.18.png',
                    'perm' => 255,
                    'file' => 'bildschirmfoto_2023-01-11_um_20.46.18.png',
                    'size' => 7346553,
                    'mtime' => 1673570009,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/bildschirmfoto_2023-01-11_um_20.46.18.png',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            31 =>
                array (
                    'id' => 'test:hihi.svg',
                    'perm' => 255,
                    'file' => 'hihi.svg',
                    'size' => 2272,
                    'mtime' => 1649246329,
                    'writable' => true,
                    'isimg' => false,
                ),
            32 =>
                array (
                    'id' => 'test:no-nav.png',
                    'perm' => 255,
                    'file' => 'no-nav.png',
                    'size' => 883694,
                    'mtime' => 1673458808,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/no-nav.png',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            33 =>
                array (
                    'id' => 'test:oho.svg',
                    'perm' => 255,
                    'file' => 'oho.svg',
                    'size' => 2789,
                    'mtime' => 1614282318,
                    'writable' => true,
                    'isimg' => false,
                ),
        );

        $expected = array (
            0 =>
                array (
                    'id' => 'test:kwa:piketty_-_brahmin_left_vs_merchant_right.pdf',
                    'perm' => 255,
                    'file' => 'piketty_-_brahmin_left_vs_merchant_right.pdf',
                    'size' => 1398231,
                    'mtime' => 1579624636,
                    'writable' => true,
                    'isimg' => false,
                ),
            1 =>
                array (
                    'id' => 'test:kwa:testkwa4.svg',
                    'perm' => 255,
                    'file' => 'testkwa4.svg',
                    'size' => 1346,
                    'mtime' => 1609958973,
                    'writable' => true,
                    'isimg' => false,
                ),
            2 =>
                array (
                    'id' => 'test:kwa:testkwa5.svg',
                    'perm' => 255,
                    'file' => 'testkwa5.svg',
                    'size' => 1129,
                    'mtime' => 1609960004,
                    'writable' => true,
                    'isimg' => false,
                ),
            3 =>
                array (
                    'id' => 'test:kwa:testkwa6.svg',
                    'perm' => 255,
                    'file' => 'testkwa6.svg',
                    'size' => 1175,
                    'mtime' => 1609961416,
                    'writable' => true,
                    'isimg' => false,
                ),
            4 =>
                array (
                    'id' => 'test:kwa:testkwa10.svg',
                    'perm' => 255,
                    'file' => 'testkwa10.svg',
                    'size' => 2327,
                    'mtime' => 1609963037,
                    'writable' => true,
                    'isimg' => false,
                ),
            5 =>
                array (
                    'id' => 'test:kwa:testkwa11.svg',
                    'perm' => 255,
                    'file' => 'testkwa11.svg',
                    'size' => 2290,
                    'mtime' => 1609963463,
                    'writable' => true,
                    'isimg' => false,
                ),
            6 =>
                array (
                    'id' => 'test:kwa:testkwa12.svg',
                    'perm' => 255,
                    'file' => 'testkwa12.svg',
                    'size' => 2196,
                    'mtime' => 1609963528,
                    'writable' => true,
                    'isimg' => false,
                ),
            7 =>
                array (
                    'id' => 'test:kwa:testkwa13.svg',
                    'perm' => 255,
                    'file' => 'testkwa13.svg',
                    'size' => 2194,
                    'mtime' => 1609963643,
                    'writable' => true,
                    'isimg' => false,
                ),
            8 =>
                array (
                    'id' => 'test:kwa:testkwa14.svg',
                    'perm' => 255,
                    'file' => 'testkwa14.svg',
                    'size' => 2292,
                    'mtime' => 1611425259,
                    'writable' => true,
                    'isimg' => false,
                ),
            9 =>
                array (
                    'id' => 'test:kwa:testkwa15.svg',
                    'perm' => 255,
                    'file' => 'testkwa15.svg',
                    'size' => 2472,
                    'mtime' => 1610033061,
                    'writable' => true,
                    'isimg' => false,
                ),
            10 =>
                array (
                    'id' => 'test:kwa:testkwa16.svg',
                    'perm' => 255,
                    'file' => 'testkwa16.svg',
                    'size' => 2253,
                    'mtime' => 1611425248,
                    'writable' => true,
                    'isimg' => false,
                ),
            11 =>
                array (
                    'id' => 'test:photogallery:flaming.jpg',
                    'perm' => 255,
                    'file' => 'flaming.jpg',
                    'size' => 477068,
                    'mtime' => 1673893608,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/photogallery/flaming.jpg',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            12 =>
                array (
                    'id' => 'test:photogallery:jackson_five_-_can_you_feel_it.mp4',
                    'perm' => 255,
                    'file' => 'jackson_five_-_can_you_feel_it.mp4',
                    'size' => 22833147,
                    'mtime' => 1673459617,
                    'writable' => true,
                    'isimg' => false,
                ),
            13 =>
            array (
                'id' => 'test:photogallery:jackson_five_-_can_you_feel_it_poster_.jpg',
                'perm' => 255,
                'file' => 'jackson_five_-_can_you_feel_it_poster_.jpg',
                'size' => 94839,
                'mtime' => 1673566925,
                'writable' => true,
                'isimg' => true,
                'meta' =>
                    array(
                        '_fileName' => '/var/www/dokuwiki/data/media/test/photogallery/jackson_five_-_can_you_feel_it_poster_.jpg',
                        '_fp' => NULL,
                        '_fpout' => NULL,
                        '_type' => 'unknown',
                    ),
            ),
            14 =>
                array (
                    'id' => 'test:plugins:chicago.jpg',
                    'perm' => 255,
                    'file' => 'chicago.jpg',
                    'size' => 2205407,
                    'mtime' => 1606330345,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/plugins/chicago.jpg',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            15 =>
                array (
                    'id' => 'test:plugins:dram.svg',
                    'perm' => 255,
                    'file' => 'dram.svg',
                    'size' => 1311,
                    'mtime' => 1615823685,
                    'writable' => true,
                    'isimg' => false,
                ),
            16 =>
                array (
                    'id' => 'test:plugins:drawing.svg',
                    'perm' => 255,
                    'file' => 'drawing.svg',
                    'size' => 235,
                    'mtime' => 1605641645,
                    'writable' => true,
                    'isimg' => false,
                ),
            17 =>
                array (
                    'id' => 'test:plugins:fri.svg',
                    'perm' => 255,
                    'file' => 'fri.svg',
                    'size' => 2386,
                    'mtime' => 1609697637,
                    'writable' => true,
                    'isimg' => false,
                ),
            18 =>
                array (
                    'id' => 'test:plugins:hel.svg',
                    'perm' => 255,
                    'file' => 'hel.svg',
                    'size' => 2269,
                    'mtime' => 1614286409,
                    'writable' => true,
                    'isimg' => false,
                ),
            19 =>
                array (
                    'id' => 'test:plugins:loglog.svg',
                    'perm' => 255,
                    'file' => 'loglog.svg',
                    'size' => 10437,
                    'mtime' => 1611425299,
                    'writable' => true,
                    'isimg' => false,
                ),
            20 =>
                array (
                    'id' => 'test:plugins:mytest.svg',
                    'perm' => 255,
                    'file' => 'mytest.svg',
                    'size' => 1098,
                    'mtime' => 1605729042,
                    'writable' => true,
                    'isimg' => false,
                ),
            21 =>
                array (
                    'id' => 'test:plugins:njuio.svg',
                    'perm' => 255,
                    'file' => 'njuio.svg',
                    'size' => 8039,
                    'mtime' => 1604354894,
                    'writable' => true,
                    'isimg' => false,
                ),
            22 =>
                array (
                    'id' => 'test:plugins:notroot.svg',
                    'perm' => 255,
                    'file' => 'notroot.svg',
                    'size' => 2189,
                    'mtime' => 1614284195,
                    'writable' => true,
                    'isimg' => false,
                ),
            23 =>
                array (
                    'id' => 'test:plugins:refactored.svg',
                    'perm' => 255,
                    'file' => 'refactored.svg',
                    'size' => 2505,
                    'mtime' => 1611344521,
                    'writable' => true,
                    'isimg' => false,
                ),
            24 =>
                array (
                    'id' => 'test:plugins:test_organigramm.svg',
                    'perm' => 255,
                    'file' => 'test_organigramm.svg',
                    'size' => 24096,
                    'mtime' => 1614544010,
                    'writable' => true,
                    'isimg' => false,
                ),
            25 =>
                array (
                    'id' => 'test:plugins:testdiagr.svg',
                    'perm' => 255,
                    'file' => 'testdiagr.svg',
                    'size' => 2471,
                    'mtime' => 1605733798,
                    'writable' => true,
                    'isimg' => false,
                ),
            26 =>
                array (
                    'id' => 'test:plugins:testplugins1.svg',
                    'perm' => 255,
                    'file' => 'testplugins1.svg',
                    'size' => 2615,
                    'mtime' => 1612289525,
                    'writable' => true,
                    'isimg' => false,
                ),
            27 =>
                array (
                    'id' => 'test:plugins:very.svg',
                    'perm' => 255,
                    'file' => 'very.svg',
                    'size' => 2646,
                    'mtime' => 1606330600,
                    'writable' => true,
                    'isimg' => false,
                ),
            28 =>
                array (
                    'id' => 'test:aclcheck.svg',
                    'perm' => 255,
                    'file' => 'aclcheck.svg',
                    'size' => 1102,
                    'mtime' => 1649255517,
                    'writable' => true,
                    'isimg' => false,
                ),
            29 =>
                array (
                    'id' => 'test:aclcheck2.svg',
                    'perm' => 255,
                    'file' => 'aclcheck2.svg',
                    'size' => 1233,
                    'mtime' => 1649255808,
                    'writable' => true,
                    'isimg' => false,
                ),
            30 =>
                array (
                    'id' => 'test:bildschirmfoto_2023-01-11_um_20.46.18.png',
                    'perm' => 255,
                    'file' => 'bildschirmfoto_2023-01-11_um_20.46.18.png',
                    'size' => 7346553,
                    'mtime' => 1673570009,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/bildschirmfoto_2023-01-11_um_20.46.18.png',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            31 =>
                array (
                    'id' => 'test:hihi.svg',
                    'perm' => 255,
                    'file' => 'hihi.svg',
                    'size' => 2272,
                    'mtime' => 1649246329,
                    'writable' => true,
                    'isimg' => false,
                ),
            32 =>
                array (
                    'id' => 'test:no-nav.png',
                    'perm' => 255,
                    'file' => 'no-nav.png',
                    'size' => 883694,
                    'mtime' => 1673458808,
                    'writable' => true,
                    'isimg' => true,
                    'meta' =>
                        array(
                            '_fileName' => '/var/www/dokuwiki/data/media/test/no-nav.png',
                            '_fp' => NULL,
                            '_fpout' => NULL,
                            '_type' => 'unknown',
                        ),
                ),
            33 =>
                array (
                    'id' => 'test:oho.svg',
                    'perm' => 255,
                    'file' => 'oho.svg',
                    'size' => 2789,
                    'mtime' => 1614282318,
                    'writable' => true,
                    'isimg' => false,
                ),
        );

        /** @var \helper_plugin_photogallery $helper */
        $helper = plugin_load('helper', 'photogallery');

        uasort($files, [$helper, 'sortFoundFiles']);

        $this->assertEquals($expected, $files);
    }
}
