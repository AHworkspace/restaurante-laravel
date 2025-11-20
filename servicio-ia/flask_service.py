from flask import Flask, request, jsonify
import joblib
import subprocess

app = Flask(__name__)

# Cargar el modelo y los mapeos
model = joblib.load("modelo_consumo.pkl")
category_mappings = joblib.load("category_mappings.pkl")


# Endpoint para predecir el consumo de un plato en un mes
@app.route('/predict/month_plato', methods=['POST'])
def predict_month_plato():
    try:
        # Obtener datos JSON
        data = request.get_json()

        # Validar campos requeridos
        if not all(k in data for k in ["insumo", "plato", "mes"]):
            return jsonify({"error": "Faltan campos requeridos: insumo, plato, mes"}), 400

        # Mapeos de insumo, plato y mes (convertir a minúsculas para coincidencia flexible)
        insumo_nombre = data["insumo"].lower().strip()
        plato_nombre = data["plato"].lower().strip()
        mes_nombre = data["mes"].lower().strip()
        
        # Buscar en los mapeos (los mapeos tienen estructura {valor: índice})
        insumo = -1
        plato = -1
        mes = -1
        
        # Buscar insumo (comparar sin importar mayúsculas/minúsculas)
        for key in category_mappings["INSUMO"].keys():
            if key.lower() == insumo_nombre:
                insumo = category_mappings["INSUMO"][key]
                break
        
        # Buscar plato
        for key in category_mappings["PLATO"].keys():
            if key.lower() == plato_nombre:
                plato = category_mappings["PLATO"][key]
                break
        
        # Buscar mes
        for key in category_mappings["MES"].keys():
            if key.lower() == mes_nombre:
                mes = category_mappings["MES"][key]
                break

        # Verificar que las categorías sean válidas
        if insumo == -1 or plato == -1 or mes == -1:
            # Mensaje más detallado
            errores = []
            if insumo == -1:
                errores.append(f"Insumo '{data['insumo']}' no encontrado")
            if plato == -1:
                errores.append(f"Plato '{data['plato']}' no encontrado")
            if mes == -1:
                errores.append(f"Mes '{data['mes']}' no encontrado")
            return jsonify({"error": "El insumo, plato o mes no están reconocidos. " + ". ".join(errores) + ". El modelo necesita ser entrenado con estos datos."}), 400

        # Preparar datos para el modelo
        inputs = [
            insumo,
            -1,  # Día no aplica aquí
            mes,
            plato,
            data.get("desperdicio", 0.5)  # Valor por defecto de desperdicio
        ]

        # Predecir
        prediction = model.predict([inputs])

        return jsonify({
            "insumo": data["insumo"],
            "plato": data["plato"],
            "mes": data["mes"],
            "cantidad_predicha": float(prediction[0])
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


# Endpoint para predecir consumo en un día específico
@app.route('/predict/day', methods=['POST'])
def predict_day():
    try:
        data = request.get_json()
        if not all(k in data for k in ["insumo", "plato", "dia", "mes"]):
            return jsonify({"error": "Faltan campos requeridos: insumo, plato, día, mes"}), 400

        insumo = category_mappings["INSUMO"].get(data["insumo"], -1)
        plato = category_mappings["PLATO"].get(data["plato"], -1)
        dia = category_mappings["DÍA"].get(data["dia"], -1)
        mes = category_mappings["MES"].get(data["mes"], -1)

        if insumo == -1 or plato == -1 or dia == -1 or mes == -1:
            return jsonify({"error": "El insumo, plato, día o mes no están reconocidos"}), 400

        inputs = [
            insumo,
            dia,
            mes,
            plato,
            data.get("desperdicio", 0.5)  # Valor por defecto
        ]

        prediction = model.predict([inputs])

        return jsonify({
            "insumo": data["insumo"],
            "plato": data["plato"],
            "dia": data["dia"],
            "mes": data["mes"],
            "cantidad_predicha": float(prediction[0])
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


# Endpoint para predecir desperdicio por mes
@app.route('/predict/desperdicio', methods=['POST'])
def predict_desperdicio():
    try:
        # Obtener los datos JSON
        data = request.get_json()
        
        # Verificar que los campos requeridos estén presentes
        if not all(k in data for k in ["insumo", "plato", "mes", "cantidad"]):
            return jsonify({"error": "Faltan campos requeridos: insumo, plato, mes, cantidad"}), 400

        # Obtener los valores mapeados (convertir a minúsculas para coincidencia flexible)
        insumo_nombre = data["insumo"].lower().strip()
        plato_nombre = data["plato"].lower().strip()
        mes_nombre = data["mes"].lower().strip()
        
        # Buscar en los mapeos (los mapeos tienen estructura {valor: índice})
        insumo = -1
        plato = -1
        mes = -1
        
        # Buscar insumo (comparar sin importar mayúsculas/minúsculas)
        for key in category_mappings["INSUMO"].keys():
            if key.lower() == insumo_nombre:
                insumo = category_mappings["INSUMO"][key]
                break
        
        # Buscar plato
        for key in category_mappings["PLATO"].keys():
            if key.lower() == plato_nombre:
                plato = category_mappings["PLATO"][key]
                break
        
        # Buscar mes
        for key in category_mappings["MES"].keys():
            if key.lower() == mes_nombre:
                mes = category_mappings["MES"][key]
                break

        # Verificar que las categorías sean válidas
        if insumo == -1 or plato == -1 or mes == -1:
            # Mensaje más detallado
            errores = []
            if insumo == -1:
                errores.append(f"Insumo '{data['insumo']}' no encontrado")
            if plato == -1:
                errores.append(f"Plato '{data['plato']}' no encontrado")
            if mes == -1:
                errores.append(f"Mes '{data['mes']}' no encontrado")
            return jsonify({"error": "El insumo, plato o mes no están reconocidos. " + ". ".join(errores) + ". El modelo necesita ser entrenado con estos datos."}), 400

        # Preparar los datos para la predicción (no es necesario el día)
        inputs = [
            insumo,
            -1,  # Día no se aplica aquí
            mes,
            plato,
            data["cantidad"]  # Usar la cantidad para la predicción
        ]

        # Realizar la predicción
        prediction = model.predict([inputs])

        # Devolver la respuesta con el desperdicio predicho
        return jsonify({
            "insumo": data["insumo"],
            "plato": data["plato"],
            "mes": data["mes"],
            "desperdicio_predicho": float(prediction[0])
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500

    
@app.route('/predict/month_insumo', methods=['POST'])
def predict_month_insumo():
    try:
        # Obtener datos JSON
        data = request.get_json()

        # Validar campos requeridos
        if not all(k in data for k in ["insumo", "mes"]):
            return jsonify({"error": "Faltan campos requeridos: insumo, mes"}), 400

        # Mapeos de insumo y mes (convertir a minúsculas para coincidencia flexible)
        insumo_nombre = data["insumo"].lower().strip()
        mes_nombre = data["mes"].lower().strip()
        
        # Buscar en los mapeos (los mapeos tienen estructura {valor: índice})
        insumo = -1
        mes = -1
        
        # Buscar insumo (comparar sin importar mayúsculas/minúsculas)
        for key in category_mappings["INSUMO"].keys():
            if key.lower() == insumo_nombre:
                insumo = category_mappings["INSUMO"][key]
                break
        
        # Buscar mes
        for key in category_mappings["MES"].keys():
            if key.lower() == mes_nombre:
                mes = category_mappings["MES"][key]
                break

        # Verificar que las categorías sean válidas
        if insumo == -1 or mes == -1:
            # Mensaje más detallado
            errores = []
            if insumo == -1:
                errores.append(f"Insumo '{data['insumo']}' no encontrado")
            if mes == -1:
                errores.append(f"Mes '{data['mes']}' no encontrado")
            return jsonify({"error": "El insumo o mes no están reconocidos. " + ". ".join(errores) + ". El modelo necesita ser entrenado con estos datos."}), 400

        # Preparar datos para el modelo (no aplicamos día ni plato aquí)
        inputs = [
            insumo,
            -1,  # Día no aplica aquí
            mes,
            -1,  # Plato no aplica aquí
            0  # Desperdicio no se usa aquí
        ]

        # Predecir
        prediction = model.predict([inputs])

        return jsonify({
            "insumo": data["insumo"],
            "mes": data["mes"],
            "cantidad_predicha": float(prediction[0])
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500



# Endpoint para reentrenar el modelo con nuevos datos
@app.route('/retrain', methods=['POST'])
def retrain_model():
    global model, category_mappings  # Declarar como globales para poder recargarlos
    try:
        if 'data_file' not in request.files:
            return jsonify({"error": "Falta el archivo de datos"}), 400

        data_file = request.files['data_file']
        data_file.save('data.csv')

        result = subprocess.run(
            ['python', 'train_model.py'],
            capture_output=True,
            text=True
        )

        if result.returncode != 0:
            return jsonify({"error": result.stderr.strip()}), 500

        # Recargar el modelo y los mapeos después del entrenamiento
        try:
            model = joblib.load("modelo_consumo.pkl")
            category_mappings = joblib.load("category_mappings.pkl")
        except Exception as e:
            return jsonify({"error": f"Error al recargar el modelo: {str(e)}"}), 500

        return jsonify({"message": "Modelo reentrenado exitosamente y recargado", "output": result.stdout.strip()})

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == '__main__':
    app.run(debug=True)
