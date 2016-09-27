<?php

namespace Drupal\commerce_checkout_panes\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\views\Views;

/**
 * Base pane for handling order displays during checkout.
 */
class BaseOrderDisplay extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view' => NULL,
      'display' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $view = $this->configuration['view'];
    $summary = '';
    if (!empty($view)) {
      $display = $this->configuration['views_display'];
      $summary = $this->t('View selected: @view => @display', ['@view' => $view, '@display' => $display]) . '<br/>';
    }
    else {
      $summary = $this->t('No view displayed.') . '<br/>';
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $views = Views::getEnabledViews();
    $view_options = [];
    foreach ($views as $view_key => $view) {
      $view_options[$view_key] = $view->label();
    }
    $form['view'] = [
      '#type' => 'select',
      '#title' => $this->t('Select view'),
      '#default_value' => ($this->configuration['view']) ? $this->configuration['view'] : reset($view_options),
      '#options' => $view_options,
      '#attached' => [
        'library' => [
          'drupal.states' => 'drupal.states',
        ],
      ],
    ];
    $default_display = $this->configuration['views_display'];
    $pane_id = $this->getId();
    ksm($pane_id);
    foreach ($view_options as $view_name => $view_label) {
      $display_options = $this->getViewDisplayOptions($view_name);
      $default = (strpos($default_display, $view_name) === 0) ?
        str_replace($default_display, $view . '_', '') :
        current($display_options);
      $form['views_display_' . $view_name] = [
        '#type' => 'select',
        '#title' => $this->t('Select display'),
        '#default_value' => $default_display,
        '#options' => $display_options,
        '#states' => [
          'visible' => [
            "select[name='configuration[panes][" . $pane_id . "][configuration][view]']" => ['value' => $view_name],
          ],
        ],
      ];
    }
    return $form;
  }

  /**
   * Get display options for a view.
   *
   * @param string $view_name
   *   The name of the view to find displays for.
   *
   * @return array
   *   An array of view displays.
   */
  protected function getViewDisplayOptions($view_name) {
    $view = Views::getView($view_name);
    $displays = $view->storage->get('display');
    $display_options = [];
    foreach ($displays as $display_key => $display) {
      $display_options[$display_key] = $display['display_title'];
    }
    return $display_options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['view'] = $values['view'];
      $this->configuration['views_display'] = $values['views_display_' . $values['view']];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $view_name = $this->configuration['view'];
    $display = $this->configuration['views_display'];
    $view = Views::getView($view_name);
    $view->setDisplay($display);
    $view->setArguments(array($this->order->id()));
    $pane_form['review_pane'] = $view->render();
    return $pane_form;
  }

}
