<?php
if (!defined('ABSPATH')) {
    exit;
}

$provider_id = isset($_GET['provider_id']) ? intval($_GET['provider_id']) : (isset($_COOKIE['cosy_selected_provider_id']) ? intval($_COOKIE['cosy_selected_provider_id']) : 0);
$availability = [];
$holiday_dates = [];

if ($provider_id > 0) {
    if (!isset($common) || !method_exists($common, 'get_provider_availability_data')) {
        $common = new class {
            use \Cosy\Appointments\Common\GlobalCommonFunctions;
        };
    }
    $avail_data    = $common->get_provider_availability_data($provider_id);
    $availability  = $avail_data['availability'];
    $holiday_dates = $avail_data['holiday_dates'];
}
?>
<script>
    window.providerAvailability = <?php echo wp_json_encode($availability); ?>;
    window.providerHolidays = <?php echo wp_json_encode($holiday_dates); ?>;
</script>

<div class="cosy-checkout-root" style="padding-top: 50px; padding-bottom: 50px;">
    <div class="cosy-checkout-container" id="cosyCheckoutContainer">
        <!-- Rendered dynamically by checkout.js -->
    </div>
</div>

<!-- Time Slot Selection Modal for Call Schedule Step -->
<div class="modal fade" id="timeSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="cosy-card-rounded modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="cosy-modal-icon-box" style="width: 42px; height: 42px; background: #fdf5fc; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #a44390;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #1e293b;"><?php esc_html_e('Select Call Start Time', 'cosy-appointments'); ?></h5>
                        <small class="text-muted fw-medium"><?php esc_html_e('10-minute slot blocks can be selected', 'cosy-appointments'); ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-4 mb-4 small fw-medium justify-content-center">
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #fff; border: 1.5px solid #edf2f7; border-radius: 3px;"></span>
                        <?php esc_html_e('Available', 'cosy-appointments'); ?>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #a44390; border-radius: 3px;"></span>
                        <?php esc_html_e('Selected', 'cosy-appointments'); ?>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span style="width: 12px; height: 12px; background: #e2e8f0; border-radius: 3px;"></span> <?php esc_html_e('Booked', 'cosy-appointments'); ?>
                    </span>
                </div>
                <!-- Time blocks generated dynamically -->
                <div id="timeGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(105px, 1fr)); gap: 10px;"></div>
            </div>
            <div class="modal-footer border-0 p-4 pt-2">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="text-start">
                        <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.72rem;"><?php esc_html_e('Total Duration', 'cosy-appointments'); ?></small>
                        <span id="modalTotalDuration" class="fw-bold" style="font-size: 1.1rem; color: #a44390;">0 <?php esc_html_e('minutes', 'cosy-appointments'); ?></span>
                    </div>
                    <button type="button" class="btn px-4 py-2 fw-bold text-white shadow-sm" id="btnConfirmTimeSlotsModal" style="background: #a44390; border-radius: 12px; font-size: 0.9rem;">
                        <?php esc_html_e('Confirm', 'cosy-appointments'); ?>
                    </button>
                </div>  
            </div>
        </div>
    </div>
</div>