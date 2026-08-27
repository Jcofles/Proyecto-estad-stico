from fastapi import APIRouter
from app.schemas.probabilidad_schema import ProbabilidadRequest
from app.services.probabilidad_services import calcular_probabilidad as calcular_probabilidad_servicio


router = APIRouter(
    prefix="/probabilidad",
    tags=["probabilidad"]
)


@router.post("/calcular")
def calcular_probabilidad(data: ProbabilidadRequest):
    resultados = calcular_probabilidad_servicio(
        data.numero_de_clientes,
        data.probabilidad_de_fallo,
        data.simulaciones
    )
    return {
        "numero_de_clientes": data.numero_de_clientes,
        "probabilidad_de_fallo": data.probabilidad_de_fallo,
        "simulaciones": data.simulaciones,
        **resultados
    }
