<?php

class Reza_Ervani_Palestina_Topics extends WP_Widget {
    const ID_BASE = 'reza_palestina_topics';
    const HOME_AREA = 'home-palestina-topics';
    const DEFAULT_COUNT = 4;
    const MAX_COUNT = 30;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Rubrik Beranda Palestina', 'mh-healthmag'), array(
            'classname' => 'reza-home-topics-widget',
            'description' => __('Pilih kategori dan jumlah artikel untuk tiga kolom: sorotan, kisi, dan daftar.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    public static function topics() {
        return array(
            'ensiklopedi-al-quds' => array(__('Ensiklopedia al Quds', 'mh-healthmag'), 'feature'),
            'tokoh' => array(__('Tokoh', 'mh-healthmag'), 'grid'),
            'jurnal' => array(__('Jurnal', 'mh-healthmag'), 'list'),
        );
    }

    private function counts($instance) {
        $counts = array();
        foreach (array_keys(self::topics()) as $slug) {
            $value = $instance[$slug] ?? null;
            $counts[$slug] = is_numeric($value) ? max(1, min(self::MAX_COUNT, (int) $value)) : self::DEFAULT_COUNT;
        }
        return $counts;
    }

    private function categories($instance) {
        $categories = array();
        foreach (array_keys(self::topics()) as $slug) {
            $id = absint($instance['categories'][$slug] ?? 0);
            $categories[$slug] = $id && term_exists($id, 'category') ? $id : 0;
        }
        return $categories;
    }

    public function update($new_instance, $old_instance) {
        $instance = $new_instance + $old_instance;
        return $this->counts($instance) + array('categories' => $this->categories($instance));
    }

    public function form($instance) {
        $counts = $this->counts($instance);
        $categories = $this->categories($instance);
        $layouts = array(
            'feature' => __('Sorotan', 'mh-healthmag'),
            'grid' => __('Kisi', 'mh-healthmag'),
            'list' => __('Daftar', 'mh-healthmag'),
        );
        foreach (self::topics() as $slug => list($title, $layout)) :
            $category_field = $this->get_field_name('categories[' . $slug . ']');
            $category_id = $this->get_field_id('category-' . $slug);
            ?>
            <p>
                <label for="<?php echo esc_attr($category_id); ?>"><?php echo esc_html($layouts[$layout]); ?></label>
                <?php wp_dropdown_categories(array(
                    'class' => 'widefat',
                    'name' => $category_field,
                    'id' => $category_id,
                    'selected' => $categories[$slug],
                    'show_option_all' => $title,
                    'hide_empty' => false,
                    'hierarchical' => true,
                )); ?>
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($slug)); ?>"><?php esc_html_e('Jumlah artikel', 'mh-healthmag'); ?></label>
                <input class="widefat" type="number" min="1" max="<?php echo esc_attr(self::MAX_COUNT); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($slug)); ?>" name="<?php echo esc_attr($this->get_field_name($slug)); ?>"
                    value="<?php echo esc_attr($counts[$slug]); ?>">
            </p>
        <?php endforeach; ?>
        <p class="description"><?php esc_html_e('Jika rubrik memakai sorotan, artikel sorotan termasuk dalam jumlah di atas.', 'mh-healthmag'); ?></p>
        <?php
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        get_template_part('template-parts/home', 'palestina-topics', array(
            'counts' => $this->counts($instance),
            'categories' => $this->categories($instance),
            'widget_id' => $args['widget_id'] ?? wp_unique_id('reza-home-topics-'),
        ));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_palestina_topics_widget() {
    register_widget(Reza_Ervani_Palestina_Topics::class);
}
add_action('widgets_init', 'reza_ervani_register_palestina_topics_widget');
