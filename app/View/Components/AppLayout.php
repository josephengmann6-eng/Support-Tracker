<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(public string $title = 'Dashboard', public ?string $subtitle = null)
    {
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
