<?php

const REZA_ERVANI_PALESTINA_TOPICS_INITIALIZED = 'reza_ervani_migrate_palestina_topics_1_2_53';

function reza_ervani_update_palestina_topic_blocks($blocks, $widget_ids) {
    foreach ($blocks as &$block) {
        if ($block['blockName'] === 'core/legacy-widget') {
            $widget_id = $block['attrs']['id'] ?? '';
            if (isset($widget_ids[$widget_id])) {
                $block['attrs']['id'] = $widget_ids[$widget_id];
            }
            if (($block['attrs']['idBase'] ?? '') === 'reza_home_topics') {
                $block['attrs']['idBase'] = Reza_Ervani_Palestina_Topics::ID_BASE;
            }
        }

        if ($block['innerBlocks']) {
            $block['innerBlocks'] = reza_ervani_update_palestina_topic_blocks($block['innerBlocks'], $widget_ids);
        }
    }
    return $blocks;
}

function reza_ervani_migrate_palestina_topic_catalog() {
    $migration = 'reza_ervani_palestina_topic_catalog_2_0_0';
    if (get_option($migration) || is_customize_preview()) {
        return;
    }

    $base = Reza_Ervani_Palestina_Topics::ID_BASE;
    $old_instances = (array) get_option('widget_reza_home_topics', array());
    $option = 'widget_' . $base;
    $instances = (array) get_option($option, array());
    $before = $instances;
    $widget_ids = array();
    $already_copied = array();
    foreach ($instances as $number => $instance) {
        if (isset($instance['legacy_reza_home_topics_id'])) {
            $already_copied[$instance['legacy_reza_home_topics_id']] = $number;
        }
    }

    $next = 2;
    foreach ($old_instances as $number => $instance) {
        if (!is_int($number) || !is_array($instance)) {
            continue;
        }

        $destination = $already_copied[$number] ?? $number;
        if (!isset($already_copied[$number]) && isset($instances[$destination])) {
            while (isset($instances[$next]) || isset($old_instances[$next])) {
                $next++;
            }
            $destination = $next++;
        }

        $instances[$destination] = $instances[$destination] ?? ($instance + array('legacy_reza_home_topics_id' => $number));
        $widget_ids['reza_home_topics-' . $number] = $base . '-' . $destination;
    }

    $sidebars = (array) get_option('sidebars_widgets', array());
    $original_sidebars = $sidebars;
    foreach ($sidebars as &$ids) {
        if (is_array($ids)) {
            foreach ($ids as &$id) {
                $id = $widget_ids[$id] ?? $id;
            }
            unset($id);
        }
    }
    unset($ids);
    $moved_placements = $sidebars !== $original_sidebars;

    $area = Reza_Ervani_Palestina_Topics::HOME_AREA;
    if (!get_option(REZA_ERVANI_PALESTINA_TOPICS_INITIALIZED) && empty($sidebars[$area])) {
        $defaults = array_fill_keys(array_keys(Reza_Ervani_Palestina_Topics::topics()), Reza_Ervani_Palestina_Topics::DEFAULT_COUNT);
        $number = array_search($defaults, $instances, true);
        if ($number === false) {
            while (isset($instances[$next])) {
                $next++;
            }
            $number = $next;
            $instances[$number] = $defaults;
        }
        $sidebars[$area] = array($base . '-' . $number);
    }

    $blocks = (array) get_option('widget_block', array());
    $original_blocks = array();
    foreach ($blocks as $number => &$instance) {
        $content = $instance['content'] ?? '';
        if (!str_contains($content, 'reza_home_topics')) {
            continue;
        }
        $parsed = parse_blocks($content);
        $updated = reza_ervani_update_palestina_topic_blocks($parsed, $widget_ids);
        if ($updated !== $parsed) {
            $original_blocks[$number] = $instance;
            $instance['content'] = serialize_blocks($updated);
        }
    }
    unset($instance);

    if ($moved_placements || $original_blocks) {
        $backup_option = 'reza_ervani_palestina_topic_catalog_backup';
        $backup = array(
            'sidebars_widgets' => $original_sidebars,
            'widget_block' => $original_blocks,
        );
        if (!get_option($backup_option) && !add_option($backup_option, $backup, '', false) && !get_option($backup_option)) {
            return;
        }
    }

    // Keep the old option for rollback. Save new instances before replacing placement IDs.
    if ($instances !== $before) {
        $instances['_multiwidget'] = 1;
        if (!update_option($option, $instances) && get_option($option) !== $instances) {
            return;
        }
    }
    if ($original_blocks && !update_option('widget_block', $blocks) && get_option('widget_block') !== $blocks) {
        return;
    }
    if ($sidebars !== $original_sidebars) {
        $sidebars['array_version'] = $sidebars['array_version'] ?? 3;
        wp_set_sidebars_widgets($sidebars);
        if (get_option('sidebars_widgets') !== $sidebars) {
            return;
        }
    }

    update_option(REZA_ERVANI_PALESTINA_TOPICS_INITIALIZED, 1, false);
    update_option($migration, 1, false);
}
add_action('widgets_init', 'reza_ervani_migrate_palestina_topic_catalog', 95);
