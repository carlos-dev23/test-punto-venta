<?php

namespace App\Livewire\Inventario;

use App\Models\Article;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ArticlesTable extends Component
{
    public string $search = '';

    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingArticleId = null;

    public ?int $articleToDeleteId = null;

    public string $name = '';

    public string $unit = 'pieza';

    public string $stock = '0';

    public string $purchase_price = '0';

    public string $sale_price = '0';

    public function getUnits(): array
    {
        return config('inventario.units', ['pieza', 'kg', 'gramos', 'tonelada', 'm2', 'litro']);
    }

    public function getArticlesProperty()
    {
        return Article::query()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingArticleId = null;
        $this->showModal = true;
    }

    public function openEditModal(Article $article): void
    {
        $this->editingArticleId = $article->id;
        $this->name = $article->name;
        $this->unit = $article->unit;
        $this->stock = (string) $article->stock;
        $this->purchase_price = (string) $article->purchase_price;
        $this->sale_price = (string) $article->sale_price;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', Rule::in($this->getUnits())],
            'stock' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'unit' => $this->unit,
            'stock' => (float) $this->stock,
            'purchase_price' => (float) $this->purchase_price,
            'sale_price' => (float) $this->sale_price,
        ];

        if ($this->editingArticleId) {
            $article = Article::find($this->editingArticleId);
            if ($article) {
                $article->update($data);
                session()->flash('message', 'Artículo actualizado correctamente.');
            }
        } else {
            Article::create($data);
            session()->flash('message', 'Artículo registrado correctamente.');
        }

        $this->closeModals();
    }

    public function confirmDelete(Article $article): void
    {
        $this->articleToDeleteId = $article->id;
        $this->showDeleteModal = true;
    }

    public function deleteArticle(): void
    {
        if ($this->articleToDeleteId) {
            $article = Article::find($this->articleToDeleteId);
            if ($article) {
                $article->delete();
                session()->flash('message', 'Artículo eliminado correctamente.');
            }
            $this->articleToDeleteId = null;
        }
        $this->showDeleteModal = false;
    }

    public function closeModals(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->editingArticleId = null;
        $this->articleToDeleteId = null;
        $this->resetForm();
    }

    public function getArticleToDeleteNameProperty(): ?string
    {
        if (! $this->articleToDeleteId) {
            return null;
        }
        $article = Article::find($this->articleToDeleteId);

        return $article?->name;
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->unit = 'pieza';
        $this->stock = '0';
        $this->purchase_price = '0';
        $this->sale_price = '0';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.inventario.articles-table');
    }
}
