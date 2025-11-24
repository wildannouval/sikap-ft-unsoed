<?php

namespace App\Livewire\Bapendik\Master;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DosenIndex extends Component
{
    use WithPagination;

    // UI state (modal)
    public bool $showForm = false;

    // Filter & paging
    public string $q = '';
    public int $perPage = 10;

    // Form fields
    public ?int $editingId = null;   // PK dosens = dosen_id
    public ?int $jurusan_id = null;
    public string $dosen_name = '';
    public ?string $dosen_nip = null;
    public bool $is_komisi_kp = false;

    // Akun login
    public ?int $user_id = null;
    public string $email = '';
    public string $password = '';

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

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true; // dipakai oleh :show modal
    }

    public function edit(int $id): void
    {
        $row = Dosen::with('user')->findOrFail($id);

        $this->editingId     = $row->getKey(); // dosen_id
        $this->jurusan_id    = $row->jurusan_id;
        $this->dosen_name    = $row->dosen_name;
        $this->dosen_nip     = $row->dosen_nip;
        $this->is_komisi_kp  = (bool) $row->is_komisi_kp;

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
            // Buat/Update user
            if ($this->user_id) {
                $user = User::findOrFail($this->user_id);
                $user->name  = $data['dosen_name'];
                $user->email = $data['email'];
                if (!empty($data['password'])) {
                    $user->password = $data['password']; // cast hashed
                }
                $user->save();
            } else {
                $user = User::create([
                    'name'     => $data['dosen_name'],
                    'email'    => $data['email'],
                    'password' => $data['password'], // cast hashed
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

        $this->showForm = false;
        $this->resetForm();
        session()->flash('ok', 'Data dosen disimpan.');
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $row = Dosen::findOrFail($id);
        $row->delete();

        session()->flash('ok', 'Data dosen dihapus.');
        $this->resetPage();
    }

    public function resetUserPassword(int $id, string $newPassword = 'password'): void
    {
        $row = Dosen::with('user')->findOrFail($id);
        abort_if(!$row->user, 422, 'User belum ada.');

        $row->user->password = $newPassword;
        $row->user->save();

        session()->flash('ok', 'Password akun dosen direset.');
    }

    private function resetForm(): void
    {
        $this->editingId     = null;
        $this->jurusan_id    = null;
        $this->dosen_name    = '';
        $this->dosen_nip     = null;
        $this->is_komisi_kp  = false;

        $this->user_id = null;
        $this->email   = '';
        $this->password = '';
    }

    public function getItemsProperty()
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
            ->orderBy('dosen_name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.bapendik.master.dosen-index', [
            'rows'     => $this->items,
            'jurusans' => Jurusan::orderBy('nama_jurusan')->get(),
        ]);
    }
}
