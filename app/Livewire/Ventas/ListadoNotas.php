<?php

namespace App\Livewire\Ventas;

use App\Models\Sale;
use Livewire\Component;

class ListadoNotas extends Component
{
    public string $fecha = '';
    public bool $showTicket = false;
    public ?Sale $ticketSale = null;

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

    public function reprint(int $saleId): void
    {
        $this->ticketSale = Sale::with(['client', 'saleItems.article'])->find($saleId);

        if ($this->ticketSale) {
            $this->showTicket = true;
        }
    }

    public function closeTicket(): void
    {
        $this->showTicket = false;
        $this->ticketSale = null;
    }

    public function render()
    {
        return view('livewire.ventas.listado-notas');
    }
}
