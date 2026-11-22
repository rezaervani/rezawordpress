<?php

function reza_ervani_palestina_footer_defaults() {
    return array(
        'footer-2' => array(
            array('custom_html', array(
                'title' => 'Tulisan Para Masyaikh',
                'content' => <<<'HTML'
<p><a href="https://rezaervani.com/category/ulama/imam-ibnu-taimiyah/">Syaikhul Islam Ibnu Taimiyah</a><br>
<a href="https://rezaervani.com/category/ulama/syaikh-bin-baz/">Syaikh Abdul Aziz bin Baz</a><br>
<a href="https://rezaervani.com/category/ulama/syaikh-ibn-utsaimin/">Syaikh Muhammad Shalih bin Utsaimin</a><br>
<a href="https://rezaervani.com/category/ulama/syaikh-wahbah-zuhailiy/">Syaikh Wahbah Zuhailiy</a><br>
<a href="https://rezaervani.com/category/ulama/dr-labib-najib-abdullah/">Syaikh Labib Najib Abdullah</a><br>
<a href="https://rezaervani.com/category/ulama/syaikh-abdul-aziz-ath-thariifiy/">Syaikh Abdul Aziz ah Tharifi</a><br>
<a href="https://rezaervani.com/category/ulama/prof-dr-abdul-ghani-muhammad-barakah/">Prof. Dr. Abdul Ghani Muhammad Barakah</a><br>
<a href="https://rezaervani.com/category/ulama/syaikh-ali-muhammad-ash-shalaby/">Syaikh DR. Muhammad Ali ash Shalabiy</a><br>
<a href="https://rezaervani.com/video-category/syaikh-adham-al-aasimiy/">Syaikh Adham al Aasimiy</a></p>
HTML
            )),
            array('custom_html', array(
                'title' => 'Topik Lain di web rezaervani.com',
                'content' => <<<'HTML'
Anda dapat juga mengunjungi materi-materi lain di website saya berikut ini.
<ol>
    <li><a href="https://opensource.rezaervani.com">Materi dan Berita Teknologi Informasi dan Open Source - opensource.rezaervani.com</a></li>
    <li><a href="https://arduino.rezaervani.com">Materi Pembelajaran Arduino dan Elektronika - arduino.rezaervani.com</a></li>
    <li><a href="https://programming.rezaervani.com">Materi Pemrogramman - programming.rezaervani.com</a></li>
</ol>
HTML
            )),
        ),
        'footer-3' => array(
            array('custom_html', array(
                'title' => 'Kontak',
                'content' => <<<'HTML'
<p>WhatsApp +62 812 132 08078 dan +62 812 8754 8752</p>
<p>Email <a href="mailto:reza@rezaervani.com">reza@rezaervani.com</a></p>
HTML
            )),
            array('custom_html', array(
                'title' => 'Disclaimer',
                'content' => <<<'HTML'
<p>Blog pribadi ini dikelola secara independen.</p>
<p>Sebagai blog pribadi, dia difungsikan untuk mencatat, baik dalam bentuk catatan utuh maupun coretan draft. Sama seperti buku catatan manual, hanya saja dalam bentuk digital.</p>
HTML
            )),
        ),
        'footer-4' => array(
            array('custom_html', array(
                'title' => 'Proyek Kecilku',
                'content' => <<<'HTML'
<ul>
    <li><a href="https://rezaervani.com/sekolah/" target="_blank" rel="noopener">Sekolah Terbuka Rumah Ilmu Indonesia</a></li>
    <li><a href="https://rezaervani.com/maktabah/" target="_blank" rel="noopener">Maktabah Rumah Ilmu Indonesia</a></li>
    <li><a href="https://rezaervani.com/social/" target="_blank" rel="noopener">mikroNote | Media Sosial Positif</a></li>
</ul>
HTML
            )),
            array('categories', array(
                'title' => 'Kategori',
                'count' => 1,
                'hierarchical' => 0,
                'dropdown' => 1,
            )),
            array('archives', array(
                'title' => 'Arsip',
                'count' => 1,
                'dropdown' => 1,
            )),
        ),
    );
}

function reza_ervani_migrate_palestina_footer_widgets() {
    $migration = 'reza_ervani_palestina_footer_widgets_initialized';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $sidebars = (array) get_option('sidebars_widgets', array());
    $assigned = array_merge(array(), ...array_values(array_filter($sidebars, 'is_array')));
    $options = array();
    $additions = array();

    foreach (reza_ervani_palestina_footer_defaults() as $area => $widgets) {
        if (!empty($sidebars[$area])) {
            continue;
        }

        foreach ($widgets as list($type, $settings)) {
            $option = 'widget_' . $type;
            $options[$option] = $options[$option] ?? (array) get_option($option, array());
            $instances = &$options[$option];

            // Reuse unassigned defaults from an earlier interrupted run.
            $number = false;
            foreach (array_keys($instances, $settings, true) as $candidate) {
                if (is_int($candidate) && !in_array($type . '-' . $candidate, $assigned, true)) {
                    $number = $candidate;
                    break;
                }
            }
            if ($number === false) {
                $number = 2;
                while (isset($instances[$number]) || in_array($type . '-' . $number, $assigned, true)) {
                    $number++;
                }
            }

            $instances[$number] = $settings;
            $instances['_multiwidget'] = 1;
            unset($instances);
            $id = $type . '-' . $number;
            $assigned[] = $id;
            $additions[$area][] = $id;
        }
    }

    foreach ($options as $option => $instances) {
        if (!update_option($option, $instances) && get_option($option) !== $instances) {
            return;
        }
    }

    if ($additions) {
        $sidebars = (array) get_option('sidebars_widgets', array());
        foreach ($additions as $area => $ids) {
            if (empty($sidebars[$area])) {
                $sidebars[$area] = $ids;
            }
        }
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        wp_set_sidebars_widgets($sidebars);
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option($migration, 1, false);
}
add_action('widgets_init', 'reza_ervani_migrate_palestina_footer_widgets', 95);
