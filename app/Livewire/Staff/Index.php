<?php

namespace App\Livewire\Staff;

use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm  = false;
    public ?int $editingId = null;
    public string $search  = '';
    public string $name       = '';
    public string $positionName = '';
    public string $email      = '';
    public string $whatsappNumber = '';
    public string $password   = '';
    public string $role       = 'staff';
    public ?int $divisionId   = null;
    public ?int $managerId    = null;
    public bool $isActive     = true;
    public float $pointRate   = 0;

    protected array $rules = [
        'name'       => 'required|string|max:255',
        'positionName' => 'nullable|string|max:255',
        'email'      => 'required|email|unique:users,email',
        'whatsappNumber' => 'nullable|string|max:20',
        'password'   => 'nullable|min:8',
        'role'       => 'required|in:staff,manager,director',
        'divisionId' => 'nullable|exists:divisions,id',
        'managerId'  => 'nullable|exists:users,id',
        'pointRate'  => 'required|numeric|min:0',
        'isActive'   => 'boolean',
    ];

    public function edit(int $id): void
    {
        $user            = User::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $user->name;
        $this->positionName = $user->position_name ?? '';
        $this->email     = $user->email;
        $wa = $user->whatsapp_number ?? '';
        if (str_starts_with($wa, '62')) {
            $wa = substr($wa, 2);
        }
        $this->whatsappNumber = $wa;
        $this->role      = $user->roles->first()?->name ?? 'staff';
        $this->divisionId= $user->division_id;
        $this->managerId = $user->manager_id;
        $this->isActive  = (bool) $user->is_active;
        $this->pointRate = (float) $user->base_point_rate;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->editingId;
        }
        $this->validate($rules);

        $wa = $this->whatsappNumber;
        if ($wa) {
            $wa = preg_replace('/[^0-9]/', '', $wa); // strip non-digits
            if (str_starts_with($wa, '0')) {
                $wa = '62' . substr($wa, 1);
            } elseif (!str_starts_with($wa, '62')) {
                $wa = '62' . $wa;
            }
        }

        $data = [
            'name'            => $this->name,
            'position_name'   => $this->positionName,
            'email'           => $this->email,
            'whatsapp_number' => $wa,
            'division_id'     => $this->divisionId,
            'manager_id'      => $this->managerId,
            'is_active'       => $this->isActive,
            'base_point_rate' => $this->pointRate,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        $user->syncRoles([$this->role]);

        $this->reset(['showForm', 'editingId', 'name', 'positionName', 'email', 'whatsappNumber', 'password', 'role', 'divisionId', 'managerId', 'pointRate', 'isActive']);
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        $this->dispatch('notify', type: 'success', message: 'User status updated.');
    }

    public function render(): \Illuminate\View\View
    {
        $users = User::with(['division', 'roles', 'manager'])
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
            )->latest()->get();

        return view('livewire.staff.index', [
            'users'     => $users,
            'divisions' => Division::all(),
            'managers'  => User::role('manager')->get(),
        ]);
    }
}
