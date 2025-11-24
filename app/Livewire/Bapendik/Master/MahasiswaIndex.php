<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MahasiswaIndex extends Component
{
    use WithPagination;

    // UI state (modal)
    public bool $showForm = false;

    public string $q = '';
    public int $perPage = 10;

    // PK tabel mahasiswas = mahasiswa_id
    public ?int $editingId = null;
    public ?int $jurusan_id = null;
    public string $mahasiswa_name = '';
    public string $mahasiswa_nim  = '';
    public ?int $mahasiswa_tahun_angkatan = null;

    public ?int $user_id = null;
    public string $email = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            'mahasiswa_name'           => ['required', 'string', 'max:120'],
            'mahasiswa_nim'            => ['required', 'string', 'max:30', Rule::unique('mahasiswas', 'mahasiswa_nim')->ignore($this->editingId, 'mahasiswa_id')],
            'mahasiswa_tahun_angkatan' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'jurusan_id'               => ['nullable', 'exists:jurusans,id'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user_id)],
            'password' => [$this->editingId ? 'nullable' : 'required', 'min:6'],
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

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
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
    }

    public function closeForm(): void
    {
        $this->showForm = false;
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
                    $user->password = $data['password']; // cast hashed
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

        $this->showForm = false;
        $this->resetForm();
        session()->flash('ok', 'Data mahasiswa disimpan.');
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $row = Mahasiswa::findOrFail($id);
        $row->delete();

        session()->flash('ok', 'Data mahasiswa dihapus.');
        $this->resetPage();
    }

    public function resetUserPassword(int $id, string $newPassword = 'password'): void
    {
        $row = Mahasiswa::with('user')->findOrFail($id);
        abort_if(!$row->user, 422, 'User belum ada.');

        $row->user->password = $newPassword;
        $row->user->save();

        session()->flash('ok', 'Password akun mahasiswa direset.');
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

    public function getItemsProperty()
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
            ->orderBy('mahasiswa_name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.bapendik.master.mahasiswa-index', [
            'rows'     => $this->items,
            'jurusans' => Jurusan::orderBy('nama_jurusan')->get(),
        ]);
    }
}
