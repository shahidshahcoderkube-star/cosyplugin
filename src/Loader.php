<?php

namespace Cosy\Appointments;

class Loader
{
    protected array $actions = [];
    protected array $filters = [];

    /**
     * REGISTERS WORDPRESS ACTION HOOK
     * 
     * USE CASE:
     * Used by plugin controllers to queue WordPress action hooks for registration.
     * 
     * HOW TO USE:
     * $loader->add_action('init', $this, 'my_callback_method');
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Stores hook name, target component instance, callback method, priority, and accepted args in $this->actions array.
     * 
     * @param string $hook          WordPress action hook name.
     * @param object $component     Target object instance.
     * @param string $callback      Callback method name.
     * @param int    $priority      Execution priority integer (default: 10).
     * @param int    $accepted_args Number of accepted arguments (default: 1).
     */
    public function add_action(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->actions[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
    }

    /**
     * REGISTERS WORDPRESS FILTER HOOK
     * 
     * USE CASE:
     * Used by plugin controllers to queue WordPress filter hooks for registration.
     * 
     * HOW TO USE:
     * $loader->add_filter('template_include', $this, 'my_filter_method');
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Stores filter details in $this->filters array.
     * 
     * @param string $hook          WordPress filter hook name.
     * @param object $component     Target object instance.
     * @param string $callback      Callback method name.
     * @param int    $priority      Execution priority integer (default: 10).
     * @param int    $accepted_args Number of accepted arguments (default: 1).
     */
    public function add_filter(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->filters[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
    }

    /**
     * EXECUTES ALL REGISTERED HOOKS
     * 
     * USE CASE:
     * Called at the end of plugin initialization to attach all queued hooks to WordPress core.
     * 
     * HOW TO USE:
     * $loader->run();
     * 
     * WHAT IT DOES INTERNALLY:
     * 1. Iterates over $this->actions array and calls WordPress add_action().
     * 2. Iterates over $this->filters array and calls WordPress add_filter().
     */
    public function run(): void
    {
        foreach ($this->actions as $a) {
            add_action($a['hook'], [$a['component'], $a['callback']], $a['priority'], $a['accepted_args']);
        }

        foreach ($this->filters as $f) {
            add_filter($f['hook'], [$f['component'], $f['callback']], $f['priority'], $f['accepted_args']);
        }
    }
}
