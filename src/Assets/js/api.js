/**
 * COSY APPOINTMENTS REST API CONFIGURATION MAP
 * 
 * USE CASE:
 * Centralizes REST API endpoint route definitions for provider services management.
 * 
 * HOW TO USE:
 * Referenced across JS files via window.COSY_API.providerServices.get / update / delete.
 * 
 * WHAT IT DOES INTERNALLY:
 * 1. Reads WP REST API root URL from cosy_ajax.root.
 * 2. Maps relative endpoint strings for provider service CRUD operations.
 */
window.COSY_API = {
    base: (typeof cosy_ajax !== 'undefined' && cosy_ajax.root ? cosy_ajax.root : '') + 'cosy/v1/',
    providerServices: {
        get: 'provider-services/get',
        update: 'provider-services/update',
        delete: 'provider-services/delete',
        getOne: 'provider-services/get-one'
    }
};
