<?php

namespace App\Livewire\Ventas;

use App\Models\Article;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;

class PuntoDeVenta extends Component
{
    public int $step = 1;

    public string $search = '';

    /** @var array<int, array{article_id: int, name: string, unit: string, quantity: float, unit_price: float}> */
    public array $cart = [];

    public ?string $noteType = null;

    public ?int $selectedClientId = null;

    public bool $showNewClientModal = false;

    public string $clientName = '';

    public string $clientRfc = '';

    public string $clientPhone = '';

    public string $clientAddress = '';

    public ?int $saleForTicketId = null;

    public bool $showTicket = false;

    public function getArticlesProperty()
    {
        return Article::query()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->get();
    }

    public function getClientsProperty()
    {
        return Client::query()->orderBy('name')->get();
    }

    public function getCartTotalProperty(): float
    {
        $total = 0.0;
        foreach ($this->cart as $item) {
            $total += (float) $item['quantity'] * (float) $item['unit_price'];
        }
        return round($total, 2);
    }

    public function getTicketSaleProperty(): ?Sale
    {
        if (! $this->saleForTicketId) {
            return null;
        }
        return Sale::with(['saleItems.article', 'client'])->find($this->saleForTicketId);
    }

    public function addToCart(int $articleId, float $quantity = 1): void
    {
        $article = Article::find($articleId);
        if (! $article) {
            return;
        }
        $qty = max(1, (int) round($quantity));
        $available = (float) $article->stock;
        if ($qty > $available) {
            $maxMsg = $available == (int) $available ? (int) $available : number_format($available, 2);
            $this->dispatch('notify', message: 'No hay suficiente stock. Máximo: ' . $maxMsg . ' ' . $article->unit);
            return;
        }
        $price = (float) $article->sale_price;
        foreach ($this->cart as $index => $item) {
            if ((int) $item['article_id'] === $articleId) {
                $currentQty = (int) round($item['quantity']);
                $newQty = $currentQty + $qty;
                if ($newQty > $available) {
                    $maxMsg = $available == (int) $available ? (int) $available : number_format($available, 2);
                    $this->dispatch('notify', message: 'No hay suficiente stock. Máximo: ' . $maxMsg . ' ' . $article->unit);
                    return;
                }
                $this->cart[$index]['quantity'] = $newQty;
                return;
            }
        }
        $this->cart[] = [
            'article_id' => $article->id,
            'name' => $article->name,
            'unit' => $article->unit,
            'quantity' => $qty,
            'unit_price' => $price,
        ];
    }

    public function updateCartItem(int $index, float $quantity): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }
        $qty = max(1, (int) round($quantity));
        $article = Article::find($this->cart[$index]['article_id']);
        if (! $article) {
            return;
        }
        $available = (float) $article->stock;
        if ($qty > $available) {
            $maxMsg = $available == (int) $available ? (int) $available : number_format($available, 2);
            $this->dispatch('notify', message: 'No hay suficiente stock. Máximo: ' . $maxMsg . ' ' . $article->unit);
            $qty = (int) $available;
        }
        $this->cart[$index]['quantity'] = $qty;
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cart[$index])) {
            array_splice($this->cart, $index, 1);
        }
    }

    public function goToStep(int $step): void
    {
        $this->step = $step;
        if ($step === 3) {
            $this->noteType = null;
            $this->selectedClientId = null;
        }
    }

    public function selectVentaUnica(): void
    {
        $this->noteType = 'venta_unica';
    }

    public function selectNotaDeVenta(): void
    {
        $this->noteType = 'nota_venta';
    }

    public function confirmVentaUnica(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Agregue al menos un artículo a la nota.');
            return;
        }
        $sale = $this->createSale(null, 'venta_unica');
        if ($sale) {
            $this->saleForTicketId = $sale->id;
            $this->showTicket = true;
            $this->cart = [];
            $this->step = 1;
            $this->noteType = null;
        }
    }

    public function openNewClientModal(): void
    {
        $this->clientName = '';
        $this->clientRfc = '';
        $this->clientPhone = '';
        $this->clientAddress = '';
        $this->resetValidation();
        $this->showNewClientModal = true;
    }

    public function closeNewClientModal(): void
    {
        $this->showNewClientModal = false;
    }

    public function saveNewClient(): void
    {
        $this->validate([
            'clientName' => ['required', 'string', 'max:255'],
            'clientRfc' => ['nullable', 'string', 'max:20'],
            'clientPhone' => ['nullable', 'string', 'max:50'],
            'clientAddress' => ['nullable', 'string'],
        ], [], [
            'clientName' => 'nombre',
            'clientRfc' => 'RFC',
            'clientPhone' => 'teléfono',
            'clientAddress' => 'dirección',
        ]);

        $client = Client::create([
            'name' => $this->clientName,
            'rfc' => $this->clientRfc ?: null,
            'phone' => $this->clientPhone ?: null,
            'address' => $this->clientAddress ?: null,
        ]);

        $this->selectedClientId = $client->id;
        $this->showNewClientModal = false;
        $this->clientName = '';
        $this->clientRfc = '';
        $this->clientPhone = '';
        $this->clientAddress = '';
        session()->flash('message', 'Cliente registrado. Ya está seleccionado; puede crear la nota de venta.');
    }

    public function confirmNotaDeVenta(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', message: 'Agregue al menos un artículo a la nota.');
            return;
        }
        if (! $this->selectedClientId) {
            $this->dispatch('notify', message: 'Seleccione un cliente o registre uno nuevo.');
            return;
        }
        $sale = $this->createSale($this->selectedClientId, 'nota_venta');
        if ($sale) {
            $this->saleForTicketId = $sale->id;
            $this->showTicket = true;
            $this->cart = [];
            $this->step = 1;
            $this->noteType = null;
            $this->selectedClientId = null;
        }
    }

    public function closeTicket(): void
    {
        $this->showTicket = false;
        $this->saleForTicketId = null;
        session()->flash('message', 'Venta registrada correctamente.');
    }

    public function selectClient(int $clientId): void
    {
        $this->selectedClientId = $clientId;
    }

    public function backToNoteTypeChoice(): void
    {
        $this->noteType = null;
    }

    private function createSale(?int $clientId, string $noteType): ?Sale
    {
        $total = $this->getCartTotalProperty();
        foreach ($this->cart as $item) {
            $article = Article::find($item['article_id']);
            if (! $article || (float) $article->stock < (float) $item['quantity']) {
                $this->dispatch('notify', message: 'No hay stock suficiente para: ' . ($item['name'] ?? ''));
                return null;
            }
        }

        $sale = Sale::create([
            'user_id' => auth()->id(),
            'client_id' => $clientId,
            'note_type' => $noteType,
            'status' => 'cerrada',
            'total' => $total,
            'notes' => null,
        ]);

        foreach ($this->cart as $item) {
            $subtotal = (float) $item['quantity'] * (float) $item['unit_price'];
            SaleItem::create([
                'sale_id' => $sale->id,
                'article_id' => $item['article_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => round($subtotal, 2),
            ]);
            $article = Article::find($item['article_id']);
            if ($article) {
                $article->decrement('stock', $item['quantity']);
            }
        }

        return $sale;
    }

    public function render()
    {
        return view('livewire.ventas.punto-de-venta');
    }
}
