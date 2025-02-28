<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Attributes\On;

class Show extends Component
{
    public Airline $airline;

    #[Url]
    public $activeTab = 'overview';

    #[Url]
    public $settingCategory = 'general';

    public $editingSetting = null;
    public $showSettingModal = false;
    public $editingUldKey = null;

    // Form fields
    public $form = [
        'category' => '',
        'key' => '',
        'value' => '',
        'type' => '',
        'description' => '',
    ];

    public $uldForm = [
        'code' => '',
        'name' => '',
        'tare_weight' => 0,
        'max_gross_weight' => 0,
        'positions_required' => 1,
        'color' => '#0dcaf0',
        'icon' => 'box-seam',
        'allowed_holds' => ['FWD', 'AFT'],
        'restrictions' => [
            'requires_adjacent_positions' => false,
            'requires_vertical_positions' => false
        ]
    ];

    // Grouped settings
    protected $defaultSettings = [
        'general' => [
            'standard_passenger_weight' => ['type' => 'integer', 'description' => 'Standard passenger weight (kg)'],
            'standard_male_passenger_weight' => ['type' => 'integer', 'description' => 'Standard male passenger weight (kg)'],
            'standard_female_passenger_weight' => ['type' => 'integer', 'description' => 'Standard female passenger weight (kg)'],
            'standard_child_passenger_weight' => ['type' => 'integer', 'description' => 'Standard child passenger weight (kg)'],
            'standard_infant_passenger_weight' => ['type' => 'integer', 'description' => 'Standard infant passenger weight (kg)'],
            'standard_cockpit_crew_weight' => ['type' => 'integer', 'description' => 'Standard cockpit crew weight (kg)'],
            'standard_cabin_crew_weight' => ['type' => 'integer', 'description' => 'Standard cabin crew weight (kg)'],
            'standard_baggage_weight' => ['type' => 'integer', 'description' => 'Standard baggage weight (kg)'],
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
            'max_cargo_piece_weight' => ['type' => 'integer', 'description' => 'Maximum cargo piece weight (kg)'],
            'max_baggage_piece_weight' => ['type' => 'integer', 'description' => 'Maximum baggage piece weight (kg)'],
        ],
        'notifications' => [
            'enable_email_notifications' => ['type' => 'boolean', 'description' => 'Enable email notifications'],
            'enable_sms_notifications' => ['type' => 'boolean', 'description' => 'Enable SMS notifications'],
            'notification_email' => ['type' => 'string', 'description' => 'Notification email address'],
            'notification_phone' => ['type' => 'string', 'description' => 'Notification phone number'],
        ],
    ];

