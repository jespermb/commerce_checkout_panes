<?php

namespace Drupal\commerce_checkout_panes\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;

/**
 * Provides the completion message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "order_note",
 *   label = @Translation("Order note"),
 *   default_step = "order_information",
 * )
 */
class OrderNote extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'note_field' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $note = $this->configuration['note_field'];
    $summary = '';
    if (!empty($note)) {
      $summary = $this->t('Note field: @field_name', ['@field_name' => $note]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $entity_type_id = 'commerce_order';
    $bundle = 'default';
    $options = array();
    foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
      $options[$field_definition->getName()] = $field_definition->getLabel();
    }
    $form['note_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Select note field'),
      '#options' => $options,
      '#default_value' => $this->configuration['note_field'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['note_field'] = $values['note_field'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order = $this->order;
    $note_field = $this->configuration['note_field'];
    if ($order->hasField($note_field)) {
      $pane_form = [
        '#type' => 'textarea',
        '#default_value' => '',
        '#title' => $this->t('Add a note'),
        '#required' => FALSE,
      ];
      return $pane_form;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    if (!$form_state->isValueEmpty('order_note')) {
      $note_field = $this->configuration['note_field'];
      $value = $form_state->getValue('order_note');
      $this->order->set($note_field, $value);
    }
  }

}
