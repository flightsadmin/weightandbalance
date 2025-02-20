<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public Airline $airline;

    #[Url]
    public $activeTab = 'overview';

    #[Url]
    public $settingCategory = 'general';

    public $editingSetting = null;

    // Form fields
    public $form = [
        'category' => '',
        'key' => '',
        'value' => '',
        'type' => '',
        'description' => '',
    ];

    // Grouped settings
    protected $defaultSettings = [
        'general' => [
            'standard_passenger_weight' => ['type' => 'float', 'description' => 'Standard passenger weight (kg)'],
            'standard_crew_weight' => ['type' => 'float', 'description' => 'Standard crew weight (kg)'],
            'standard_baggage_weight' => ['type' => 'float', 'description' => 'Standard baggage weight (kg)'],
            'standard_fuel_density' => ['type' => 'float', 'description' => 'Standard fuel density (kg/L)'],
        ],
        'operations' => [
            'checkin_open_time' => ['type' => 'integer', 'description' => 'Check-in opens before departure (minutes)'],
            'checkin_close_time' => ['type' => 'integer', 'description' => 'Check-in closes before departure (minutes)'],
            'boarding_open_time' => ['type' => 'integer', 'description' => 'Boarding opens before departure (minutes)'],
            'boarding_close_time' => ['type' => 'integer', 'description' => 'Boarding closes before departure (minutes)'],
        ],
        'cargo' => [
            'dangerous_goods_allowed' => ['type' => 'boolean', 'description' => 'Allow dangerous goods'],
            'live_animals_allowed' => ['type' => 'boolean', 'description' => 'Allow live animals'],
            'max_cargo_piece_weight' => ['type' => 'float', 'description' => 'Maximum cargo piece weight (kg)'],
            'max_baggage_piece_weight' => ['type' => 'float', 'description' => 'Maximum baggage piece weight (kg)'],
        ],
        'notifications' => [
            'enable_email_notifications' => ['type' => 'boolean', 'description' => 'Enable email notifications'],
            'enable_sms_notifications' => ['type' => 'boolean', 'description' => 'Enable SMS notifications'],
            'notification_email' => ['type' => 'string', 'description' => 'Notification email address'],
            'notification_phone' => ['type' => 'string', 'description' => 'Notification phone number'],
        ],
    ];

    public $showSettingModal = false;

    public function mount(Airline $airline)
    {
        $this->airline = $airline->load([
            'settings',
            'aircraft.type',
            'flights' => fn($q) => $q->latest('scheduled_departure_time')->take(5),
        ]);
    }

    public function setSettingCategory($category)
    {
        $this->settingCategory = $category;
    }

    public function getCurrentCategorySettings()
    {
        return $this->defaultSettings[$this->settingCategory] ?? [];
    }

    public function editSetting($category, $key, $config)
    {
        $settings = $this->airline->getSettings($category);

        $this->form = [
            'category' => $category,
            'key' => $key,
            'value' => $settings[$key] ?? '',
            'type' => $config['type'],
            'description' => $config['description'],
        ];

        $this->editingSetting = true;
        $this->showSettingModal = true;
    }

    public function saveSetting()
    {
        $this->validate([
            'form.value' => 'required',
            'form.type' => 'required|in:string,float,integer,boolean',
        ]);

        $value = match ($this->form['type']) {
            'float' => (float) $this->form['value'],
            'integer' => (int) $this->form['value'],
            'boolean' => (bool) $this->form['value'],
            default => $this->form['value']
        };

        $this->airline->updateSettings(
            $this->form['category'],
            $this->form['key'],
            $value
        );

        $this->showSettingModal = false;
        $this->editingSetting = false;
        $this->dispatch('setting-saved');
        $this->dispatch('alert', icon: 'success', message: 'Setting saved successfully.');
    }

    public function deleteSetting($key)
    {
        $this->airline->settings()->where('key', $key)->delete();
        $this->dispatch('alert', icon: 'success', message: 'Setting deleted successfully.');
    }

    public function toggleStatus()
    {
        $this->airline->active = !$this->airline->active;
        $this->airline->save();

        $this->dispatch('alert', icon: 'success', message: 'Airline status updated successfully.');
    }

    public function render()
    {
        return view('livewire.airline.show', [
            'settings' => $this->airline->getSettings(),
            'defaultSettings' => $this->defaultSettings,
            'currentSettings' => $this->getCurrentCategorySettings(),
        ])->layout('components.layouts.app');
    }
}