    protected $defaultUldTypes = [
        'pmc' => [
            'code' => 'PMC',
            'name' => 'Pallet with Net',
            'tare_weight' => 110,
            'max_gross_weight' => 3400,
            'positions_required' => 2,
            'color' => '#fd7e14',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT'],
            'restrictions' => [
                'requires_adjacent_positions' => true,
                'requires_vertical_positions' => true
            ]
        ],
        'ake' => [
            'code' => 'AKE',
            'name' => 'LD3 Container',
            'tare_weight' => 85,
            'max_gross_weight' => 1588,
            'positions_required' => 1,
            'color' => '#0dcaf0',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT', 'BULK'],
            'restrictions' => [
                'requires_adjacent_positions' => false,
                'requires_vertical_positions' => false
            ]
        ]
    ];

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
            'value' => $settings[$key] ?? ($config['type'] === 'json' ? $config['default'] : ''),
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
            'form.type' => 'required|in:string,float,integer,boolean,json',
        ]);

        $value = match ($this->form['type']) {
            'float' => (float) $this->form['value'],
            'integer' => (int) $this->form['value'],
            'boolean' => (bool) $this->form['value'],
            'json' => is_array($this->form['value']) ? $this->form['value'] : json_decode($this->form['value'], true),
            default => $this->form['value']
        };

        $this->airline->updateSettings(
            $this->form['category'],
            $this->form['key'],
            $value
        );

        $this->resetForm();
        $this->dispatch('setting-saved');
        $this->dispatch('alert', icon: 'success', message: 'Setting saved successfully.');
    }

    public function resetForm()
    {
        $this->form = [
            'category' => '',
            'key' => '',
            'value' => '',
            'type' => '',
            'description' => '',
        ];
        $this->editingSetting = false;
        $this->showSettingModal = false;
    }

    public function closeModal()
    {
        $this->resetForm();
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

    // ULD Management Methods
    public function getUldTypes()
    {
        $uldSettings = $this->airline->settings()->where('key', 'uld_types')->first();

        if (!$uldSettings) {
            return $this->initializeDefaultUldTypes();
        }

        return json_decode($uldSettings->value, true);
    }

    protected function initializeDefaultUldTypes()
    {
        $defaultUlds = collect($this->defaultUldTypes)->toArray();

        $this->airline->settings()->create([
            'key' => 'uld_types',
            'value' => json_encode($defaultUlds)
        ]);

        return $defaultUlds;
    }

    public function createUldType()
    {
        $this->resetUldForm();
        $this->dispatch('showUldModal');
    }

    public function editUldType($key)
    {
        $this->editingUldKey = $key;
        $uldTypes = $this->getUldTypes();

        if (!isset($uldTypes[$key])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');
            return;
        }

        $this->uldForm = $uldTypes[$key];
    }

    public function saveUldType()
    {
        $this->validate([
            'uldForm.code' => 'required|string|max:3',
            'uldForm.name' => 'required|string|max:255',
            'uldForm.tare_weight' => 'required|numeric|min:0',
            'uldForm.max_gross_weight' => 'required|numeric|min:0',
            'uldForm.positions_required' => 'required|integer|min:1|max:2',
            'uldForm.color' => 'required|string',
            'uldForm.icon' => 'required|string',
            'uldForm.allowed_holds' => 'required|array|min:1',
            'uldForm.allowed_holds.*' => 'required|in:FWD,AFT,BULK',
            'uldForm.restrictions.requires_adjacent_positions' => 'required|boolean',
            'uldForm.restrictions.requires_vertical_positions' => 'required|boolean',
        ]);

        $uldTypes = $this->getUldTypes();
        $key = $this->editingUldKey ?? strtolower($this->uldForm['code']);

        if (!$this->editingUldKey && isset($uldTypes[$key])) {
            $this->addError('uldForm.code', 'This ULD code already exists.');
            return;
        }

        $uldTypes[$key] = [
            'code' => $this->uldForm['code'],
            'name' => $this->uldForm['name'],
            'tare_weight' => (float) $this->uldForm['tare_weight'],
            'max_gross_weight' => (float) $this->uldForm['max_gross_weight'],
            'positions_required' => (int) $this->uldForm['positions_required'],
            'color' => $this->uldForm['color'],
            'icon' => $this->uldForm['icon'],
            'allowed_holds' => $this->uldForm['allowed_holds'],
            'restrictions' => [
                'requires_adjacent_positions' => (bool) $this->uldForm['restrictions']['requires_adjacent_positions'],
                'requires_vertical_positions' => (bool) $this->uldForm['restrictions']['requires_vertical_positions']
            ]
        ];

        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->dispatch('uld-saved');
        $this->resetUldForm();
        $this->dispatch('alert', icon: 'success', message: 'ULD type ' . ($this->editingUldKey ? 'updated' : 'created') . ' successfully.');
    }

    public function deleteUldType($key)
    {
        $uldTypes = $this->getUldTypes();

        if (!isset($uldTypes[$key])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');
            return;
        }

        unset($uldTypes[$key]);
        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->dispatch('alert', icon: 'success', message: 'ULD type deleted successfully.');
    }

    public function resetUldForm()
    {
        $this->editingUldKey = null;
        $this->uldForm = [
            'code' => '',
            'name' => '',
            'tare_weight' => 0,
            'max_gross_weight' => 0,
            'positions_required' => 1,
            'color' => '#0dcaf0',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT'],
            'restrictions' => [
                'requires_adjacent_positions' => false,
                'requires_vertical_positions' => false
            ]
        ];
    }

    #[On('modalClosed')]
    public function handleModalClosed()
    {
        $this->resetUldForm();
    }

    public function render()
    {
        return view('livewire.airline.show', [
            'settings' => $this->airline->getSettings(),
            'defaultSettings' => $this->defaultSettings,
            'currentSettings' => $this->getCurrentCategorySettings(),
            'uldTypes' => $this->getUldTypes(),
        ]);
    }
}
