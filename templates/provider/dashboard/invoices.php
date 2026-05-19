<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_provider_id = get_current_user_id();

// Dynamic WP Query to fetch provider appointments for invoices
$args = [
    'post_type'      => 'cosy_appointment',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => [
        [
            'key'     => 'cosy_provider_id',
            'value'   => $current_provider_id,
            'compare' => '='
        ]
    ],
    'orderby'        => 'date',
    'order'          => 'DESC'
];

$appointments_query = new WP_Query($args);
$appointments = $appointments_query->posts;
?>

<div class="card cosy-invoices-card mb-4 border-0" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(109, 46, 103, 0.04);">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-invoice-dollar" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0 fw-bold" style="color: #1e293b; font-family: 'Outfit', sans-serif;">Invoices</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px; font-size: 0.9rem;">Generate, view and manage your invoices below.</p>

        <!-- Invoice Table -->
        <style>
            #providerInvoicesTable,
            #providerInvoicesTable th, 
            #providerInvoicesTable td {
                border-left: 0 !important;
                border-right: 0 !important;
            }
        </style>
        <div class="table-responsive">
            <table class="table align-middle" id="providerInvoicesTable">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                        <th class="pb-3">#Invoice ID</th>
                        <th class="pb-3">Customer</th>
                        <th class="pb-3">Service</th>
                        <th class="pb-3">Date</th>
                        <th class="pb-3">Amount</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)) : ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-file-invoice fa-3x" style="color: #cbd5e1;"></i>
                                </div>
                                <h6 class="fw-bold mb-1">No Invoices Found</h6>
                                <p class="small text-muted mb-0">Completed bookings will automatically generate invoices here.</p>
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
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td class="fw-bold text-dark">INV-<?php echo $appt_id; ?></td>
                                <td class="fw-semibold text-slate" style="color: #334155;"><?php echo esc_html($customer_name); ?></td>
                                <td><span class="badge bg-light text-dark border-0 px-3 py-2 rounded-3" style="font-weight: 500; font-size: 0.8rem; color: #475569 !important;"><?php echo esc_html($service_name); ?></span></td>
                                <td style="font-size: 0.85rem; color: #64748b;"><?php echo esc_html($start_date); ?></td>
                                <td class="fw-bold text-slate" style="color: #334155;">£<?php echo esc_html($total_payable); ?></td>
                                <td>
                                    <span class="badge" style="background: #e6fcf5; color: #0ca678; padding: 6px 12px; border-radius: 20px; font-weight: 600;">
                                        <i class="fas fa-check-circle me-1"></i> <?php echo esc_html($payment_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn-action btn-view-invoice bg-light text-secondary" 
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
                                                style="width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                                title="View Invoice">
                                            <i class="fas fa-eye" style="font-size: 0.8rem;"></i>
                                        </button>
                                        <button class="btn-action btn-download-invoice bg-light text-secondary" 
                                                data-id="<?php echo $appt_id; ?>"
                                                data-customer="<?php echo esc_attr($customer_name); ?>"
                                                data-service="<?php echo esc_attr($service_name); ?>"
                                                data-total="<?php echo esc_attr($total_payable); ?>"
                                                style="width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" 
                                                title="Download PDF">
                                            <i class="fas fa-download" style="font-size: 0.8rem;"></i>
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

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cosy-modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header cosy-modal-header text-white p-4" style="background: linear-gradient(135deg, #a44390 0%, #6d2e67 100%); border-top-left-radius: 20px; border-top-right-radius: 20px;">
                <h5 class="modal-title fw-bold text-white" id="modalInvoiceTitle" style="font-family: 'Outfit', sans-serif; color: #ffffff !important;">Invoice Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4" style="background-color: #fdf2fb; border: 1px solid #f1e4ef;">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif;"><i class="fas fa-user text-primary" style="color: #a44390 !important;"></i> Customer Info</h6>
                            <p class="mb-1 fw-bold text-slate" id="modalInvCustomerName" style="color: #1e293b;"></p>
                            <p class="mb-0 text-muted small" id="modalInvCustomerEmail"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 bg-light" style="border: 1px solid #e2e8f0;">
                            <h6 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif;"><i class="fas fa-info-circle text-primary" style="color: #a44390 !important;"></i> Billing Details</h6>
                            <p class="mb-1 fw-bold text-slate" id="modalInvServiceName" style="color: #1e293b;"></p>
                            <p class="mb-1 text-muted small" id="modalInvDate" style="font-size: 0.8rem;"></p>
                            <p class="mb-0 fw-bold text-primary" id="modalInvCostInfo" style="color: #a44390 !important; font-size: 1.1rem;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button class="btn btn-primary w-100 rounded-4 py-3 fw-bold" id="btnDownloadInvoicePdf" style="background: linear-gradient(135deg, #a44390, #6d2e67); border: none; box-shadow: 0 4px 12px rgba(164, 67, 144, 0.2);">Download PDF Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 1. Populate dynamic invoice details modal
    $('.btn-view-invoice').on('click', function() {
        const id = $(this).data('id');
        const customer = $(this).data('customer');
        const email = $(this).data('email');
        const service = $(this).data('service');
        const start = $(this).data('start');
        const total = $(this).data('total');

        $('#modalInvoiceTitle').text('Invoice Details - INV-' + id);
        $('#modalInvCustomerName').text(customer);
        $('#modalInvCustomerEmail').text(email);
        $('#modalInvServiceName').text('Service: ' + service);
        $('#modalInvDate').text('Billing Date: ' + start);
        $('#modalInvCostInfo').text('Amount Received: £' + total);
        
        // Save ID on download button
        $('#btnDownloadInvoicePdf').data('id', id).data('customer', customer).data('service', service).data('total', total);
    });

    // 2. Download invoice function helper
    $(document).on('click', '.btn-download-invoice, #btnDownloadInvoicePdf', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const customer = $(this).data('customer');
        const service = $(this).data('service');
        const total = $(this).data('total');

        let invoiceDoc = `COSY APPOINTMENTS INVOICE RECEIPT\n`;
        invoiceDoc += `===================================\n`;
        invoiceDoc += `Invoice Reference: INV-${id}\n`;
        invoiceDoc += `Customer Name: ${customer}\n`;
        invoiceDoc += `Service: ${service}\n`;
        invoiceDoc += `Amount Paid: £${total}\n`;
        invoiceDoc += `Status: PAID / SECURED\n`;
        invoiceDoc += `Payment Method: Worldpay FIS\n`;
        invoiceDoc += `===================================\n`;
        invoiceDoc += `Thank you for your business!`;

        const element = document.createElement("a");
        const file = new Blob([invoiceDoc], {type: 'text/plain'});
        element.href = URL.createObjectURL(file);
        element.download = "invoice_INV_" + id + ".txt";
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    });
});
</script>