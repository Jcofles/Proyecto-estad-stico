from pydantic import BaseModel, Field


class ProbabilidadRequest(BaseModel):
    numero_de_clientes: int = Field(gt=0)
    probabilidad_de_fallo: float = Field(ge=0, le=1)
    simulaciones: int = Field(gt=0)
