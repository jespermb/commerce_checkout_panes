/**
 * @file
 * Attach behaviors to ajax configuration for commerce checkout panes.
 */

(function ($, Drupal) {
    Drupal.behaviors.commerceCheckoutPanesConfiguration = {
        attach: function (context, settings) {
            if ($('select[name="configuration[panes][review_order_display][configuration][view]"]', context).size() > 0) {
                Drupal.attachBehaviors();
            }
        }
    };

})(jQuery, Drupal);
