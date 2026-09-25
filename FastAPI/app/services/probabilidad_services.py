import jax
import jax.numpy as jnp
from functools import partial
import secrets

# ---------------------------------------------------------
# FASE 1: Motor de Cálculo Avanzado (JAX) - Modelos Estocásticos
# ---------------------------------------------------------

# Parámetros base del contexto y franja horaria
# Afectan la tasa de llegada (clientes por hora) y montos de retiro
CONTEXTOS = {
    'dia_normal': {'multiplicador_llegada': 1.0, 'multiplicador_retiro': 1.0, 'prob_falla_base': 0.01},
    'quincena': {'multiplicador_llegada': 2.5, 'multiplicador_retiro': 1.8, 'prob_falla_base': 0.03},
    'festivo': {'multiplicador_llegada': 1.5, 'multiplicador_retiro': 1.2, 'prob_falla_base': 0.015}
}

FRANJAS = {
    'manana': {'multiplicador': 0.8},
    'tarde': {'multiplicador': 1.5},   # Hora pico
    'noche': {'multiplicador': 0.5}
}

@partial(jax.jit, static_argnums=(1, 2))
def simular_cajero_montecarlo(key, simulaciones, numero_de_clientes, prob_falla, retiro_promedio, efectivo_inicial, tasa_llegada, tasa_servicio):
    """
    Simulación integral del cajero automático usando JAX para 10 millones de iteraciones.
    Utiliza el método de Monte Carlo combinado con la ecuación de Lindley para teoría de colas.
    """
    keys = jax.random.split(key, 5)

    # 1. Simulación de tiempos entre llegadas y tiempos de servicio (M/M/1 estocástico)
    # Generamos matrices de shape (numero_de_clientes, simulaciones) para usar jax.lax.scan en la dimensión de clientes
    intervalos_llegada = jax.random.exponential(keys[0], shape=(numero_de_clientes, simulaciones)) / tasa_llegada
    tiempos_llegada = jnp.cumsum(intervalos_llegada, axis=0)
    tiempos_servicio = jax.random.exponential(keys[1], shape=(numero_de_clientes, simulaciones)) / tasa_servicio

    # Ecuación de Lindley iterativa para calcular tiempos de espera en cola
    def iteracion_cola_fin(terminacion_previa, datos_cliente):
        t_llegada, t_servicio = datos_cliente
        inicio_servicio = jnp.maximum(terminacion_previa, t_llegada)
        espera = inicio_servicio - t_llegada
        return inicio_servicio + t_servicio, espera

    # Aplicamos scan a través de los clientes (secuencialmente) de forma paralela para todas las simulaciones
    espera_inicial = jnp.zeros(simulaciones)
    _, tiempos_espera_hist = jax.lax.scan(iteracion_cola_fin, espera_inicial, (tiempos_llegada, tiempos_servicio))

    tiempo_espera_promedio_por_sim = jnp.mean(tiempos_espera_hist, axis=0)
    max_cola_por_sim = jnp.max(tiempos_espera_hist, axis=0)

    # Promedios globales
    tiempo_espera_promedio_global = jnp.mean(tiempo_espera_promedio_por_sim)
    tiempo_espera_maximo_global = jnp.mean(max_cola_por_sim)

    # 2. Riesgo de Cajero (Agotamiento de efectivo y transacciones)
    # Retiros simulados (distribución normal truncada)
    retiros = jax.random.normal(keys[2], shape=(numero_de_clientes, simulaciones)) * (retiro_promedio * 0.4) + retiro_promedio
    retiros = jnp.maximum(10000.0, retiros) # Mínimo retiro (10.000 COP)

    # Consumo acumulado de efectivo por simulación
    efectivo_consumido = jnp.sum(retiros, axis=0)
    simulaciones_sin_efectivo = jnp.sum(efectivo_consumido > efectivo_inicial)
    prob_quedarse_sin_efectivo = simulaciones_sin_efectivo / simulaciones

    # 3. Análisis de Confiabilidad y Fallos
    rand_fallos = jax.random.uniform(keys[3], shape=(numero_de_clientes, simulaciones))
    # Falla del sistema en hora pico (se incrementa la falla si hay muchos retiros en poco tiempo)
    fallos_por_simulacion = jnp.any(rand_fallos < prob_falla, axis=0)
    probabilidad_colapso = jnp.sum(fallos_por_simulacion) / simulaciones

    disponibilidad_uptime = 1.0 - probabilidad_colapso

    return {
        "tiempo_espera_promedio": tiempo_espera_promedio_global,
        "tiempo_espera_maximo": tiempo_espera_maximo_global,
        "probabilidad_sin_efectivo": prob_quedarse_sin_efectivo,
        "probabilidad_colapso": probabilidad_colapso,
        "disponibilidad_uptime": disponibilidad_uptime
    }

def calcular_probabilidad_avanzada(
        numero_de_clientes: int,
        simulaciones: int,
        contexto: str = 'dia_normal',
        franja_horaria: str = 'tarde',
        efectivo_inicial: float = 50000000.0 # 50 millones COP
):
    """
    Función orquestadora que prepara los parámetros estocásticos basados en las reglas de negocio
    y luego invoca el motor JAX compilado.
    """
    conf_contexto = CONTEXTOS.get(contexto, CONTEXTOS['dia_normal'])
    conf_franja = FRANJAS.get(franja_horaria, FRANJAS['tarde'])

    # Cálculo dinámico de variables estocásticas
    # Asumimos una tasa de llegada base de 15 clientes por hora
    tasa_llegada = 15.0 * conf_contexto['multiplicador_llegada'] * conf_franja['multiplicador']

    # Tasa de servicio del cajero: un cliente promedio tarda 2 minutos (30 clientes por hora)
    tasa_servicio = 30.0

    # Probabilidad de falla ajustada por estrés transaccional
    prob_falla = conf_contexto['prob_falla_base'] * conf_franja['multiplicador']

    # Retiro promedio base de 200,000 COP
    retiro_promedio = 200000.0 * conf_contexto['multiplicador_retiro']

    # Inicialización de semilla
    key = jax.random.PRNGKey(secrets.randbits(32))

    # Ejecutamos el motor de Monte Carlo con JAX
    resultados_jax = simular_cajero_montecarlo(
        key,
        simulaciones,
        numero_de_clientes,
        prob_falla,
        retiro_promedio,
        efectivo_inicial,
        tasa_llegada,
        tasa_servicio
    )

    # Extraemos y convertimos resultados a tipos estándar de Python para la API
    return {
        "parametros_calculados": {
            "tasa_llegada_por_hora": float(tasa_llegada),
            "probabilidad_falla_ajustada": float(prob_falla),
            "retiro_promedio_estimado": float(retiro_promedio)
        },
        "metricas_colas": {
            "tiempo_espera_promedio_minutos": float(resultados_jax["tiempo_espera_promedio"] * 60), # convertido a minutos
            "tiempo_espera_maximo_minutos": float(resultados_jax["tiempo_espera_maximo"] * 60)
        },
        "riesgo_cajero": {
            "probabilidad_sin_efectivo": float(resultados_jax["probabilidad_sin_efectivo"]),
            "probabilidad_colapso_transaccional": float(resultados_jax["probabilidad_colapso"]),
            "disponibilidad_operacional_uptime": float(resultados_jax["disponibilidad_uptime"])
        }
    }
