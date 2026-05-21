<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('cosy_render_popup')) {
    /**
     * Renders a standardized Bootstrap 5 modal/popup.
     * Keeps HTML DRY and easy to maintain.
     *
     * @param string $id            The HTML ID of the modal (e.g., 'addHolidayModal').
     * @param string $title_html    The title (can include HTML like icons).
     * @param string $body_html     The internal content of the modal body.
     * @param array  $options       Optional configs (max_width, z_index).
     * @return string               The complete HTML string for the modal.
     */
    function cosy_render_popup($id, $title_html, $body_html, $options = [])
    {
        $max_width    = isset($options['max_width']) ? $options['max_width'] : '480px';
        $z_index      = isset($options['z_index']) ? $options['z_index'] : '99999';
        $dialog_class = isset($options['dialog_class']) ? $options['dialog_class'] : '';
        $header_class = isset($options['header_class']) ? $options['header_class'] : '';
        $footer_html  = isset($options['footer_html']) ? $options['footer_html'] : '';

        ob_start();
        ?>
        <div class="modal fade" id="<?php echo esc_attr($id); ?>" tabindex="-1" aria-hidden="true" style="z-index: <?php echo esc_attr($z_index); ?>;">
            <div class="modal-dialog modal-dialog-centered <?php echo esc_attr($dialog_class); ?>" style="max-width: <?php echo esc_attr($max_width); ?>;">
                <div class="modal-content cosy-modal-content border-0 shadow-lg">

                    <!-- Modal Header -->
                    <div class="modal-header cosy-modal-header <?php echo esc_attr($header_class); ?>">
                        <h5 class="modal-title fw-bold text-white mb-0" id="<?php echo esc_attr($id); ?>Label">
                            <?php echo $title_html; ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="cosy-modal-body p-4">
                        <?php echo $body_html; ?>
                    </div>
                    
                    <?php if (!empty($footer_html)) : ?>
                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 p-4 pt-0 justify-content-end gap-2" id="<?php echo esc_attr($id); ?>Footer">
                        <?php echo $footer_html; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
