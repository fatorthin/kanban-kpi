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
    public string $email      = '';
    public string $password   = '';
    public string $role       = 'staff';
    public ?int $divisionId   = null;
    public ?int $managerId    = null;
    public float $pointRate   = 0;

    protected array $rules = [
        'name'       => 'required|string|max:255',
        'email'      => 'required|email|unique:users,email',
        'password'   => 'nullable|min:8',
        'role'       => 'required|in:staff,manager,director',
        'divisionId' => 'nullable|exists:divisions,id',
        'managerId'  => 'nullable|exists:users,id',
        'pointRate'  => 'required|numeric|min:0',
    ];

    public function edit(int $id): void
    {
        $user            = User::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->roles->first()?->name ?? 'staff';
        $this->divisionId= $user->division_id;
        $this->managerId = $user->manager_id;
        $this->pointRate = $user->base_point_rate;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->editingId;
        }
        $this->validate($rules);

        $data = [
            'name'            => $this->name,
            'email'           => $this->email,
            'division_id'     => $this->divisionId,
            'manager_id'      => $this->managerId,
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

        $this->reset(['showForm', 'editingId', 'name', 'email', 'password', 'role', 'divisionId', 'managerId', 'pointRate']);
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
