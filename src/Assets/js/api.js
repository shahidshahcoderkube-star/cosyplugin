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
window.COSY_API = window.COSY_API || {};

(function () {
    'use strict';
    const rootUrl = (typeof cosy_ajax !== 'undefined' && cosy_ajax.root) 
        ? cosy_ajax.root 
        : (window.location.origin + '/wp-json/');

    window.COSY_API = {
        base: rootUrl + 'cosy/v1/',
        nonce: (typeof cosy_ajax !== 'undefined' && cosy_ajax.nonce) ? cosy_ajax.nonce : '',
        providerServices: {
            get: 'provider-services/get',
            update: 'provider-services/update',
            delete: 'provider-services/delete',
            getOne: 'provider-services/get-one'
        },
        /**
         * GET FULL ENDPOINT URL
         * 
         * USE CASE: Safely builds absolute REST API endpoint URL with query params.
         * HOW TO USE: window.COSY_API.getEndpoint('provider-services/get');
         */
        getEndpoint: function (route) {
            return (this.base || (rootUrl + 'cosy/v1/')) + route;
        }
    };
})();
