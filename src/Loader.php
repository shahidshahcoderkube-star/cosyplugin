<?php

namespace Cosy\Appointments;

class Loader
{
    protected array $actions = [];
    protected array $filters = [];

    //--------------- Add Action and Filter ----------------//
    public function add_action(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->actions[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
    }

    //--------------- Add Filter ----------------//
    public function add_filter(string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1): void
    {
        $this->filters[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
    }

    //--------------- Run all registered actions and filters ----------------//
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
