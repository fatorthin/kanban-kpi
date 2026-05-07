<?php

namespace App\Livewire\Clients;

use App\Models\Client;
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

    protected array $rules = [
        'name'  => 'required|string|max:255',
        'code'  => 'required|string|max:50|unique:clients,code',
        'grade' => 'required|in:A,B,C,D,E,F',
    ];

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
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['code'] = 'required|string|max:50|unique:clients,code,' . $this->editingId;
        }

        $this->validate($rules);

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

        return view('livewire.clients.index', compact('clients'));
    }
}
