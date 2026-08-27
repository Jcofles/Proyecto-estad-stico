from fastapi import FastAPI

from app.api.probabilidad import router as probabilidad_router

app = FastAPI()
app.include_router(probabilidad_router)

@app.get("/")
def read_root():
    return {"message": "API funcionando correctamente."}

