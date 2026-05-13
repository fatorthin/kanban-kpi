<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\GradeMultiplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm  = false;
    public ?int $editingId = null;
    public string $search  = '';
    public string $name    = '';
    public string $code    = '';
    public string $grade   = 'A';

    protected function rules()
    {
        $grades = GradeMultiplier::pluck('grade')->toArray();
        return [
            'name'  => 'required|string|max:255',
            'code'  => 'required|string|max:50|unique:clients,code' . ($this->editingId ? ',' . $this->editingId : ''),
            'grade' => 'required|in:' . implode(',', $grades),
        ];
    }

    public function edit(int $id): void
    {
        $client          = Client::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $client->name;
        $this->code      = $client->code;
        $this->grade     = $client->grade;
        $this->showForm  = true;
    }
    public function save(): void
    {
        $this->validate();

        $data = ['name' => $this->name, 'code' => $this->code, 'grade' => $this->grade];

        if ($this->editingId) {
            Client::findOrFail($this->editingId)->update($data);
        } else {
            Client::create($data);
        }

        $this->reset(['showForm', 'editingId', 'name', 'code', 'grade']);
    }

    public function delete(int $id): void
    {
        Client::findOrFail($id)->delete();
    }

    public function render(): \Illuminate\View\View
    {
        $clients = Client::when($this->search, fn ($q) =>
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('code', 'like', '%' . $this->search . '%')
        )->withCount('tasks')->latest()->get();

        $grades = GradeMultiplier::pluck('grade')->toArray();

        return view('livewire.clients.index', compact('clients', 'grades'));
    }
}
