<?php

namespace Cosy\Appointments\Common;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EmailTemplates (Backward Compatibility Proxy)
 * 
 * Provides 100% backward-compatibility for all legacy references across the plugin.
 * Extends the centralized \Cosy\Appointments\Email\EmailTemplates class.
 */
class EmailTemplates extends \Cosy\Appointments\Email\EmailTemplates
{
    // Inherits all dynamic template methods, protected tables, and send() dispatcher.
}
