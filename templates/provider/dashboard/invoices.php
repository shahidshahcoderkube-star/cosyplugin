<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_provider_id = get_current_user_id();

// Fetch appointments using the centralized OOP method from the Dashboard class
$appointments = \Cosy\Appointments\Frontend\Dashboard::get_provider_appointments($current_provider_id);
?>

<div class="card cosy-invoices-card mb-4 border-0">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <h3 class="mb-0 fw-bold cosy-invoices-title"><?php esc_html_e('Invoices', 'cosy-appointments'); ?></h3>
        </div>
        <p class="text-muted mb-4 cosy-invoices-subtitle"><?php esc_html_e('Generate, view and manage your invoices below.', 'cosy-appointments'); ?></p>

        <!-- Invoice Table -->
        <div class="table-responsive">
            <table class="table align-middle" id="providerInvoicesTable">
                <thead>
                    <tr>
                        <th class="pb-3"><?php esc_html_e('#Invoice ID', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Customer', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Service', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Date', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Amount', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Status', 'cosy-appointments'); ?></th>
                        <th class="pb-3"><?php esc_html_e('Action', 'cosy-appointments'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)) : ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-file-invoice fa-3x invoice-empty-icon"></i>
                                </div>
                                <h6 class="fw-bold mb-1"><?php esc_html_e('No Invoices Found', 'cosy-appointments'); ?></h6>
                                <p class="small text-muted mb-0"><?php esc_html_e('Completed bookings will automatically generate invoices here.', 'cosy-appointments'); ?></p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($appointments as $appt) :
                            $appt_id = $appt->ID;
                            $customer_name   = get_post_meta($appt_id, 'cosy_customer_name', true);
                            $customer_email  = get_post_meta($appt_id, 'cosy_customer_email', true);
                            $service_name    = get_post_meta($appt_id, 'cosy_service_name', true);
                            $start_date      = get_post_meta($appt_id, 'cosy_start_date', true);
                            $end_date        = get_post_meta($appt_id, 'cosy_end_date', true);
                            $weekly_booking  = get_post_meta($appt_id, 'cosy_weekly_booking', true);
                            $number_of_weeks = get_post_meta($appt_id, 'cosy_number_of_weeks', true);
                            $number_of_slots = get_post_meta($appt_id, 'cosy_number_of_bookings', true);
                            $service_cost    = get_post_meta($appt_id, 'cosy_service_cost', true);
                            $total_payable   = get_post_meta($appt_id, 'cosy_total_payable', true);
                            $payment_status  = get_post_meta($appt_id, 'cosy_payment_status', true);
                            if (empty($payment_status)) {
                                $payment_status = 'Paid';
                            }
                        ?>
                            <tr class="invoice-table-row">
                                <td class="fw-bold text-dark">INV-<?php echo $appt_id; ?></td>
                                <td class="fw-semibold text-slate invoice-customer-name"><?php echo esc_html($customer_name); ?></td>
                                <td><span class="badge bg-light text-dark border-0 px-3 py-2 rounded-3 invoice-service-badge"><?php echo esc_html($service_name); ?></span></td>
                                <td class="invoice-date-cell"><?php echo esc_html($start_date); ?></td>
                                <td class="fw-bold text-slate invoice-amount-cell">£<?php echo esc_html($total_payable); ?></td>
                                <td>
                                    <span class="badge invoice-status-paid">
                                        <i class="fas fa-check-circle me-1"></i> <?php echo esc_html($payment_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn-action btn-view-invoice bg-light text-secondary invoice-action-btn"
                                            data-id="<?php echo $appt_id; ?>"
                                            data-customer="<?php echo esc_attr($customer_name); ?>"
                                            data-email="<?php echo esc_attr($customer_email); ?>"
                                            data-service="<?php echo esc_attr($service_name); ?>"
                                            data-start="<?php echo esc_attr($start_date); ?>"
                                            data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                                            data-cost="<?php echo esc_attr($service_cost); ?>"
                                            data-total="<?php echo esc_attr($total_payable); ?>"
                                            data-status="<?php echo esc_attr($payment_status); ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoiceModal"
                                            title="View Invoice">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-download-invoice bg-light text-secondary invoice-action-btn"
                                            data-id="<?php echo $appt_id; ?>"
                                            data-customer="<?php echo esc_attr($customer_name); ?>"
                                            data-service="<?php echo esc_attr($service_name); ?>"
                                            data-total="<?php echo esc_attr($total_payable); ?>"
                                            title="Download PDF">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
ob_start();
?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 invoice-modal-info-box-primary">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 invoice-modal-info-title"><i class="fas fa-user invoice-modal-icon-primary"></i> <?php esc_html_e('Customer Info', 'cosy-appointments'); ?></h6>
                            <p class="mb-1 fw-bold text-slate invoice-modal-customer-name" id="modalInvCustomerName"></p>
                            <p class="mb-0 text-muted small" id="modalInvCustomerEmail"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light invoice-modal-info-box-secondary">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 invoice-modal-info-title"><i class="fas fa-info-circle invoice-modal-icon-primary"></i> <?php esc_html_e('Billing Details', 'cosy-appointments'); ?></h6>
                            <p class="mb-1 fw-bold text-slate invoice-modal-customer-name" id="modalInvServiceName"></p>
                            <p class="mb-1 text-muted small invoice-modal-small-info" id="modalInvDate"></p>
                            <p class="mb-0 fw-bold invoice-modal-cost-info" id="modalInvCostInfo"></p>
                        </div>
                    </div>
                </div>
<?php
$modal_body = ob_get_clean();

$footer_html = '<button class="btn btn-primary w-100 rounded-4 py-3 fw-bold invoice-btn-modal-download" id="btnDownloadInvoicePdf">' . esc_html__('Download PDF Receipt', 'cosy-appointments') . '</button>';

echo cosy_render_popup(
    'invoiceModal',
    __('Invoice Details', 'cosy-appointments'),
    $modal_body,
    [
        'dialog_class' => 'modal-lg',
        'max_width'    => '',
        'header_class' => 'invoice-modal-header-gradient p-4 text-white',
        'footer_html'  => $footer_html
    ]
);
?>