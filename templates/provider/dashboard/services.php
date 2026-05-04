<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h3 class="text-success">🛠 My Services</h3>
        <p class="text-muted">Select services and update price, duration and description.</p>

        <!-- Service Checkbox List -->
        <?php
        $services = $this->get_all_services();
        $checked_services = $this->get_checked_services();

        if (!empty($services)) : ?>
            <div class="mb-3">
                <label class="form-label">Select Services</label><br>

                <?php foreach ($services as $service) : ?>
                    <div class="form-check form-check-inline">
                        <input
                            class="form-check-input service-checkbox"
                            type="checkbox"
                            value="<?php echo esc_attr($service['post_name']); ?>"
                            id="service-<?php echo esc_attr($service['ID']); ?>"
                            <?php checked(in_array($service['ID'], $checked_services, true)); ?>
                            data-action="select_service"
                            data-id="<?php echo esc_attr($service['ID']); ?>">
                        <label class="form-check-label" for="service-<?php echo esc_attr($service['ID']); ?>">
                            <?php echo esc_html($service['title']); ?>
                        </label>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="alert alert-warning">No services found.</div>
        <?php endif; ?>
        <!-- <br> -->

        <!-- Services Table -->
        <form id="servicesForm">
            <div class="cosy-message"></div>
            <table class="table table-bordered align-middle" id="servicesTable">
                <thead class="table-success">
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
        </form>
    </div>
</div>