<?php

namespace Cosy\Appointments\Rest;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Rest\ProviderServices;

class Routes
{
    public function register(Loader $loader): void
    {
        $loader->add_action('rest_api_init', $this, 'register_routes');
    }

    /**
     * Registers custom REST API endpoints for the plugin.
     * These endpoints allow the frontend JavaScript to communicate with the server
     * (e.g., getting, saving, or deleting services) securely.
     */
    public function register_routes(): void
    {
        /**
         * Endpoint: GET /cosy/v1/provider-services/get
         * Fetches all the services that belong to the logged-in provider.
         */
        register_rest_route('cosy/v1', '/provider-services/get', [
            'methods'  => 'GET',
            'callback' => [ProviderServices::class, 'get_service'],
            'permission_callback' => function ($request) {
                return $this->check_provider_permission($request);
            }
        ]);

        /**
         * Endpoint: POST /cosy/v1/provider-services/update
         * Saves or updates a service for the logged-in provider.
         */
        register_rest_route('cosy/v1', '/provider-services/update', [
            'methods'  => 'POST',
            'callback' => [ProviderServices::class, 'save_service'],
            'permission_callback' => function ($request) {
                return $this->check_provider_permission($request);
            }
        ]);

        /**
         * Endpoint: POST /cosy/v1/provider-services/delete
         * Deletes a specific service belonging to the logged-in provider.
         */
        register_rest_route('cosy/v1', '/provider-services/delete', [
            'methods'  => 'POST',
            'callback' => [ProviderServices::class, 'delete_service'],
            'permission_callback' => function ($request) {
                return $this->check_provider_permission($request);
            }
        ]);

        /**
         * Endpoint: GET /cosy/v1/provider-services/get-one
         * Fetches details of a single specific service using its ID.
         */
        register_rest_route('cosy/v1', '/provider-services/get-one', [
            'methods' => 'GET',
            'callback' => [ProviderServices::class, 'get_service_by_id'],
            'args' => [
                'service_id' => [
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param) && $param > 0;
                    }
                ]
            ],
            'permission_callback' => function ($request) {
                return $this->check_provider_permission($request);
            }
        ]);
    }

    /**
     * Centralized permission check
     */
    private function check_provider_permission($request): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();

        // Only allow provider role
        if (!in_array('provider', (array) $user->roles, true)) {
            return false;
        }

        // Verify REST nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return false;
        }

        return true;
    }
}
