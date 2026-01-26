<?php

namespace App\Livewire\Bapendik\Signatory;

use App\Models\Signatory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $sortBy = 'created_at';
    #[Url] public string $sortDirection = 'desc';
    #[Url] public int $perPage = 10;
    #[Url] public string $search = '';

    // form state
    public ?int $editingId = null;
    public string $name = '';
    public string $position = '';
    public string $nip = '';

    // delete
    public ?int $deleteId = null;

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'nip'      => ['nullable', 'string', 'max:50'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        return Signatory::query()
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                       ->orWhere('position', 'like', $term)
                       ->orWhere('nip', 'like', $term);
                });
            })
            ->tap(fn ($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q)
            ->paginate($this->perPage);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        Flux::modal('signatory-form')->show();
    }

    public function openEdit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $s = Signatory::findOrFail($id);
        $this->editingId = $s->id;
        $this->name = (string) $s->name;
        $this->position = (string) $s->position;
        $this->nip = (string) ($s->nip ?? '');

        Flux::modal('signatory-form')->show();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $s = Signatory::findOrFail($this->editingId);
            $s->update($data);
            Flux::toast(heading: 'Tersimpan', text: 'Penandatangan diperbarui.', variant: 'success');
        } else {
            Signatory::create($data);
            Flux::toast(heading: 'Tersimpan', text: 'Penandatangan ditambahkan.', variant: 'success');
        }

        Flux::modal('signatory-form')->close();
        $this->resetForm();
    }

    public function markDelete(int $id): void
    {
        $this->deleteId = $id;
        Flux::modal('signatory-delete')->show();
    }

    public function confirmDelete(): void
    {
        if (! $this->deleteId) return;

        Signatory::whereKey($this->deleteId)->delete();

        Flux::modal('signatory-delete')->close();
        Flux::toast(heading: 'Dihapus', text: 'Penandatangan berhasil dihapus.', variant: 'warning');

        $this->deleteId = null;
        $this->resetPage();
    }

    public function cancel(): void
    {
        Flux::modal('signatory-form')->close();
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editingId = null;
        $this->name = '';
        $this->position = '';
        $this->nip = '';
    }

    public function render()
    {
        return view('livewire.bapendik.signatory.index');
    }
}
