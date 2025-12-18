<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MahasiswaIndex extends Component
{
    use WithPagination;

    // UI state
    public bool $showForm = false;

    // Filter & paging
    public string $q = '';
    public int $perPage = 10;
    public string $sortBy = 'mahasiswa_name';
    public string $sortDirection = 'asc';

    // PK tabel mahasiswas = mahasiswa_id
    public ?int $editingId = null;
    public ?int $jurusan_id = null;
    public string $mahasiswa_name = '';
    public string $mahasiswa_nim  = '';
    public ?int $mahasiswa_tahun_angkatan = null;

    public ?int $user_id = null;
    public string $email = '';
    public string $password = '';

    // Delete Confirmation State
    public ?int $deleteId = null;

    public function rules(): array
    {
        return [
            'mahasiswa_name'           => ['required', 'string', 'max:120'],
            'mahasiswa_nim'            => ['required', 'string', 'max:30', Rule::unique('mahasiswas', 'mahasiswa_nim')->ignore($this->editingId, 'mahasiswa_id')],
            'mahasiswa_tahun_angkatan' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'jurusan_id'               => ['nullable', 'exists:jurusans,id'],
            'email'                    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user_id)],
            'password'                 => [$this->editingId ? 'nullable' : 'required', 'min:6'],
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

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
        Flux::modal('mhs-form')->show();
    }

    public function edit(int $id): void
    {
        $row = Mahasiswa::with('user')->findOrFail($id);

        $this->editingId                = $row->getKey();
        $this->jurusan_id               = $row->jurusan_id;
        $this->mahasiswa_name           = $row->mahasiswa_name;
        $this->mahasiswa_nim            = $row->mahasiswa_nim;
        $this->mahasiswa_tahun_angkatan = $row->mahasiswa_tahun_angkatan;

        $this->user_id = $row->user_id;
        $this->email   = $row->user?->email ?? '';
        $this->password = '';

        $this->showForm = true;
        Flux::modal('mhs-form')->show();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        Flux::modal('mhs-form')->close();
    }

    public function save(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            // User
            if ($this->user_id) {
                $user = User::findOrFail($this->user_id);
                $user->name  = $data['mahasiswa_name'];
                $user->email = $data['email'];
                if (!empty($data['password'])) {
                    $user->password = $data['password'];
                }
                $user->save();
            } else {
                $user = User::create([
                    'name'     => $data['mahasiswa_name'],
                    'email'    => $data['email'],
                    'password' => $data['password'],
                ]);
                $this->user_id = $user->id;
            }

            // Role
            $user->syncRoles(['Mahasiswa']);

            // Upsert Mahasiswa
            $payload = [
                'user_id'                  => $user->id,
                'jurusan_id'               => $data['jurusan_id'],
                'mahasiswa_name'           => $data['mahasiswa_name'],
                'mahasiswa_nim'            => $data['mahasiswa_nim'],
                'mahasiswa_tahun_angkatan' => $data['mahasiswa_tahun_angkatan'],
            ];

            if ($this->editingId) {
                Mahasiswa::whereKey($this->editingId)->update($payload);
            } else {
                $created = Mahasiswa::create($payload);
                $this->editingId = $created->getKey();
            }
        });

        $this->closeForm();
        $this->resetForm();
        $this->resetPage();

        Flux::toast(heading: 'Berhasil', text: 'Data mahasiswa disimpan.', variant: 'success');
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
            $row = Mahasiswa::findOrFail($this->deleteId);
            $row->delete(); // User cascade delete biasanya diatur di migration/model event

            $this->resetPage();
            Flux::toast(heading: 'Terhapus', text: 'Data mahasiswa dihapus.', variant: 'success');
        }

        $this->deleteId = null;
        Flux::modal('delete-confirm')->close();
    }

    public function resetUserPassword(int $id, string $newPassword = 'password'): void
    {
        $row = Mahasiswa::with('user')->findOrFail($id);
        abort_if(!$row->user, 422, 'User belum ada.');

        $row->user->password = $newPassword;
        $row->user->save();

        Flux::toast(heading: 'Berhasil', text: 'Password akun mahasiswa direset.', variant: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->jurusan_id = null;
        $this->mahasiswa_name = '';
        $this->mahasiswa_nim  = '';
        $this->mahasiswa_tahun_angkatan = null;
        $this->user_id = null;
        $this->email   = '';
        $this->password = '';
    }

    #[Computed]
    public function items()
    {
        return Mahasiswa::query()
            ->with(['user:id,email', 'jurusan:id,nama_jurusan'])
            ->when($this->q !== '', function ($q) {
                $term = '%' . $this->q . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('mahasiswa_name', 'like', $term)
                        ->orWhere('mahasiswa_nim', 'like', $term);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.bapendik.master.mahasiswa-index', [
            'jurusans' => Jurusan::orderBy('nama_jurusan')->get(),
        ]);
    }
}
