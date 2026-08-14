<?php

namespace Cosy\Appointments\Rest;

use Cosy\Appointments\Loader;
use Cosy\Appointments\Rest\ProviderServices;

class Routes
{
    /**
     * REGISTERS REST ROUTE HOOKS
     * 
     * USE CASE:
     * Hooks custom plugin REST API routes into WordPress during rest_api_init.
     * 
     * HOW TO USE:
     * (new Routes())->register($loader);
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Attaches 'register_routes' callback to WordPress 'rest_api_init' action hook via Loader instance.
     * 
     * @param Loader $loader Plugin loader instance.
     */
    public function register(Loader $loader): void
    {
        $loader->add_action('rest_api_init', $this, 'register_routes');
    }

    /**
     * REGISTERS PLUGIN REST ENDPOINTS
     * 
     * USE CASE:
     * Exposes secure endpoints under /wp-json/cosy/v1 for provider service management.
     * 
     * HOW TO USE:
     * Automatically triggered on rest_api_init.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Registers GET /cosy/v1/provider-services/get to query provider services.
     * 2. Registers POST /cosy/v1/provider-services/update to save/update services.
     * 3. Registers POST /cosy/v1/provider-services/delete to delete services.
     * 4. Registers GET /cosy/v1/provider-services/get-one to fetch single service.
     * 5. Attaches permission_callback to check_provider_permission() for authorization.
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
     * VERIFIES PROVIDER REST PERMISSIONS
     * 
     * USE CASE:
     * Validates that an incoming REST API request originates from an authenticated provider user.
     * 
     * HOW TO USE:
     * Internal callback for REST endpoint permission_callback parameters.
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Checks if user is logged in.
     * 2. Verifies current user possesses 'provider' role.
     * 3. Validates X-WP-Nonce header for REST security.
     * 
     * @param \WP_REST_Request $request Incoming REST request object.
     * @return bool                     True if authorized, false otherwise.
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
