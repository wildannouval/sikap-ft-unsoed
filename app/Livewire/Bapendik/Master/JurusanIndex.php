<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Jurusan;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class JurusanIndex extends Component
{
    use WithPagination;

    // UI State
    public bool $showForm = false;

    // Filter & Paging
    public string $q = '';
    public int $perPage = 10;
    public string $sortBy = 'nama_jurusan';
    public string $sortDirection = 'asc';

    // Form Fields
    public ?int $editingId = null;
    public string $nama_jurusan = '';

    // Delete Confirmation State
    public ?int $deleteId = null;

    public function rules(): array
    {
        return [
            'nama_jurusan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jurusans', 'nama_jurusan')->ignore($this->editingId)
            ],
        ];
    }

    // --- Reset Pagination Hooks ---
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

    // --- Actions ---

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        Flux::modal('jurusan-form')->show();
    }

    public function edit(int $id): void
    {
        $row = Jurusan::findOrFail($id);
        $this->editingId = $row->id;
        $this->nama_jurusan = $row->nama_jurusan;

        $this->showForm = true;
        Flux::modal('jurusan-form')->show();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        Flux::modal('jurusan-form')->close();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $row = Jurusan::findOrFail($this->editingId);
            $row->update(['nama_jurusan' => $this->nama_jurusan]);
            $msg = 'Data jurusan diperbarui.';
        } else {
            Jurusan::create(['nama_jurusan' => $this->nama_jurusan]);
            $msg = 'Jurusan ditambahkan.';
        }

        $this->closeForm();
        $this->resetForm();
        $this->resetPage();

        Flux::toast(heading: 'Berhasil', text: $msg, variant: 'success');
    }

    // --- Delete Logic ---

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        Flux::modal('delete-confirm')->show();
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            try {
                $row = Jurusan::findOrFail($this->deleteId);
                $row->delete();

                $this->resetPage();
                Flux::toast(heading: 'Terhapus', text: 'Data jurusan dihapus.', variant: 'success');
            } catch (\Exception $e) {
                Flux::toast(heading: 'Gagal', text: 'Gagal menghapus data. Kemungkinan sedang digunakan.', variant: 'danger');
            }
        }

        $this->deleteId = null;
        Flux::modal('delete-confirm')->close();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->nama_jurusan = '';
    }

    #[Computed]
    public function items()
    {
        return Jurusan::query()
            ->when($this->q !== '', function ($q) {
                $q->where('nama_jurusan', 'like', '%' . $this->q . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.bapendik.master.jurusan-index');
    }
}
