import random

def calcular_probabilidad(
        numero_de_clientes:int,
        probabilidad_de_fallo:float,
        simulaciones:int
):
    fallos_en_simulaciones =0

    for _ in range(simulaciones):
        ocurrio_fallo = False

        for _ in range(numero_de_clientes):
            if random.random() < probabilidad_de_fallo:
                ocurrio_fallo = True
                break

        if ocurrio_fallo:
            fallos_en_simulaciones += 1

    probabilidad_montecarlo = (
        fallos_en_simulaciones / simulaciones
    )
    probabilidad_sin_fallo = (
        (1 - probabilidad_de_fallo) ** numero_de_clientes
    )
    probabilidad_al_menos_un_fallo = (
        1 - probabilidad_sin_fallo
    )

    return {
        "probabilidad_montecarlo": probabilidad_montecarlo,
        "probabilidad_al_menos_un_fallo": probabilidad_al_menos_un_fallo,
        "probabilidad_sin_fallo": probabilidad_sin_fallo

    }
