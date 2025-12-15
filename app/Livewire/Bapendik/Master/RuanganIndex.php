<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Room;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RuanganIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public string $sortBy = 'building';
    public string $sortDirection = 'asc';
    public int    $perPage = 10;

    // form state
    public bool $showForm = false; // Untuk kontrol modal
    public ?int $editId = null;
    public string $room_number = '';
    public string $building = '';
    public ?string $notes = null;

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

    protected function rules(): array
    {
        return [
            'room_number' => ['required', 'string', 'max:50'],
            'building'    => ['required', 'string', 'max:100'],
            'notes'       => ['nullable', 'string', 'max:255'],
        ];
    }

    #[Computed]
    public function items()
    {
        return Room::query()
            ->when($this->q !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('room_number', 'like', '%' . $this->q . '%')
                        ->orWhere('building', 'like', '%' . $this->q . '%')
                        ->orWhere('notes', 'like', '%' . $this->q . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function resetForm(): void
    {
        $this->reset(['editId', 'room_number', 'building', 'notes']);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        Flux::modal('ruangan-form')->show();
    }

    public function edit(int $id): void
    {
        $r = Room::findOrFail($id);

        $this->editId      = $r->id;
        $this->room_number = $r->room_number;
        $this->building    = $r->building;
        $this->notes       = $r->notes;

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

        if ($this->editId) {
            $r = Room::findOrFail($this->editId);
            $r->update([
                'room_number' => $this->room_number,
                'building'    => $this->building,
                'notes'       => $this->notes,
            ]);
            $msg = 'Perubahan ruangan disimpan.';
        } else {
            Room::create([
                'room_number' => $this->room_number,
                'building'    => $this->building,
                'notes'       => $this->notes,
            ]);
            $msg = 'Ruangan ditambahkan.';
        }

        $this->closeForm();
        $this->resetForm();
        $this->resetPage();

        Flux::toast(heading: 'Berhasil', text: $msg, variant: 'success');
    }

    public function delete(int $id): void
    {
        Room::findOrFail($id)->delete();
        $this->resetPage();

        Flux::toast(heading: 'Terhapus', text: 'Ruangan dihapus.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.bapendik.master.ruangan-index');
    }
}
