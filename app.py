from flask import Flask, request, jsonify
import base64
import json
import subprocess
import sys
import os

app = Flask(__name__)


@app.route("/", methods=["GET"])
def home():
    return jsonify({
        "status": "success",
        "message": "MotherCare AI API is running"
    })


@app.route("/predict", methods=["POST"])
def predict():

    try:
        payload = request.get_json(silent=True)

        if not payload:
            return jsonify({
                "status": "error",
                "success": False,
                "message": "No JSON data received"
            }), 400

        # Convert request data to JSON
        json_string = json.dumps(payload)

        # Encode for predict_ai.py
        encoded_payload = base64.b64encode(
            json_string.encode("utf-8")
        ).decode("utf-8")

        # Locate predict_ai.py
        predict_script = os.path.join(
            os.path.dirname(os.path.abspath(__file__)),
            "predict_ai.py"
        )

        if not os.path.exists(predict_script):
            return jsonify({
                "status": "error",
                "success": False,
                "message": "predict_ai.py not found"
            }), 500

        # Run AI engine
        result = subprocess.run(
            [
                sys.executable,
                predict_script,
                encoded_payload
            ],
            capture_output=True,
            text=True,
            timeout=60
        )

        if result.returncode != 0:
            return jsonify({
                "status": "error",
                "success": False,
                "message": "AI engine failed",
                "details": result.stderr
            }), 500

        output = result.stdout.strip()

        if not output:
            return jsonify({
                "status": "error",
                "success": False,
                "message": "AI engine returned no result"
            }), 500

        prediction = json.loads(output)

        return jsonify(prediction)

    except subprocess.TimeoutExpired:

        return jsonify({
            "status": "error",
            "success": False,
            "message": "AI prediction timed out"
        }), 504

    except json.JSONDecodeError:

        return jsonify({
            "status": "error",
            "success": False,
            "message": "AI engine returned invalid JSON"
        }), 500

    except Exception as e:

        return jsonify({
            "status": "error",
            "success": False,
            "message": str(e)
        }), 500


if __name__ == "__main__":

    port = int(os.environ.get("PORT", 5000))

    app.run(
        host="0.0.0.0",
        port=port,
        debug=False
    )
