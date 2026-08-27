# Proyecto.E

Aplicación para analizar el comportamiento de un cajero automático mediante una simulación de Monte Carlo. El proyecto combina una interfaz web sencilla en Laravel Blade con una API independiente en FastAPI.

## Estado actual

El primer módulo, **estimar probabilidades**, está implementado y probado:

- FastAPI recibe el número de clientes, la probabilidad de fallo y el número de simulaciones.
- FastAPI calcula la probabilidad exacta de al menos una falla y la probabilidad de no tener fallas.
- FastAPI estima la probabilidad mediante Monte Carlo.
- Laravel valida el formulario y consume el endpoint de FastAPI.
- Laravel muestra los resultados en una vista Blade.

Los módulos de simulación de riesgos, confiabilidad y tiempos de espera todavía están pendientes.

## Objetivo

El objetivo es permitir la configuración de un escenario de atención en un cajero automático y mostrar indicadores que ayuden a evaluar su desempeño, entre ellos:

- Tiempo promedio de espera.
- Riesgo o porcentaje de abandono.
- Confiabilidad del cajero (uptime).
- Probabilidad de esperas superiores a cinco minutos.
- Muestra de clientes con sus tiempos de llegada, espera, fallas y estado.

Actualmente, la interfaz de probabilidades utiliza resultados calculados por FastAPI. La simulación completa del cajero todavía se desarrollará en módulos posteriores.

## Tecnologías

- **Laravel 13** y **PHP 8.3 o superior**: aplicación web principal.
- **Blade**: interfaz HTML del módulo actual.
- **Vite**, **Tailwind CSS 4** y **Node.js**: herramientas frontend disponibles, pero opcionales para la vista HTML actual.
- **FastAPI** y **Python**: API independiente para la futura lógica de simulación.
- **MySQL**: base de datos configurada actualmente en el archivo `.env`.

## Requisitos previos

Instala las siguientes herramientas:

- PHP 8.3 o superior con Composer.
- Node.js y npm.
- Python 3.10 o superior recomendado.
- Git, opcional para clonar el repositorio.
- MySQL en ejecución, con una base de datos creada para el proyecto.

## Instalación

### 1. Laravel

Desde la carpeta raíz del proyecto:

```powershell
Set-Location Laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

`npm install` solo es necesario si vas a utilizar o compilar los recursos de Vite, Tailwind o JavaScript. Para la vista Blade sencilla actual no es necesario ejecutar `npm run dev`.

Livewire ya está incluido en `composer.json`, por lo que queda instalado automáticamente con `composer install`. No es necesario ejecutar un comando adicional como `composer require livewire/livewire`.

Si el archivo `.env` ya existe, conserva tu configuración actual y omite `Copy-Item`. En la configuración actual, completa estos valores con los datos de tu instalación de MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

Crea previamente la base de datos en MySQL y luego ejecuta `php artisan migrate`.

Como alternativa para desarrollo local, puedes usar SQLite cambiando `DB_CONNECTION=sqlite` y creando el archivo `database/database.sqlite` antes de ejecutar las migraciones:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate
```

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
php artisan serve --port=8001
```

La aplicación estará disponible en [http://127.0.0.1:8001](http://127.0.0.1:8001).

### Vite, opcional

Ejecuta esto solo si la vista utiliza `@vite(['resources/css/app.css', 'resources/js/app.js'])` o si vas a trabajar en CSS, Tailwind o JavaScript:

```powershell
Set-Location Laravel
npm run dev
```

### API FastAPI

En otra terminal:

```powershell
Set-Location FastAPI
.\venv\Scripts\Activate.ps1
uvicorn main:app --reload
```

La API estará disponible en [http://127.0.0.1:8000](http://127.0.0.1:8000). La documentación interactiva está en [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs).

Los endpoints disponibles actualmente son:

```text
GET /
POST /probabilidad/calcular
```

Ejemplo de entrada para `POST /probabilidad/calcular`:

```json
{
	"numero_de_clientes": 10,
	"probabilidad_de_fallo": 0.03,
	"simulaciones": 10000
}
```

Laravel utiliza `FASTAPI_URL=http://127.0.0.1:8000` para comunicarse con FastAPI. Por eso Laravel se ejecuta en el puerto `8001` y FastAPI en el `8000`.

## Uso

1. Inicia FastAPI en el puerto `8000`.
2. Inicia Laravel en el puerto `8001`.
3. Abre la aplicación Laravel en el navegador.
4. Introduce el número de clientes, la probabilidad de fallo y el número de simulaciones.
5. Pulsa **Calcular** y consulta los resultados.

## Estructura principal

```text
Proyecto.E/
├── FastAPI/
│   ├── main.py                    # Entrada de la API
│   └── app/
│       ├── api/probabilidad.py    # Endpoint de probabilidades
│       ├── schemas/               # Validación de datos
│       └── services/              # Cálculos Monte Carlo
├── Laravel/
│   ├── app/Http/Controllers/ProbabilidadController.php
│   ├── app/Services/FastApiClient.php
│   ├── resources/views/probabilidad/index.blade.php
│   ├── routes/web.php             # Rutas web
│   ├── config/services.php        # URL de FastAPI
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

- Añadir el módulo de simulación de riesgos del cajero.
- Analizar la confiabilidad del sistema.
- Estimar los tiempos de espera y el comportamiento de la cola.
- Crear pruebas automatizadas para FastAPI y Laravel.
- Mejorar la presentación de resultados en la vista Blade.

## Licencia

Este proyecto utiliza la configuración de licencia MIT incluida por Laravel. Confirma o ajusta la licencia según las necesidades del proyecto antes de publicarlo.
