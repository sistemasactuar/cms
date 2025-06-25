<?php

namespace App\Livewire;

use Livewire\Component;

class GraficasPlanoCartera extends Component
{
    public $data = [];

    public function mount($data)
    {
        $this->data = $data;
    }

    public function render()
    {
        return view('livewire.graficas-plano-cartera');
    }
}

