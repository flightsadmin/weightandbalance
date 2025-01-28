<?php

namespace App\Livewire\Setting;

use App\Models\Airline;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $search = '';
    public $airline;

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function render()
    {
        return view('livewire.airline.setting.index', [
            'settings' => Setting::query()
                ->where('airline_id', $this->airline->id)
                ->when($this->search, function ($query) {
                    $query->where('key', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->paginate(20)
        ])->layout('components.layouts.app');
    }


    public function updateValue(Setting $setting, $value)
    {
        if ($setting->airline_id !== $this->airline->id) {
            return;
        }

        $setting->update(['value' => $value]);

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Setting updated successfully.'
        ]);
    }
}