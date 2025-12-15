<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Signatory;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SignatoryIndex extends Component
{
    use WithPagination;

    // UI state
    public bool $showForm = false;

    // Filter & paging
    public string $q = '';
    public int $perPage = 10;
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $nip = '';
    public string $position = '';

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'nip'      => ['nullable', 'string', 'max:50', Rule::unique('signatories', 'nip')->ignore($this->editingId)],
            'position' => ['required', 'string', 'max:255'],
        ];
    }

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
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
    public function items()
    {
        return Signatory::query()
            ->when($this->q !== '', function ($q) {
                $term = '%' . $this->q . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('nip', 'like', $term)
                        ->orWhere('position', 'like', $term);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // --- Actions ---

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        Flux::modal('signatory-form')->show();
    }

    public function edit(int $id): void
    {
        $row = Signatory::findOrFail($id);

        $this->editingId = $row->id;
        $this->name      = $row->name;
        $this->nip       = (string) $row->nip;
        $this->position  = $row->position;

        $this->showForm = true;
        Flux::modal('signatory-form')->show();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        Flux::modal('signatory-form')->close();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $row = Signatory::findOrFail($this->editingId);
            $row->update([
                'name'     => $this->name,
                'nip'      => $this->nip,
                'position' => $this->position,
            ]);
            $msg = 'Data penandatangan diperbarui.';
        } else {
            Signatory::create([
                'name'     => $this->name,
                'nip'      => $this->nip,
                'position' => $this->position,
            ]);
            $msg = 'Penandatangan ditambahkan.';
        }

        $this->closeForm();
        $this->resetForm();
        $this->resetPage();

        Flux::toast(heading: 'Berhasil', text: $msg, variant: 'success');
    }

    public function delete(int $id): void
    {
        // Cek apakah dipakai (opsional, tergantung kebutuhan integritas)
        // Saat ini delete cascade/set null di DB, jadi aman dihapus langsung
        $row = Signatory::findOrFail($id);
        $row->delete();

        $this->resetPage();
        Flux::toast(heading: 'Terhapus', text: 'Data dihapus.', variant: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name      = '';
        $this->nip       = '';
        $this->position  = '';
    }

    public function render()
    {
        return view('livewire.bapendik.master.signatory-index');
    }
}
