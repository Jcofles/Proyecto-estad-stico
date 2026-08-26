# Proyecto.E

Aplicación para analizar el comportamiento de un cajero automático mediante una simulación de Monte Carlo. El proyecto combina una interfaz web en Laravel y Livewire con una API independiente en FastAPI.

## Objetivo

El objetivo es permitir la configuración de un escenario de atención en un cajero automático y mostrar indicadores que ayuden a evaluar su desempeño, entre ellos:

- Tiempo promedio de espera.
- Riesgo o porcentaje de abandono.
- Confiabilidad del cajero (uptime).
- Probabilidad de esperas superiores a cinco minutos.
- Muestra de clientes con sus tiempos de llegada, espera, fallas y estado.

Actualmente, la interfaz utiliza resultados ficticios para validar la experiencia visual. La conexión entre Laravel y FastAPI queda preparada como siguiente etapa de desarrollo.

## Tecnologías

- **Laravel 13** y **PHP 8.3 o superior**: aplicación web principal.
- **Livewire 4**: componente interactivo de la simulación.
- **Vite**, **Tailwind CSS 4** y **Node.js**: compilación de recursos frontend.
- **FastAPI** y **Python**: API independiente para la futura lógica de simulación.
- **SQLite**: base de datos local predeterminada de Laravel.

## Requisitos previos

Instala las siguientes herramientas:

- PHP 8.3 o superior con Composer.
- Node.js y npm.
- Python 3.10 o superior recomendado.
- Git, opcional para clonar el repositorio.

## Instalación

### 1. Laravel

Desde la carpeta raíz del proyecto:

```powershell
Set-Location Laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate
npm install
```

Si el archivo `.env` ya existe, conserva tu configuración actual y omite `Copy-Item`.

### 2. FastAPI

Desde otra terminal, en la carpeta raíz del proyecto:

```powershell
Set-Location FastAPI
python -m venv venv
.\venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
pip install fastapi uvicorn
```

En PowerShell, si la política de ejecución impide activar el entorno virtual, ejecuta PowerShell como usuario y configura la política correspondiente o usa directamente `venv\Scripts\python.exe`.

## Ejecución

### Aplicación Laravel

En una terminal:

```powershell
Set-Location Laravel
php artisan serve
```

En otra terminal, para compilar y observar los recursos frontend:

```powershell
Set-Location Laravel
npm run dev
```

La aplicación estará disponible en [http://localhost:8000](http://localhost:8000).

### API FastAPI

En otra terminal:

```powershell
Set-Location FastAPI
.\venv\Scripts\Activate.ps1
uvicorn main:app --reload
```

La API estará disponible en [http://127.0.0.1:8000](http://127.0.0.1:8000). El endpoint disponible actualmente es:

```text
GET /
```

Respuesta actual:

```json
{"message":"Hola bb"}
```

> Laravel y FastAPI usan por defecto puertos distintos en los comandos anteriores: Laravel en `8000` y FastAPI en `8000`. Si ejecutas ambos al mismo tiempo, cambia uno de los puertos, por ejemplo: `php artisan serve --port=8001` o `uvicorn main:app --reload --port 8001`.

## Uso

1. Abre la aplicación Laravel en el navegador.
2. Introduce el número de clientes y los parámetros de llegada, servicio, fallas y capacidad máxima de la fila.
3. Pulsa **Ejecutar Simulación**.
4. Consulta las métricas y la muestra de los primeros clientes.

## Estructura principal

```text
Proyecto.E/
├── FastAPI/
│   └── main.py                    # API FastAPI mínima
├── Laravel/
│   ├── app/Livewire/AtmSimulation.php  # Lógica del componente ATM
│   ├── resources/views/livewire/index.blade.php  # Interfaz
│   ├── routes/web.php             # Ruta principal
│   ├── resources/css/             # Estilos Tailwind
│   ├── resources/js/              # JavaScript de frontend
│   ├── database/migrations/       # Migraciones
│   ├── composer.json              # Dependencias PHP
│   └── package.json               # Dependencias frontend
└── README.md
```

## Pruebas

Para ejecutar las pruebas de Laravel:

```powershell
Set-Location Laravel
php artisan test
```

## Próximos pasos sugeridos

- Implementar la simulación Monte Carlo real en FastAPI.
- Crear un endpoint POST que reciba los parámetros del formulario.
- Configurar Laravel/Livewire para consumir ese endpoint.
- Añadir validación de rangos y mensajes de error para los parámetros.
- Incorporar pruebas para la API y para el flujo completo de simulación.

## Licencia

Este proyecto utiliza la configuración de licencia MIT incluida por Laravel. Confirma o ajusta la licencia según las necesidades del proyecto antes de publicarlo.
