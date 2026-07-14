import joblib
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_squared_error
from sklearn.model_selection import train_test_split

MESES = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
]

DIAS = [
    "__GENERAL__", "Lunes", "Martes", "Miercoles", "Jueves",
    "Viernes", "Sabado", "Domingo",
]


def limpiar_texto(valor):
    if pd.isna(valor):
        return "__GENERAL__"
    return str(valor).strip() or "__GENERAL__"


def categorias_fijas(columna, valores_base=None):
    valores_base = valores_base or []
    datos = [limpiar_texto(v) for v in data[columna].dropna().unique()]
    return list(dict.fromkeys(valores_base + sorted(datos)))


data = pd.read_csv("data.csv", encoding="utf-8-sig")
data = data.rename(columns={"DÍA": "DIA", "DÃA": "DIA"})

if "PRESENTACION" not in data.columns:
    data["PRESENTACION"] = data["INSUMO"]

columnas_requeridas = ["INSUMO", "PRESENTACION", "CANTIDAD", "DIA", "MES", "PLATO", "DESPERDICIO"]
faltantes = [col for col in columnas_requeridas if col not in data.columns]
if faltantes:
    raise ValueError(f"Faltan columnas requeridas en data.csv: {', '.join(faltantes)}")

data = data[columnas_requeridas].copy()
for columna in ["INSUMO", "PRESENTACION", "DIA", "MES", "PLATO"]:
    data[columna] = data[columna].apply(limpiar_texto)

data["CANTIDAD"] = pd.to_numeric(data["CANTIDAD"], errors="coerce")
data["DESPERDICIO"] = pd.to_numeric(data["DESPERDICIO"], errors="coerce").fillna(0)
data = data.dropna(subset=["CANTIDAD"])
data = data[data["CANTIDAD"] > 0]

if data.empty:
    raise ValueError("No hay datos validos para entrenar el modelo.")

categorias = {
    "INSUMO": categorias_fijas("INSUMO"),
    "PRESENTACION": categorias_fijas("PRESENTACION", ["__GENERAL__"]),
    "DIA": categorias_fijas("DIA", DIAS),
    "MES": categorias_fijas("MES", MESES),
    "PLATO": categorias_fijas("PLATO", ["__GENERAL__"]),
}

category_mappings = {
    columna: {valor: indice for indice, valor in enumerate(valores)}
    for columna, valores in categorias.items()
}
joblib.dump(category_mappings, "category_mappings.pkl")

for columna in ["INSUMO", "PRESENTACION", "DIA", "MES", "PLATO"]:
    data[columna] = data[columna].map(category_mappings[columna]).astype(int)

features = ["INSUMO", "PRESENTACION", "DIA", "MES", "PLATO", "DESPERDICIO"]
X = data[features]
y_consumo = data["CANTIDAD"]
y_desperdicio = data["DESPERDICIO"]

model = RandomForestRegressor(random_state=42)
model_desperdicio = RandomForestRegressor(random_state=43)

if len(data) >= 5:
    X_train, X_test, y_train, y_test = train_test_split(X, y_consumo, test_size=0.2, random_state=42)
    model.fit(X_train, y_train)
    y_pred = model.predict(X_test)
    mse = mean_squared_error(y_test, y_pred)
    print(f"Error cuadratico medio (MSE): {mse}")

    X_train_d, X_test_d, y_train_d, y_test_d = train_test_split(X, y_desperdicio, test_size=0.2, random_state=42)
    model_desperdicio.fit(X_train_d, y_train_d)
    y_pred_d = model_desperdicio.predict(X_test_d)
    mse_d = mean_squared_error(y_test_d, y_pred_d)
    print(f"Error cuadratico medio desperdicio (MSE): {mse_d}")
else:
    model.fit(X, y_consumo)
    model_desperdicio.fit(X, y_desperdicio)
    print("Datos limitados: modelo entrenado sin conjunto de prueba.")

joblib.dump(model, "modelo_consumo.pkl")
joblib.dump(model_desperdicio, "modelo_desperdicio.pkl")
print(f"Modelo entrenado con {len(data)} registros.")
print("Meses disponibles:", ", ".join(categorias["MES"]))
print("Modelo guardado como 'modelo_consumo.pkl'")
print("Modelo de desperdicio guardado como 'modelo_desperdicio.pkl'")
