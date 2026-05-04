<?php

namespace Cosy\Appointments\Rest;

use WP_REST_Request;
use Cosy\Appointments\Loader;

class ProviderServices
{

    //---------- Get service --------------//
    public static function get_service(WP_REST_Request $request)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'provider_services';

        $provider_id = get_current_user_id();
        $user = wp_get_current_user();

        if (!$provider_id || !in_array('provider', (array) $user->roles, true)) {
            return rest_ensure_response(['success' => false, 'message' => 'Unauthorized access']);
        }

        // Fetch all saved services for this provider
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT service_id, service, description, duration, price, checkbox_status 
             FROM $table 
             WHERE provider_id = %d",
                $provider_id
            ),
            ARRAY_A
        );

        if (!empty($rows)) {
            return rest_ensure_response($rows);
        } else {
            return rest_ensure_response([
                'success' => false,
                'message' => 'No services found'
            ]);
        }
    }

    //---------- Save service --------------//
    public static function save_service(WP_REST_Request $request)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'provider_services';

        $provider_id   = get_current_user_id();
        $user = wp_get_current_user();

        // ✅ Ensure only provider role can access
        if (!in_array('provider', (array) $user->roles, true)) {
            return rest_ensure_response(['success' => false, 'message' => 'Unauthorized']);
        }

        $service_id    = intval($request->get_param('service_id'));
        $service       = trim(preg_replace('/\s+/', ' ', sanitize_text_field($request->get_param('serviceTitle'))));
        $description   = sanitize_text_field($request->get_param('description')) ? sanitize_text_field($request->get_param('description')) : null;
        $duration      = sanitize_text_field($request->get_param('duration'));
        $price = $request->get_param('price') !== null ? floatval($request->get_param('price')) : null;
        $checked = $request->get_param('checked') === 'yes' ? 'yes' : 'no';
        $provider_name = $user->display_name;

        if (empty($service_id) || empty($service)) {
            return rest_ensure_response(['success' => false, 'message' => 'Invalid input']);
        }

        $message = '';
        $status  = false;

        // ✅ Check if record exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table WHERE service_id = %d AND provider_id = %d",
                $service_id,
                $provider_id
            )
        );

        if (!$exists) {
            // ✅ Insert new record
            $inserted = $wpdb->insert(
                $table,
                [
                    'service_id'      => $service_id,
                    'service'         => $service,
                    'description'     => $description,
                    'provider_id'     => $provider_id,
                    'provider'        => $provider_name,
                    'duration'        => $duration,
                    'price'           => $price,
                    'checkbox_status' => $checked,
                    'created_at'      => current_time('mysql'),
                    'updated_at'      => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
            );

            if ($inserted) {
                $status  = true;
                $message = 'Service inserted successfully';
            } else {
                $message = 'Failed to insert service';
            }
        } else {
            // ✅ Decide update fields
            $update_data = [
                'checkbox_status' => $checked,
                'service'         => $service,
                'updated_at'      => current_time('mysql')
            ];
            $update_format = ['%s', '%s'];

            // Agar title/description/duration/price request me aaye hain to hi update karo
            if (!empty($service)) {
                $update_data['service'] = $service;
                $update_format[] = '%s';
            }
            if (!empty($description)) {
                $update_data['description'] = $description;
                $update_format[] = '%s';
            }
            if (!empty($duration)) {
                $update_data['duration'] = $duration;
                $update_format[] = '%s';
            }

            if ($price !== '' && $price !== null) {
                $update_data['price'] = $price;
                $update_format[] = '%s';
            }

            // ✅ Update existing record
            $updated = $wpdb->update(
                $table,
                $update_data,
                [
                    'service_id'  => $service_id,
                    'provider_id' => $provider_id
                ],
                $update_format,
                ['%d', '%d']
            );

            if ($updated !== false) {
                $status  = true;
                $message = $updated ? 'Service updated successfully' : 'No changes made';
            } else {
                $message = 'Failed to update service';
            }
        }

        return rest_ensure_response([
            'success'         => $status,
            'message'         => $message,
            'service_id'      => $service_id,
            'service'         => $service,
            'description'     => $description,
            'duration'        => $duration,
            'price'           => $price,
            'checkbox_status' => $checked
        ]);
    }


    //---------- Delete service --------------//
    public static function  delete_service(WP_REST_Request $request)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'provider_services';

        $provider_id = get_current_user_id();
        $user = wp_get_current_user();
        $service_id  = intval($request->get_param('service_id'));

        // ✅ Only provider role allowed
        if (!in_array('provider', (array) $user->roles, true)) {
            return rest_ensure_response(['success' => false, 'message' => 'Unauthorized']);
        }

        if (!$service_id) {
            return rest_ensure_response(['success' => false, 'message' => 'Invalid service ID']);
        }

        $deleted = $wpdb->delete(
            $table,
            [
                'service_id'  => $service_id,
                'provider_id' => $provider_id
            ],
            ['%d', '%d']
        );

        if ($deleted) {
            return rest_ensure_response([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        } else {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Failed to delete service'
            ]);
        }
    }

    //---------- Get service by id --------------//
    public static function get_service_by_id(WP_REST_Request $request)
    {

        global $wpdb;
        $table = $wpdb->prefix . 'provider_services';

        $provider_id = get_current_user_id();
        $user = wp_get_current_user();
        $service_id  = intval($request->get_param('service_id'));

        // ✅ Ensure user is logged in and has provider role
        if (!$provider_id || !in_array('provider', (array) $user->roles, true)) {
            return rest_ensure_response(['success' => false, 'message' => 'Unauthorized access']);
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT service_id, service, description, duration, price, checkbox_status 
             FROM $table 
             WHERE provider_id = %d AND service_id = %d",
                $provider_id,
                $service_id
            ),
            ARRAY_A
        );

        if ($row) {
            return rest_ensure_response($row);
        } else {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Service not found'
            ]);
        }
    }
}
