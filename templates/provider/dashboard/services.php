<div class="card cosy-services-card mb-4">
    <div class="card-body p-0">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-badge"
                style="background: rgba(164, 67, 144, 0.1); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-tools" style="color: #a44390; font-size: 1.2rem;"></i>
            </div>
            <h3 class="mb-0" style="margin-bottom: 0 !important;">My Services</h3>
        </div>
        <p class="text-muted mb-4" style="margin-left: 58px;">Manage your offerings, pricing, and session durations
            below.</p>

        <!-- Service Checkbox List -->
        <?php
        $services = $this->get_all_services();
        $checked_services = $this->get_checked_services();

        if (!empty($services)): ?>
            <div class="service-checkbox-container">
                <label class="form-label d-block mb-3 fw-bold text-dark">Select Services</label>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($services as $service): ?>
                        <div class="form-check">
                            <input class="form-check-input service-checkbox" type="checkbox"
                                value="<?php echo esc_attr($service['post_name']); ?>"
                                id="service-<?php echo esc_attr($service['ID']); ?>" <?php checked(in_array($service['ID'], $checked_services, true)); ?> data-action="select_service"
                                data-id="<?php echo esc_attr($service['ID']); ?>">
                            <label class="form-check-label fw-medium" for="service-<?php echo esc_attr($service['ID']); ?>">
                                <?php echo esc_html($service['title']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
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