import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error
import joblib

# Cargar los datos
data = pd.read_csv("data.csv")

# Generar y guardar los mapeos de categorías
mappings = {
    "INSUMO": dict(enumerate(data["INSUMO"].astype("category").cat.categories)),
    "DÍA": dict(enumerate(data["DÍA"].astype("category").cat.categories)),
    "MES": dict(enumerate(data["MES"].astype("category").cat.categories)),
    "PLATO": dict(enumerate(data["PLATO"].astype("category").cat.categories))
}

# Guardar los mapeos invertidos (de valor a número)
inverse_mappings = {key: {v: k for k, v in mapping.items()} for key, mapping in mappings.items()}
joblib.dump(inverse_mappings, "category_mappings.pkl")  # Guardar los mapeos

# Codificar las columnas usando los mapeos
data["INSUMO"] = data["INSUMO"].astype("category").cat.codes
data["DÍA"] = data["DÍA"].astype("category").cat.codes
data["MES"] = data["MES"].astype("category").cat.codes
data["PLATO"] = data["PLATO"].astype("category").cat.codes

# Variables independientes (features) y dependiente (target)
X = data[["INSUMO", "DÍA", "MES", "PLATO", "DESPERDICIO"]]  # Incluimos DESPERDICIO
y = data["CANTIDAD"]

# Dividir los datos en conjunto de entrenamiento y prueba
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Entrenar el modelo
model = RandomForestRegressor(random_state=42)
model.fit(X_train, y_train)

# Evaluar el modelo
y_pred = model.predict(X_test)
mse = mean_squared_error(y_test, y_pred)
print(f"Error cuadrático medio (MSE): {mse}")

# Guardar el modelo entrenado
joblib.dump(model, "modelo_consumo.pkl")
print("Modelo entrenado y guardado como 'modelo_consumo.pkl'")