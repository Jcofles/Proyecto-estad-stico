<?php

namespace App\Livewire;

use Livewire\Component;

class AtmSimulation extends Component
{
    // Parámetros de entrada del formulario
    public $numClients = 500;
    public $interarrivalTimeMean = 3.0; 
    public $serviceTimeMean = 2.0;       
    public $failureProbability = 0.03;   
    public $maxQueueCapacity = 3;        

    // Estado del proceso y resultados
    public $isLoading = false;
    public $results = null;

    public function runSimulation()
    {
        // Generamos datos estáticos ficticios solo para probar cómo se ve la interfaz
        $this->results = [
            'avg_wait_time' => 3.42,
            'abandonment_rate' => 8.5,
            'reliability' => 97.0,
            'prob_long_wait' => 12.3,
            'sample_clients' => [
                ['id' => 1, 'arrival_time' => 1.2, 'wait_time' => 0.0, 'atm_failed' => false, 'status' => 'Atendido'],
                ['id' => 2, 'arrival_time' => 3.5, 'wait_time' => 1.5, 'atm_failed' => false, 'status' => 'Atendido'],
                ['id' => 3, 'arrival_time' => 4.1, 'wait_time' => 6.2, 'atm_failed' => true, 'status' => 'Abandonó'],
                ['id' => 4, 'arrival_time' => 7.8, 'wait_time' => 2.1, 'atm_failed' => false, 'status' => 'Atendido'],
                ['id' => 5, 'arrival_time' => 9.0, 'wait_time' => 0.0, 'atm_failed' => false, 'status' => 'Atendido'],
            ]
        ];
    }

    public function render()
    {
        return view('livewire.index');
    }
}