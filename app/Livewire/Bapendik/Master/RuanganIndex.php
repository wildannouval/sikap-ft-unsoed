<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Room;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class RuanganIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public string $sortBy = 'building';
    public string $sortDirection = 'asc';
    public int    $perPage = 10;

    // form state
    public ?int $editId = null;
    public string $room_number = '';
    public string $building = '';
    public ?string $notes = null;

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function updatingSortDirection()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
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
    }

    public function store(): void
    {
        $this->validate();

        Room::create([
            'room_number' => $this->room_number,
            'building'    => $this->building,
            'notes'       => $this->notes,
        ]);

        $this->resetForm();
        $this->resetPage();

        // === TOAST sukses tambah ===
        Flux::toast(
            heading: 'Berhasil',
            text: 'Ruangan ditambahkan.',
            variant: 'success',
        );
    }

    public function edit(int $id): void
    {
        $r = Room::findOrFail($id);
        $this->editId      = $r->id;
        $this->room_number = $r->room_number;
        $this->building    = $r->building;
        $this->notes       = $r->notes;
    }

    public function update(): void
    {
        $this->validate();

        $r = Room::findOrFail($this->editId);
        $r->update([
            'room_number' => $this->room_number,
            'building'    => $this->building,
            'notes'       => $this->notes,
        ]);

        $this->resetForm();
        $this->resetPage();

        // === TOAST sukses update ===
        Flux::toast(
            heading: 'Berhasil',
            text: 'Perubahan ruangan disimpan.',
            variant: 'success',
        );
    }

    public function delete(int $id): void
    {
        Room::findOrFail($id)->delete();
        $this->resetPage();

        // === TOAST sukses hapus ===
        Flux::toast(
            heading: 'Berhasil',
            text: 'Ruangan dihapus.',
            variant: 'success',
        );
    }

    public function render()
    {
        return view('livewire.bapendik.master.ruangan-index');
    }
}
