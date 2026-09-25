from typing import Literal
from pydantic import BaseModel, Field, model_validator

class ProbabilidadRequest(BaseModel):
    numero_de_clientes: int = Field(default=100, gt=0, le=10000)
    simulaciones: int = Field(default=10000, gt=0, le=1000000)
    contexto: Literal['dia_normal', 'quincena', 'festivo'] = 'dia_normal'
    franja_horaria: Literal['manana', 'tarde', 'noche'] = 'tarde'
    efectivo_inicial: float = Field(default=50000000.0, ge=1000000)

    @model_validator(mode='after')
    def limitar_tamano_de_simulacion(self):
        if self.numero_de_clientes * self.simulaciones > 2_000_000:
            raise ValueError('El producto de clientes por simulaciones no puede superar 2.000.000.')
        return self
