from fastapi import APIRouter
from app.schemas.probabilidad_schema import ProbabilidadRequest
from app.services.probabilidad_services import calcular_probabilidad_avanzada

router = APIRouter(
    prefix="/probabilidad",
    tags=["probabilidad"]
)

@router.post("/calcular")
def calcular_probabilidad(data: ProbabilidadRequest):
    resultados = calcular_probabilidad_avanzada(
        numero_de_clientes=data.numero_de_clientes,
        simulaciones=data.simulaciones,
        contexto=data.contexto,
        franja_horaria=data.franja_horaria,
        efectivo_inicial=data.efectivo_inicial
    )
    return {
        "parametros_entrada": {
            "numero_de_clientes": data.numero_de_clientes,
            "simulaciones": data.simulaciones,
            "contexto": data.contexto,
            "franja_horaria": data.franja_horaria,
            "efectivo_inicial": data.efectivo_inicial
        },
        **resultados
    }
