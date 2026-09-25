# Proyecto estadístico: simulador de cajero automático

Aplicación web para simular la operación de un cajero, consultar el historial de resultados y analizar riesgos de efectivo, fallas y tiempos de espera. Laravel sirve la interfaz y persiste los resultados; FastAPI ejecuta el modelo de Monte Carlo con JAX. OpenRouter genera una explicación en lenguaje natural cuando se configura una clave.

## Requisitos

- PHP 8.3 o superior y Composer.
- Python 3.10 o superior y pip.
- Node.js/npm solo si se van a compilar recursos frontend.
- SQLite (incluido como opción local) o MySQL.

## Instalación

### Laravel

```sh
cd Laravel
composer install
cp .env.example .env
php artisan key:generate
```

Configura `DB_CONNECTION` y, para MySQL, sus valores `DB_*` en `Laravel/.env`. La clave de OpenRouter es opcional; se configura en ese mismo archivo con `OPENROUTER_API_KEY`. Nunca la pongas en código ni en archivos versionados. Después ejecuta:

```sh
php artisan migrate
```

### FastAPI

```sh
cd FastAPI
python -m venv .venv
# Linux/macOS: source .venv/bin/activate
# Windows PowerShell: .venv\Scripts\Activate.ps1
python -m pip install -r requirements.txt
```

Laravel debe tener `FASTAPI_URL` apuntando a la API (por defecto `http://127.0.0.1:8000`).

## Ejecución

En terminales separadas, desde las carpetas respectivas:

```sh
# FastAPI
uvicorn main:app --reload --port 8000
```

```sh
# Laravel
php artisan serve --port=8001
```

Abre <http://127.0.0.1:8001>. La documentación de FastAPI está en <http://127.0.0.1:8000/docs>.

## Funcionalidad

- Simula llegadas, colas, retiros de efectivo y fallas en escenarios de día normal, quincena o festivo, y franjas de mañana, tarde o noche.
- Guarda cada ejecución en la base de datos y muestra un historial con gráficas de espera y riesgo/uptime. La gráfica refleja también los registros que se hayan importado desde Excel.
- Permite exportar e importar el historial en Excel/CSV.
- Genera un informe ejecutivo con OpenRouter si `OPENROUTER_API_KEY` está configurada. Si el servicio no responde, los resultados de la simulación siguen disponibles.

La API limita el tamaño de cada solicitud para mantener acotado el uso de memoria. Cada llamada usa una semilla aleatoria nueva; los resultados son estimaciones de Monte Carlo.
