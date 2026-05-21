<?php

namespace Cosy\Appointments;

class Loader
{
    protected array $actions = [];
    protected array $filters = [];

    /**
     * Registers a WordPress action hook.
     * Stores the hook details in an array to be executed later when run() is called.
     */
    public function add_action(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->actions[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
    }

    /**
     * Registers a WordPress filter hook.
     * Stores the filter details in an array to be executed later when run() is called.
     */
    public function add_filter(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->filters[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
    }

    /**
     * Executes all the registered actions and filters.
     * This loops through the stored arrays and actually hooks them into WordPress.
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
