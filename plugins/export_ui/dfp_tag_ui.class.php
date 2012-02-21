<?php

/**
 * @file
 * A custom Ctools Export UI class for DART Tags.
 */

/**
 * Customizations of the DART Tags UI.
 */
class dfp_tag_ui extends ctools_export_ui {

  /**
   * Prepare the item object before the edit form is rendered.
   */
  function edit_form(&$form, &$form_state) {
    $form_state['item']->settings = unserialize($form_state['item']->settings);

    parent::edit_form($form, $form_state);
  }

  /**
   * Prepare the tag values before they are added to the database.
   */
  function edit_form_submit(&$form, &$form_state) {
    $settings = unserialize($form_state['item']->settings);

    // Remove empty targeting values before storing them in the database.
    foreach ($form_state['values']['targeting'] as $key => $target) {
      if (empty($target['target']) && empty($target['value'])) {
        unset($form_state['values']['targeting'][$key]);
      }
    }

    $settings['targeting'] = $form_state['values']['targeting'];
    $settings['slug'] = $form_state['values']['slug'];
    $settings['block'] = $form_state['values']['block'];
    $settings['scriptless'] = $form_state['values']['scriptless'];

    $form_state['values']['settings'] = serialize($settings);
    parent::edit_form_submit($form, $form_state);
  }

  /**
   * Build a row based on the item.
   *
   * By default all of the rows are placed into a table by the render
   * method, so this is building up a row suitable for theme('table').
   * This doesn't have to be true if you override both.
   */
  function list_build_row($item, &$form_state, $operations) {
    // Set up sorting
    $name = $item->{$this->plugin['export']['key']};
    $schema = ctools_export_get_schema($this->plugin['schema']);

    switch ($form_state['values']['order']) {
      case 'disabled':
        $this->sorts[$name] = empty($item->disabled) . $name;
        break;
      case 'name':
        $this->sorts[$name] = $name;
        break;
      case 'storage':
        $this->sorts[$name] = $item->{$schema['export']['export type string']} . $name;
        break;
    }

    $this->rows[$name]['data'] = array();
    $this->rows[$name]['class'] = !empty($item->disabled) ? array('ctools-export-ui-disabled') : array('ctools-export-ui-enabled');
    $this->rows[$name]['data'][] = array('data' => check_plain($item->name), 'class' => array('ctools-export-ui-name'));
    $this->rows[$name]['data'][] = array('data' => check_plain($item->size), 'class' => array('ctools-export-ui-size'));
    $this->rows[$name]['data'][] = array('data' => (check_plain($item->block) ? t('Yes') : t('No')), 'class' => array('ctools-export-ui-block'));
    $this->rows[$name]['data'][] = array('data' => check_plain($item->{$schema['export']['export type string']}), 'class' => array('ctools-export-ui-storage'));

    $ops = theme('links__ctools_dropbutton', array('links' => $operations, 'attributes' => array('class' => array('links', 'inline'))));

    $this->rows[$name]['data'][] = array('data' => $ops, 'class' => array('ctools-export-ui-operations'));
  }

  /**
   * Provide the table header.
   *
   * If you've added columns via list_build_row() but are still using a
   * table, override this method to set up the table header.
   */
  function list_table_header() {
    $header = array();

    $header[] = array('data' => t('Name'), 'class' => array('ctools-export-ui-name'));
    $header[] = array('data' => t('Size'), 'class' => array('ctools-export-ui-size'));
    $header[] = array('data' => t('Block'), 'class' => array('ctools-export-ui-block'));
    $header[] = array('data' => t('Storage'), 'class' => array('ctools-export-ui-storage'));
    $header[] = array('data' => t('Operations'), 'class' => array('ctools-export-ui-operations'));

    return $header;
  }

}