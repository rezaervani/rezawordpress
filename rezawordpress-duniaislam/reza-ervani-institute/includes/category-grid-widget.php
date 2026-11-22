<?php

class Reza_Ervani_Category_Grid extends WP_Widget {
    const ID_BASE = 'reza_category_grid';
    const HOME_AREA = 'home-category-grid';
    const MAX_COLUMNS = 8;
    const MAX_ARTICLES = 20;

    public function __construct() {
        parent::__construct(self::ID_BASE, __('Kisi Kategori', 'mh-healthmag'), array(
            'classname' => 'reza-category-grid-widget',
            'description' => __('Kartu kategori dengan satu gambar dan daftar artikel seperti bagian Tanya Jawab.', 'mh-healthmag'),
            'customize_selective_refresh' => true,
        ));
    }

    private function scopes() {
        return array(
            'root' => __('Hanya kategori utama', 'mh-healthmag'),
            'children' => __('Kategori utama dan subkategori', 'mh-healthmag'),
            'grandchildren' => __('Kategori utama hingga sub-subkategori', 'mh-healthmag'),
            'child_cards' => __('Kelompokkan artikel dalam kartu subkategori', 'mh-healthmag'),
        );
    }

    private function settings($instance) {
        $scope = $instance['scope'] ?? '';
        return array(
            'category' => absint($instance['category'] ?? 0),
            'title' => sanitize_text_field($instance['title'] ?? ''),
            'scope' => is_string($scope) && isset($this->scopes()[$scope]) ? $scope : 'root',
            'columns' => max(1, min(self::MAX_COLUMNS, (int) ($instance['columns'] ?? 7))),
            'articles' => max(1, min(self::MAX_ARTICLES, (int) ($instance['articles'] ?? 3))),
        );
    }

    public function update($new_instance, $old_instance) {
        return $this->settings($new_instance + $old_instance);
    }

    public function form($instance) {
        $settings = $this->settings($instance);
        $category_id = $this->get_field_id('category');
        $title_id = $this->get_field_id('title');
        $scope_id = $this->get_field_id('scope');
        ?>
        <p>
            <label for="<?php echo esc_attr($category_id); ?>"><?php esc_html_e('Kategori utama', 'mh-healthmag'); ?></label>
            <?php wp_dropdown_categories(array(
                'id' => $category_id,
                'name' => $this->get_field_name('category'),
                'class' => 'widefat',
                'show_option_none' => __('Pilih kategori', 'mh-healthmag'),
                'option_none_value' => 0,
                'orderby' => 'name',
                'hierarchical' => true,
                'hide_empty' => false,
                'selected' => $settings['category'],
            )); ?>
        </p>
        <p>
            <label for="<?php echo esc_attr($title_id); ?>"><?php esc_html_e('Judul bagian (opsional)', 'mh-healthmag'); ?></label>
            <input class="widefat" type="text" id="<?php echo esc_attr($title_id); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($settings['title']); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($scope_id); ?>"><?php esc_html_e('Cakupan kategori', 'mh-healthmag'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($scope_id); ?>" name="<?php echo esc_attr($this->get_field_name('scope')); ?>">
                <?php foreach ($this->scopes() as $scope => $label) : ?>
                    <option value="<?php echo esc_attr($scope); ?>" <?php selected($settings['scope'], $scope); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php foreach (array(
            'columns' => array(__('Jumlah kolom per baris di layar lebar', 'mh-healthmag'), self::MAX_COLUMNS),
            'articles' => array(__('Jumlah artikel per kategori', 'mh-healthmag'), self::MAX_ARTICLES),
        ) as $field => list($label, $max)) : ?>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id($field)); ?>"><?php echo esc_html($label); ?></label>
                <input class="widefat" type="number" min="1" max="<?php echo esc_attr($max); ?>" step="1"
                    id="<?php echo esc_attr($this->get_field_id($field)); ?>" name="<?php echo esc_attr($this->get_field_name($field)); ?>"
                    value="<?php echo esc_attr($settings[$field]); ?>">
            </p>
        <?php endforeach;
    }

    public function widget($args, $instance) {
        $settings = $this->settings($instance);
        $category = get_term($settings['category'], 'category');
        if (!$category || is_wp_error($category)) {
            return;
        }

        $cards = reza_ervani_editorial_question_cards($category, $settings['scope'], $settings['articles']);
        if (!$cards) {
            return;
        }

        $title = $settings['title'] !== '' ? $settings['title'] : $category->name;
        $title_prefix = 'reza-editorial-questions-';
        echo $args['before_widget'];
        get_template_part('template-parts/editorial', 'questions', array(
            'category' => $category,
            'cards' => $cards,
            'columns' => $settings['columns'],
            'title' => apply_filters('widget_title', $title, $instance, $this->id_base),
            'title_id' => empty($args['widget_id']) ? wp_unique_id($title_prefix) : $title_prefix . $args['widget_id'],
        ));
        echo $args['after_widget'];
    }
}

function reza_ervani_register_category_grid_widget() {
    register_widget(Reza_Ervani_Category_Grid::class);
}
add_action('widgets_init', 'reza_ervani_register_category_grid_widget');

function reza_ervani_migrate_category_grid_widget() {
    $migration = 'reza_ervani_category_grid_widget_1_2_32';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $category = get_theme_mod('reza_ervani_show_home_questions', true) && get_theme_mod('reza_ervani_editorial_home', true)
        ? get_category_by_slug('tanya-jawab')
        : false;
    $area = Reza_Ervani_Category_Grid::HOME_AREA;
    $sidebars = (array) get_option('sidebars_widgets', array());
    $base = Reza_Ervani_Category_Grid::ID_BASE;
    if ($category && !preg_grep('/^' . $base . '-\d+$/D', $sidebars[$area] ?? array())) {
        $option = 'widget_' . $base;
        $instances = (array) get_option($option, array());
        $initial = array('category' => (int) $category->term_id, 'scope' => 'child_cards', 'columns' => 7, 'articles' => 3);
        // Reuse the saved instance if a previous request stopped before assigning it.
        $number = array_search($initial, $instances, true);
        if ($number === false) {
            $number = 2;
            while (isset($instances[$number])) {
                $number++;
            }
        }
        $instances[$number] = $initial;
        $instances['_multiwidget'] = 1;
        if (!update_option($option, $instances) && get_option($option) !== $instances) {
            return;
        }

        $sidebars[$area][] = $base . '-' . $number;
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        wp_set_sidebars_widgets($sidebars);
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option($migration, 1, false);
}
// Register the migrated widget instance on the same request.
add_action('widgets_init', 'reza_ervani_migrate_category_grid_widget', 95);
