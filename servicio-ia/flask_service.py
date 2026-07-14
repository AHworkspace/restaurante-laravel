from flask import Flask, jsonify, request
import joblib
import subprocess

app = Flask(__name__)

model = joblib.load("modelo_consumo.pkl")
try:
    waste_model = joblib.load("modelo_desperdicio.pkl")
except Exception:
    waste_model = model
category_mappings = joblib.load("category_mappings.pkl")


def normalizar(valor, general=False):
    if valor is None or str(valor).strip() == "":
        return "__GENERAL__" if general else ""
    return str(valor).strip()


def mapa(nombre):
    if nombre == "DIA":
        return category_mappings.get("DIA") or category_mappings.get("DÍA") or category_mappings.get("DÃA") or {}
    return category_mappings.get(nombre, {})


def buscar_categoria(nombre, valor, general=False):
    valor = normalizar(valor, general=general)
    opciones = mapa(nombre)
    if valor in opciones:
        return opciones[valor], None
    for key, indice in opciones.items():
        if str(key).lower() == valor.lower():
            return indice, None
    if general and "__GENERAL__" in opciones:
        return opciones["__GENERAL__"], None
    return -1, f"{nombre.lower()} '{valor}' no encontrado"


def entrada_modelo(data, usar_plato=True, usar_dia=False, usar_desperdicio=True):
    insumo, err_insumo = buscar_categoria("INSUMO", data.get("insumo"))
    presentacion, err_presentacion = buscar_categoria(
        "PRESENTACION",
        data.get("presentacion") or data.get("insumo"),
        general=True,
    )
    dia, err_dia = buscar_categoria("DIA", data.get("dia"), general=True)
    mes, err_mes = buscar_categoria("MES", data.get("mes"))
    plato, err_plato = buscar_categoria("PLATO", data.get("plato"), general=True)

    errores = [e for e in [err_insumo, err_presentacion, err_mes] if e]
    if usar_plato and err_plato:
        errores.append(err_plato)
    if usar_dia and err_dia:
        errores.append(err_dia)
    if errores:
        return None, "El modelo necesita ser reentrenado con estos datos. " + ". ".join(errores)

    desperdicio = float(data.get("desperdicio", data.get("cantidad", 0)) if usar_desperdicio else 0)

    if getattr(model, "n_features_in_", 5) >= 6:
        return [insumo, presentacion, dia, mes, plato, desperdicio], None

    return [insumo, dia, mes, plato, desperdicio], None


@app.route("/predict/month_plato", methods=["POST"])
def predict_month_plato():
    data = request.get_json() or {}
    if not all(k in data for k in ["insumo", "plato", "mes"]):
        return jsonify({"error": "Faltan campos requeridos: insumo, plato, mes"}), 400

    inputs, error = entrada_modelo(data, usar_plato=True, usar_dia=False, usar_desperdicio=True)
    if error:
        return jsonify({"error": error}), 400

    prediction = model.predict([inputs])
    return jsonify({
        "insumo": data["insumo"],
        "presentacion": data.get("presentacion"),
        "plato": data["plato"],
        "mes": data["mes"],
        "cantidad_predicha": float(prediction[0]),
    })


@app.route("/predict/day", methods=["POST"])
def predict_day():
    data = request.get_json() or {}
    if not all(k in data for k in ["insumo", "plato", "dia", "mes"]):
        return jsonify({"error": "Faltan campos requeridos: insumo, plato, dia, mes"}), 400

    inputs, error = entrada_modelo(data, usar_plato=True, usar_dia=True, usar_desperdicio=True)
    if error:
        return jsonify({"error": error}), 400

    prediction = model.predict([inputs])
    return jsonify({
        "insumo": data["insumo"],
        "presentacion": data.get("presentacion"),
        "plato": data["plato"],
        "dia": data["dia"],
        "mes": data["mes"],
        "cantidad_predicha": float(prediction[0]),
    })


@app.route("/predict/desperdicio", methods=["POST"])
def predict_desperdicio():
    data = request.get_json() or {}
    if not all(k in data for k in ["insumo", "plato", "mes", "cantidad"]):
        return jsonify({"error": "Faltan campos requeridos: insumo, plato, mes, cantidad"}), 400

    inputs, error = entrada_modelo(data, usar_plato=True, usar_dia=False, usar_desperdicio=True)
    if error:
        return jsonify({"error": error}), 400

    prediction = waste_model.predict([inputs])
    return jsonify({
        "insumo": data["insumo"],
        "presentacion": data.get("presentacion"),
        "plato": data["plato"],
        "mes": data["mes"],
        "desperdicio_predicho": float(prediction[0]),
    })


@app.route("/predict/month_insumo", methods=["POST"])
def predict_month_insumo():
    data = request.get_json() or {}
    if not all(k in data for k in ["insumo", "mes"]):
        return jsonify({"error": "Faltan campos requeridos: insumo, mes"}), 400

    inputs, error = entrada_modelo(data, usar_plato=False, usar_dia=False, usar_desperdicio=False)
    if error:
        return jsonify({"error": error}), 400

    prediction = model.predict([inputs])
    return jsonify({
        "insumo": data["insumo"],
        "presentacion": data.get("presentacion"),
        "mes": data["mes"],
        "cantidad_predicha": float(prediction[0]),
    })


@app.route("/retrain", methods=["POST"])
def retrain_model():
    global model, waste_model, category_mappings
    try:
        if "data_file" not in request.files:
            return jsonify({"error": "Falta el archivo de datos"}), 400

        request.files["data_file"].save("data.csv")
        result = subprocess.run(["python", "train_model.py"], capture_output=True, text=True)

        if result.returncode != 0:
            return jsonify({"error": result.stderr.strip()}), 500

        model = joblib.load("modelo_consumo.pkl")
        waste_model = joblib.load("modelo_desperdicio.pkl")
        category_mappings = joblib.load("category_mappings.pkl")

        return jsonify({
            "message": "Modelo reentrenado exitosamente y recargado",
            "output": result.stdout.strip(),
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    app.run(debug=True)
