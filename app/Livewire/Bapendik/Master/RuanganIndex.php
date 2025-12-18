<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Room;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RuanganIndex extends Component
{
    use WithPagination;

    // UI State
    public bool $showForm = false;

    // Filter & Paging
    public string $q = '';
    public int $perPage = 10;
    public string $sortBy = 'room_number';
    public string $sortDirection = 'asc';

    // Form Fields
    public ?int $editingId = null;
    public string $room_number = '';
    public string $building = '';
    public string $notes = '';

    // Delete Confirmation State
    public ?int $deleteId = null;

    public function rules(): array
    {
        return [
            'room_number' => [
                'required',
                'string',
                'max:50',
                // Validasi unik kombinasi room_number + building
                Rule::unique('rooms')->where(function ($query) {
                    return $query->where('room_number', $this->room_number)
                        ->where('building', $this->building);
                })->ignore($this->editingId)
            ],
            'building' => ['required', 'string', 'max:100'],
            'notes'    => ['nullable', 'string', 'max:255'],
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
        Flux::modal('ruangan-form')->show();
    }

    public function edit(int $id): void
    {
        $row = Room::findOrFail($id);
        $this->editingId = $row->id;
        $this->room_number = $row->room_number;
        $this->building    = $row->building;
        $this->notes       = (string) $row->notes;

        $this->showForm = true;
        Flux::modal('ruangan-form')->show();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        Flux::modal('ruangan-form')->close();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'room_number' => $this->room_number,
            'building'    => $this->building,
            'notes'       => $this->notes,
        ];

        if ($this->editingId) {
            $row = Room::findOrFail($this->editingId);
            $row->update($data);
            $msg = 'Data ruangan diperbarui.';
        } else {
            Room::create($data);
            $msg = 'Ruangan ditambahkan.';
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
                $row = Room::findOrFail($this->deleteId);
                $row->delete();

                $this->resetPage();
                Flux::toast(heading: 'Terhapus', text: 'Data ruangan dihapus.', variant: 'success');
            } catch (\Exception $e) {
                Flux::toast(heading: 'Gagal', text: 'Gagal menghapus data.', variant: 'danger');
            }
        }

        $this->deleteId = null;
        Flux::modal('delete-confirm')->close();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->room_number = '';
        $this->building    = '';
        $this->notes       = '';
    }

    #[Computed]
    public function items()
    {
        return Room::query()
            ->when($this->q !== '', function ($q) {
                $term = '%' . $this->q . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('room_number', 'like', $term)
                        ->orWhere('building', 'like', $term)
                        ->orWhere('notes', 'like', $term);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.bapendik.master.ruangan-index');
    }
}
