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
                            $service_fee     = get_post_meta($appt_id, 'cosy_service_fee', true);
                            $total_payable   = get_post_meta($appt_id, 'cosy_total_payable', true);
                            $payment_status  = get_post_meta($appt_id, 'cosy_payment_status', true);
                            if (empty($payment_status)) {
                                $payment_status = 'Paid';
                            }
                        ?>
                            <tr class="invoice-table-row">
                                <td class="fw-bold text-dark"><?php esc_html_e('INV-', 'cosy-appointments'); ?><?php echo esc_html($appt_id); ?></td>
                                <td class="fw-semibold text-slate invoice-customer-name"><?php echo esc_html($customer_name); ?></td>
                                <td><span class="badge bg-light text-dark border-0 px-3 py-2 rounded-3 invoice-service-badge"><?php echo esc_html($service_name); ?></span></td>
                                <td class="invoice-date-cell"><?php echo esc_html($start_date); ?></td>
                                <td class="fw-bold text-slate invoice-amount-cell"><?php echo esc_html(cosy_get_currency_symbol()); ?><?php echo esc_html($total_payable); ?></td>
                                <td>
                                    <span class="badge invoice-status-paid">
                                        <i class="fas fa-check-circle me-1"></i> <?php echo esc_html($payment_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn-action btn-view-invoice bg-light text-secondary invoice-action-btn"
                                            data-id="<?php echo esc_attr($appt_id); ?>"
                                            data-customer="<?php echo esc_attr($customer_name); ?>"
                                            data-email="<?php echo esc_attr($customer_email); ?>"
                                            data-service="<?php echo esc_attr($service_name); ?>"
                                            data-start="<?php echo esc_attr($start_date); ?>"
                                            data-weekly="<?php echo esc_attr($weekly_booking); ?>"
                                            data-cost="<?php echo esc_attr($service_cost); ?>"
                                            data-fee="<?php echo esc_attr($service_fee); ?>"
                                            data-total="<?php echo esc_attr($total_payable); ?>"
                                            data-status="<?php echo esc_attr($payment_status); ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#invoiceModal"
                                            title="View Invoice">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-download-invoice bg-light text-secondary invoice-action-btn"
                                            data-id="<?php echo esc_attr($appt_id); ?>"
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
<div class="row g-3">
    <!-- Customer Info -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 modal-info-box-primary" style="background-color: var(--cosy-brand-lightest); border: 1px solid var(--cosy-brand-light);">
            <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-user modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Customer Info', 'cosy-appointments'); ?></h6>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark" style="font-size: 1.05rem; text-transform: capitalize;" id="modalInvCustomerName"></span>
                <span class="text-muted small" id="modalInvCustomerEmail"></span>
            </div>
        </div>
    </div>
    
    <!-- Billing Details -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 bg-light modal-info-box-secondary" style="border: 1px solid var(--cosy-border);">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-info-circle modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Billing Details', 'cosy-appointments'); ?></h6>
            <p class="mb-3 fw-bold text-slate" style="font-size: 1.1rem; color: var(--cosy-brand-dark);" id="modalInvServiceName"></p>
            
            <div class="d-flex flex-column gap-2" style="font-size: 0.88rem; color: #475569;">
                <div class="d-flex align-items-baseline gap-1">
                    <span class="text-muted" style="min-width: 100px; font-weight: 500;">Billing Date:</span>
                    <span id="modalInvDate" class="text-dark fw-semibold"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Statement -->
    <div class="col-md-12">
        <div class="p-3 rounded-4 border bg-white" style="border-color: var(--cosy-brand-light) !important;">
            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2 modal-info-title" style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 12px !important;"><i class="fas fa-receipt modal-icon-primary" style="color: var(--cosy-brand-purple) !important;"></i> <?php esc_html_e('Payment Summary', 'cosy-appointments'); ?></h6>
            <div class="d-flex justify-content-between mb-2 small text-muted" style="font-size: 0.88rem;">
                <span>Provider Share:</span>
                <strong id="modalInvProviderShare" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between mb-2 small text-muted" style="font-size: 0.88rem;">
                <span>Service Fee:</span>
                <strong id="modalInvServiceFee" class="text-dark fw-bold" style="font-size: 0.95rem;"></strong>
            </div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2" style="font-size: 0.95rem;">
                <span class="fw-bold text-dark">Amount Received:</span>
                <strong id="modalInvTotalPaid" style="color: var(--cosy-brand-purple); font-size: 1.15rem; font-weight: 800;"></strong>
            </div>
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