<style>
.cosy-services-card {
    background: #ffffff;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    border: none !important;
    padding: 30px;
}

.cosy-services-card h3 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 1.75rem;
    color: #1e293b;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.cosy-services-card .service-checkbox-container {
    background: #f8fafc;
    padding: 20px;
    border-radius: 15px;
    border: 1px solid #e2e8f0;
    margin-bottom: 30px;
}

.cosy-services-card .form-check-input:checked {
    background-color: #a44390 !important;
    border-color: #a44390 !important;
}

.cosy-services-card .table {
    border-collapse: separate;
    border-spacing: 0 10px;
    border: none !important;
}

.cosy-services-card .table thead th {
    background: #f1f5f9 !important;
    color: #475569 !important;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 15px !important;
    border: none !important;
}

.cosy-services-card .table tbody tr {
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.cosy-services-card .table tbody td {
    background: #fff !important;
    padding: 15px !important;
    border-top: 1px solid #f1f5f9 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}

.cosy-services-card .table tbody td:first-child {
    border-left: 1px solid #f1f5f9 !important;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.cosy-services-card .table tbody td:last-child {
    border-right: 1px solid #f1f5f9 !important;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.cosy-services-card .form-control,
.cosy-services-card .form-select {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important;
    background-color: #f8fafc !important;
    font-size: 0.9rem;
    padding: 8px 12px !important;
}

.cosy-services-card .form-control:focus,
.cosy-services-card .form-select:focus {
    border-color: #a44390 !important;
    background-color: #fff !important;
    box-shadow: 0 0 0 3px rgba(164, 67, 144, 0.1) !important;
}

.cosy-services-card .btn-success {
    background: #a44390 !important;
    border: none !important;
    border-radius: 10px !important;
    width: 35px;
    height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.cosy-services-card .btn-danger {
    background: #fee2e2 !important;
    color: #ef4444 !important;
    border: none !important;
    border-radius: 10px !important;
    width: 35px;
    height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>

<div class="card cosy-services-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge" style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-tools" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0" style="margin-bottom: 0 !important;">My Services</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Manage your offerings, pricing, and session durations below.</p>

        <!-- Service Checkbox List -->
        <?php
        $services = $this->get_all_services();
        $checked_services = $this->get_checked_services();

        if (!empty($services)) : ?>
            <div class="service-checkbox-container">
                <label class="form-label d-block mb-3 fw-bold text-dark">Select Services</label>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($services as $service) : ?>
                        <div class="form-check">
                            <input
                                class="form-check-input service-checkbox"
                                type="checkbox"
                                value="<?php echo esc_attr($service['post_name']); ?>"
                                id="service-<?php echo esc_attr($service['ID']); ?>"
                                <?php checked(in_array($service['ID'], $checked_services, true)); ?>
                                data-action="select_service"
                                data-id="<?php echo esc_attr($service['ID']); ?>">
                            <label class="form-check-label fw-medium" for="service-<?php echo esc_attr($service['ID']); ?>">
                                <?php echo esc_html($service['title']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="alert alert-warning rounded-4 border-0">No services found in the system.</div>
        <?php endif; ?>

        <!-- Services Table -->
        <form id="servicesForm">
            <div class="cosy-message"></div>
            <div class="table-responsive">
                <table class="table align-middle" id="servicesTable">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="cc__service-body">
                        <!-- Dynamic rows will appear here -->
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>