<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DosenIndex extends Component
{
    use WithPagination;

    // UI state
    public bool $showForm = false;

    // Filter & paging
    public string $q = '';
    public int $perPage = 10;
    public string $sortBy = 'dosen_name';
    public string $sortDirection = 'asc';

    // Form fields
    public ?int $editingId = null;
    public ?int $jurusan_id = null;
    public string $dosen_name = '';
    public ?string $dosen_nip = null;
    public bool $is_komisi_kp = false;

    // Akun login
    public ?int $user_id = null;
    public string $email = '';
    public string $password = '';

    // Delete Confirmation State
    public ?int $deleteId = null;

    public function rules(): array
    {
        return [
            'dosen_name'   => ['required', 'string', 'max:120'],
            'dosen_nip'    => ['nullable', 'string', 'max:50', Rule::unique('dosens', 'dosen_nip')->ignore($this->editingId, 'dosen_id')],
            'jurusan_id'   => ['nullable', 'exists:jurusans,id'],
            'is_komisi_kp' => ['boolean'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user_id)],
            'password'     => [$this->editingId ? 'nullable' : 'required', 'min:6'],
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
        Flux::modal('dosen-form')->show();
    }

    public function edit(int $id): void
    {
        $row = Dosen::with('user')->findOrFail($id);

        $this->editingId    = $row->getKey();
        $this->jurusan_id   = $row->jurusan_id;
        $this->dosen_name   = $row->dosen_name;
        $this->dosen_nip    = $row->dosen_nip;
        $this->is_komisi_kp = (bool) $row->is_komisi_kp;

        $this->user_id = $row->user_id;
        $this->email   = $row->user?->email ?? '';
        $this->password = '';

        $this->showForm = true;
        Flux::modal('dosen-form')->show();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        Flux::modal('dosen-form')->close();
    }

    public function save(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            // Buat/Update user
            if ($this->user_id) {
                $user = User::findOrFail($this->user_id);
                $user->name  = $data['dosen_name'];
                $user->email = $data['email'];
                if (!empty($data['password'])) {
                    $user->password = $data['password'];
                }
                $user->save();
            } else {
                $user = User::create([
                    'name'     => $data['dosen_name'],
                    'email'    => $data['email'],
                    'password' => $data['password'],
                ]);
                $this->user_id = $user->id;
            }

            // Roles
            $user->syncRoles(['Dosen Pembimbing']);
            if ($data['is_komisi_kp']) {
                if (!$user->hasRole('Dosen Komisi')) {
                    $user->assignRole('Dosen Komisi');
                }
            } else {
                $user->removeRole('Dosen Komisi');
            }

            // Upsert Dosen
            $payload = [
                'user_id'      => $user->id,
                'dosen_name'   => $data['dosen_name'],
                'dosen_nip'    => $data['dosen_nip'],
                'jurusan_id'   => $data['jurusan_id'],
                'is_komisi_kp' => $data['is_komisi_kp'],
            ];

            if ($this->editingId) {
                Dosen::whereKey($this->editingId)->update($payload);
            } else {
                $created = Dosen::create($payload);
                $this->editingId = $created->getKey();
            }
        });

        $this->closeForm();
        $this->resetForm();
        $this->resetPage();

        Flux::toast(heading: 'Berhasil', text: 'Data dosen disimpan.', variant: 'success');
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
            $row = Dosen::findOrFail($this->deleteId);
            $row->delete(); // User juga akan terhapus jika cascade di DB, atau perlu logic tambahan

            $this->resetPage();
            Flux::toast(heading: 'Terhapus', text: 'Data dosen dihapus.', variant: 'success');
        }

        $this->deleteId = null;
        Flux::modal('delete-confirm')->close();
    }

    public function resetUserPassword(int $id, string $newPassword = 'password'): void
    {
        $row = Dosen::with('user')->findOrFail($id);
        abort_if(!$row->user, 422, 'User belum ada.');

        $row->user->password = $newPassword;
        $row->user->save();

        Flux::toast(heading: 'Berhasil', text: 'Password akun dosen direset.', variant: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->jurusan_id   = null;
        $this->dosen_name   = '';
        $this->dosen_nip    = null;
        $this->is_komisi_kp = false;
        $this->user_id      = null;
        $this->email        = '';
        $this->password     = '';
    }

    #[Computed]
    public function items()
    {
        return Dosen::query()
            ->with(['user:id,email', 'jurusan:id,nama_jurusan'])
            ->when($this->q !== '', function ($q) {
                $term = '%' . $this->q . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('dosen_name', 'like', $term)
                        ->orWhere('dosen_nip', 'like', $term);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.bapendik.master.dosen-index', [
            'jurusans' => Jurusan::orderBy('nama_jurusan')->get(),
        ]);
    }
}
