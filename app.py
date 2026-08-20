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
        # -------------------------------------------------
        # 1. Receive JSON from the PHP application
        # -------------------------------------------------

        payload = request.get_json(silent=True)

        if not payload:
            return jsonify({
                "status": "error",
                "success": False,
                "message": "No JSON data received"
            }), 400

        # -------------------------------------------------
        # 2. Convert the JSON to Base64
        # -------------------------------------------------
        # predict_ai.py already expects its input as
        # a Base64 command-line argument.
        # -------------------------------------------------

        json_string = json.dumps(payload)

        encoded_payload = base64.b64encode(
            json_string.encode("utf-8")
        ).decode("utf-8")

        # -------------------------------------------------
        # 3. Locate predict_ai.py
        # -------------------------------------------------

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

        # -------------------------------------------------
        # 4. Run the existing AI engine
        # -------------------------------------------------

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

        # -------------------------------------------------
        # 5. Check for Python execution errors
        # -------------------------------------------------

        if result.returncode != 0:

            return jsonify({
                "status": "error",
                "success": False,
                "message": "AI engine failed",
                "details": result.stderr
            }), 500

        # -------------------------------------------------
        # 6. Read the JSON returned by predict_ai.py
        # -------------------------------------------------

        output = result.stdout.strip()

        if not output:
            return jsonify({
                "status": "error",
                "success": False,
                "message": "AI engine returned no result"
            }), 500

        prediction = json.loads(output)

        # -------------------------------------------------
        # 7. Return prediction to PHP
        # -------------------------------------------------

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

    app.run(
        host="127.0.0.1",
        port=5000,
        debug=True
    )