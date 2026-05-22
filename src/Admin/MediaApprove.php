<?php

namespace Cosy\Appointments\Admin;

class MediaApprove
{
    public function render_media_approve(): void
    {
?>

        <div class="container-fluid mt-4">

            <h4 class="mb-3">📂 Pending Media Approvals</h4>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Media</th>
                            <th>Provider</th>
                            <!-- <th>Type</th> -->
                            <th>Uploaded On</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr data-id="1">
                            <!-- Media -->
                            <td style="max-width:220px;">
                                <video controls class="w-100" style="max-height:140px;">
                                    <source src="sample-video.mp4" type="video/mp4">
                                </video>
                            </td>
                            <!-- User -->
                            <td>
                                John Doe
                                <!-- <small class="text-muted">john@example.com</small> -->
                            </td>


                            <!-- Type -->
                            <!-- <td>Video</td> -->

                            <!-- Date -->
                            <td>08 Jan 2026</td>

                            <!-- Status -->
                            <td>
                                <span class="badge bg-warning text-dark status-badge">
                                    Pending
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <button class="btn btn-success btn-sm approve-media">
                                    Approve
                                </button>
                                <button class="btn btn-outline-danger btn-sm reject-media">
                                    Reject
                                </button>
                            </td>
                        </tr>

                        <!-- More rows dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

<?php
    }
}
