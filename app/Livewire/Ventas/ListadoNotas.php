<?php

namespace App\Livewire\Ventas;

use App\Models\Sale;
use Livewire\Component;

class ListadoNotas extends Component
{
    public string $fecha = '';

    public function mount(): void
    {
        if ($this->fecha === '') {
            $this->fecha = now()->format('Y-m-d');
        }
    }

    public function getSalesProperty()
    {
        return Sale::query()
            ->with('client')
            ->whereDate('created_at', $this->fecha)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTotalDiaProperty(): float
    {
        return (float) Sale::query()
            ->whereDate('created_at', $this->fecha)
            ->sum('total');
    }

    public function getCantidadVentasProperty(): int
    {
        return Sale::query()
            ->whereDate('created_at', $this->fecha)
            ->count();
    }

    public function render()
    {
        return view('livewire.ventas.listado-notas');
    }
}
