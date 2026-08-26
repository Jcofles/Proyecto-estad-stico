<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Simulador Montecarlo: Cajero Automático (ATM)</h1>
            <p class="text-gray-600 text-sm mt-1">
                Estimación de tiempos de espera, confiabilidad y riesgos de abandono.
            </p>
        </div>

        @if (session()->has('error'))
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Panel de Controles (Formulario) -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 space-y-4">
                <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Parámetros del Sistema</h2>
                
                <form wire:submit.prevent="runSimulation" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Número de Clientes</label>
                        <input type="number" wire:model="numClients" class="w-full mt-1 p-2 border rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Tiempo Medio Entre Llegadas (min)</label>
                        <input type="number" step="0.1" wire:model="interarrivalTimeMean" class="w-full mt-1 p-2 border rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Tiempo Medio de Servicio (min)</label>
                        <input type="number" step="0.1" wire:model="serviceTimeMean" class="w-full mt-1 p-2 border rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Probabilidad de Falla (%)</label>
                        <input type="number" step="0.01" wire:model="failureProbability" class="w-full mt-1 p-2 border rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase">Límite de Fila (Riesgo)</label>
                        <input type="number" wire:model="maxQueueCapacity" class="w-full mt-1 p-2 border rounded-md text-sm">
                    </div>

                    <button type="submit" wire:loading.attr="disabled" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-sm transition">
                        <span wire:loading.remove>Ejecutar Simulación</span>
                        <span wire:loading>Procesando en FastAPI...</span>
                    </button>
                </form>
            </div>

            <!-- Panel de Resultados -->
            <div class="lg:col-span-2 space-y-6">
                
                @if($results)
                    <!-- Métricas Clave (Cards) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <span class="text-xs text-gray-500 font-semibold block">T. Espera Promedio</span>
                            <span class="text-xl font-bold text-blue-600">{{ number_format($results['avg_wait_time'], 2) }} min</span>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <span class="text-xs text-gray-500 font-semibold block">Riesgo de Abandono</span>
                            <span class="text-xl font-bold text-red-600">{{ number_format($results['abandonment_rate'], 1) }}%</span>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <span class="text-xs text-gray-500 font-semibold block">Confiabilidad (Uptime)</span>
                            <span class="text-xl font-bold text-green-600">{{ number_format($results['reliability'], 1) }}%</span>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <span class="text-xs text-gray-500 font-semibold block">Prob. Espera > 5 min</span>
                            <span class="text-xl font-bold text-amber-600">{{ number_format($results['prob_long_wait'], 1) }}%</span>
                        </div>
                    </div>

                    <!-- Vista previa de los datos simulados -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-md font-semibold text-gray-700 mb-4">Muestra de Iteraciones (Primeros 5 clientes)</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2">ID</th>
                                        <th class="px-3 py-2">Llegada</th>
                                        <th class="px-3 py-2">T. Espera</th>
                                        <th class="px-3 py-2">Estado ATM</th>
                                        <th class="px-3 py-2">Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($results['sample_clients'], 0, 5) as $client)
                                        <tr class="border-b">
                                            <td class="px-3 py-2 font-medium text-gray-900">#{{ $client['id'] }}</td>
                                            <td class="px-3 py-2">{{ number_format($client['arrival_time'], 2) }} min</td>
                                            <td class="px-3 py-2">{{ number_format($client['wait_time'], 2) }} min</td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded text-xs {{ $client['atm_failed'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $client['atm_failed'] ? 'Falla' : 'OK' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <span class="px-2 py-0.5 rounded text-xs {{ $client['status'] == 'Atendido' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $client['status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-white p-12 text-center rounded-lg shadow-sm border border-gray-200 text-gray-400">
                        Ajusta los parámetros a la izquierda y presiona "Ejecutar Simulación" para obtener datos.
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>