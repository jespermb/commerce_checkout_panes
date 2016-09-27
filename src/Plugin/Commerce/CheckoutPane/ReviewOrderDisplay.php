<?php

namespace Drupal\commerce_checkout_panes\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\views\Views;

/**
 * Provides the completion message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "review_order_display",
 *   label = @Translation("Review order display"),
 *   default_step = "review",
 * )
 */
class ReviewOrderDisplay extends BaseOrderDisplay implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $view_name = $this->configuration['view'];
    $display = $this->configuration['views_display'];
    $view = Views::getView($view_name);
    $view->setDisplay($display);
    $view->setArguments(array($this->order->id()));
    $pane_form['review_order'] = $view->render();
    return $pane_form;
  }

}
