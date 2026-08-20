#!/usr/bin/env python3
"""
Pre-eclampsia AI Prediction Engine
Uses checklist_model.pkl with clinical safety overrides
"""

import sys
import json
import base64
import os
import warnings
import numpy as np

# Silence warnings
warnings.filterwarnings("ignore")

try:
    import joblib
    HAS_JOBLIB = True
except ImportError:
    HAS_JOBLIB = False
    import pickle

# ============================================
# MODEL LOADING
# ============================================

def load_model_assets():
    """Load the trained machine learning models"""
    possible_paths = [
        os.path.join(os.path.dirname(os.path.abspath(__file__)), 'models'),
        'C:\\xampp\\htdocs\\mothercare\\models',
        '/var/www/html/mothercare/models',
        './models',
        '../models'
    ]
    
    model_data = {
        'checklist_model': None,
        'feature_columns': [],
        'model_loaded': False
    }
    
    for models_dir in possible_paths:
        if not os.path.exists(models_dir):
            continue
            
        checklist_path = os.path.join(models_dir, 'checklist_model.pkl')
        features_path = os.path.join(models_dir, 'features.json')
        
        if os.path.exists(checklist_path):
            try:
                if HAS_JOBLIB:
                    model_data['checklist_model'] = joblib.load(checklist_path)
                else:
                    with open(checklist_path, 'rb') as f:
                        model_data['checklist_model'] = pickle.load(f)
                
                print(f"✅ Loaded model from: {checklist_path}", file=sys.stderr)
                model_data['model_loaded'] = True
                
                if os.path.exists(features_path):
                    with open(features_path, 'r') as f:
                        model_data['feature_columns'] = json.load(f)
                    print(f"✅ Loaded {len(model_data['feature_columns'])} features", file=sys.stderr)
                
                break
                
            except Exception as e:
                print(f"❌ Failed to load model: {e}", file=sys.stderr)
                continue
    
    return model_data

# ============================================
# SMART FEATURE EXTRACTION
# ============================================

def extract_features_from_symptoms(symptoms_input):
    """Extract features with clinical context"""
    if not symptoms_input:
        return [0, 0, 0, 0, 0, 0]
    
    if isinstance(symptoms_input, list):
        text = ' '.join(symptoms_input).lower()
    else:
        text = str(symptoms_input).lower()
    
    # Feature extraction with context
    features = [
        1 if 'fever' in text else 0,
        1 if 'headache' in text else 0,
        1 if 'blurred' in text or 'vision' in text else 0,
        1 if 'abdominal' in text or 'epigastric' in text else 0,
        1 if 'swelling' in text or 'swollen' in text else 0,
        1 if 'shortness' in text or 'breath' in text else 0,
    ]
    
    return features

# ============================================
# CLINICAL RISK ASSESSMENT (WHO GUIDELINES)
# ============================================

def calculate_clinical_risk(systolic_bp, diastolic_bp, proteinuria, symptoms_list):
    """
    Calculate risk based on clinical parameters
    Returns: (risk_level, risk_score, reason)
    """
    s = ' '.join(symptoms_list).lower() if symptoms_list else ''
    
    # Count pre-eclampsia symptoms
    pe_symptoms = ['headache', 'blurred', 'vision', 'abdominal', 'swelling', 'shortness']
    symptom_count = sum(1 for sym in pe_symptoms if sym in s)
    
    # CRITICAL - Immediate danger
    if systolic_bp >= 180 or diastolic_bp >= 120:
        return "Critical", 95, "CRITICAL HYPERTENSION - EMERGENCY"
    
    # SEVERE Hypertension
    if systolic_bp >= 160 or diastolic_bp >= 110:
        if symptom_count >= 2:
            return "High", 85, "SEVERE HYPERTENSION + SYMPTOMS"
        return "High", 75, "SEVERE HYPERTENSION"
    
    # MODERATE Hypertension
    if systolic_bp >= 140 or diastolic_bp >= 90:
        if symptom_count >= 3:
            return "High", 70, "HYPERTENSION + MULTIPLE SYMPTOMS"
        elif symptom_count >= 2:
            return "Moderate", 50, "HYPERTENSION + SYMPTOMS"
        else:
            return "Moderate", 40, "HYPERTENSION (no symptoms)"
    
    # NORMAL BP with symptoms
    if symptom_count >= 4:
        return "Moderate", 45, "MULTIPLE SYMPTOMS (normal BP)"
    elif symptom_count >= 3:
        return "Low", 30, "SEVERAL SYMPTOMS (normal BP)"
    
    # Proteinuria
    if proteinuria in ['Yes', 'Positive', '3+', '4+']:
        if symptom_count >= 2:
            return "Moderate", 55, "PROTEINURIA + SYMPTOMS"
        return "Low", 35, "PROTEINURIA ONLY"
    
    # LOW RISK - Default
    return "Low", 15, "LOW RISK PROFILE"

# ============================================
# ML PREDICTION WITH CONTEXT
# ============================================

def predict_with_checklist(model, features, feature_columns, clinical_info):
    """
    Make prediction using ML model with clinical context
    """
    try:
        # Ensure feature count matches
        if len(features) != len(feature_columns):
            if len(features) < len(feature_columns):
                features = features + [0] * (len(feature_columns) - len(features))
            else:
                features = features[:len(feature_columns)]
        
        features_array = np.array([features])
        
        # Get prediction probabilities
        proba = model.predict_proba(features_array)[0]
        classes = model.classes_
        
        # Get prediction
        max_idx = np.argmax(proba)
        pred_label = str(classes[max_idx])
        confidence = np.max(proba) * 100
        
        # Map to risk score - CONSERVATIVE MAPPING
        risk_map = {
            "Green": 15, 
            "Yellow": 35, 
            "Red": 65,
            "Low": 15, 
            "Moderate": 35, 
            "High": 65, 
            "Critical": 85
        }
        
        ml_risk = risk_map.get(pred_label, 35)
        
        # ADJUST BASED ON CLINICAL CONTEXT
        systolic_bp, diastolic_bp, proteinuria, symptom_count = clinical_info
        
        # BP adjustment - only if clinically significant
        if systolic_bp >= 140 or diastolic_bp >= 90:
            ml_risk = min(ml_risk + 10, 85)
        
        if proteinuria in ['Yes', 'Positive', '3+', '4+']:
            ml_risk = min(ml_risk + 10, 85)
        
        # Multiple symptoms adjustment
        if symptom_count >= 3 and (systolic_bp >= 130 or diastolic_bp >= 85):
            ml_risk = min(ml_risk + 5, 85)
        
        return pred_label, ml_risk, confidence
        
    except Exception as e:
        print(f"❌ Prediction error: {e}", file=sys.stderr)
        return None, None, 0

# ============================================
# COMBINED DECISION ENGINE
# ============================================

def get_final_risk(symptoms_list, systolic_bp, diastolic_bp, proteinuria, model, feature_columns):
    """
    Combine clinical and ML predictions for final risk
    """
    # 1. Get clinical risk
    clinical_level, clinical_risk, clinical_reason = calculate_clinical_risk(
        systolic_bp, diastolic_bp, proteinuria, symptoms_list
    )
    
    print(f"🏥 Clinical: {clinical_level} ({clinical_risk}%) - {clinical_reason}", file=sys.stderr)
    
    # 2. Get ML prediction
    features = extract_features_from_symptoms(symptoms_list)
    symptom_count = sum(features)
    
    clinical_info = (systolic_bp, diastolic_bp, proteinuria, symptom_count)
    ml_level, ml_risk, confidence = predict_with_checklist(
        model, features, feature_columns, clinical_info
    )
    
    if ml_level:
        print(f"🤖 ML: {ml_level} ({ml_risk}%) - {confidence:.1f}% confidence", file=sys.stderr)
    
    # 3. CRITICAL OVERRIDE - Clinical emergency always wins
    if clinical_risk >= 75 or "EMERGENCY" in clinical_reason:
        print(f"🚨 CLINICAL OVERRIDE: Emergency detected!", file=sys.stderr)
        return "Critical", 95
    
    # 4. For MODERATE clinical risk, blend with ML
    if clinical_risk >= 40 and clinical_risk <= 65:
        if ml_level and confidence > 60:
            # Blend clinical + ML
            combined = int((clinical_risk * 0.6) + (ml_risk * 0.4))
            # Ensure we don't over-predict
            combined = min(combined, clinical_risk + 10)
            print(f"🔄 Blended: {combined}%", file=sys.stderr)
            return clinical_level, combined
        else:
            # Trust clinical when ML is uncertain
            return clinical_level, clinical_risk
    
    # 5. For LOW clinical risk, use conservative approach
    if clinical_risk < 40:
        if ml_level and confidence > 70 and ml_risk > clinical_risk + 20:
            # ML sees something clinical missed - but be cautious
            print(f"⚠️ ML higher than clinical, using conservative blend", file=sys.stderr)
            combined = int((clinical_risk + ml_risk) / 2)
            return clinical_level, min(combined, 55)
        else:
            return clinical_level, clinical_risk
    
    # 6. Default: Use clinical if available, else ML
    if clinical_level:
        return clinical_level, clinical_risk
    elif ml_level:
        return ml_level, ml_risk
    else:
        return "Low", 15

# ============================================
# ADVICE GENERATION
# ============================================

def get_advice(risk_score, level, facility=None):
    """Generate advice based on risk level"""
    f = facility if facility else "your nearest health facility"
    
    if risk_score < 20:
        return f"""LOW RISK

Risk Score: {risk_score}%
Level: {level}

✅ Continue routine antenatal care
✅ Monitor blood pressure weekly
✅ Watch for new symptoms
✅ Maintain healthy lifestyle

📅 Next appointment: {f}"""
    
    elif risk_score < 40:
        return f"""LOW-MODERATE RISK

Risk Score: {risk_score}%
Level: {level}

📋 Recommendations:
• Monitor blood pressure regularly
• Watch for warning signs
• Maintain healthy diet (low salt)
• Stay hydrated
• Report any new symptoms

🏥 Follow up within 2-4 weeks"""
    
    elif risk_score < 60:
        return f"""MODERATE RISK

Risk Score: {risk_score}%
Level: {level}

📋 Recommended Actions:
• Check blood pressure DAILY
• Reduce salt intake
• Rest on left side when possible
• Monitor for warning signs
• Keep symptom diary

🏥 Visit {f} within 1-2 weeks"""
    
    elif risk_score < 75:
        return f"""ELEVATED RISK

Risk Score: {risk_score}%
Level: {level}

⚠️ Important Actions:
• Check BP TWICE daily
• Strict bed rest
• Low salt diet
• Monitor fetal movement
• Watch for warning signs

🏥 Contact {f} within 3-5 days"""
    
    else:
        return f"""HIGH RISK - URGENT

Risk Score: {risk_score}%
Level: {level}

🚨 URGENT ACTION REQUIRED:
• Contact {f} TODAY
• Check BP TWICE daily
• Strict bed rest
• Monitor fetal movement
• Do not delay seeking care

🚑 EMERGENCY: Call 112 NOW if:
• Convulsions/seizures
• Severe headache
• Vision changes
• Difficulty breathing
• Severe abdominal pain
• Decreased fetal movement

⚠️ DO NOT WAIT - This requires immediate attention"""

# ============================================
# MAIN PREDICTION ENGINE
# ============================================

def main():
    try:
        if len(sys.argv) < 2:
            raise ValueError("No payload provided")
        
        b64_string = sys.argv[1]
        json_data = base64.b64decode(b64_string).decode('utf-8')
        payload = json.loads(json_data)
        
        print("=" * 60, file=sys.stderr)
        print("🤖 PRE-ECLAMPSIA AI PREDICTION ENGINE", file=sys.stderr)
        print("=" * 60, file=sys.stderr)
        
        # Extract data
        symptoms_input = payload.get('symptoms', [])
        systolic_bp = int(payload.get('systolic_bp', 0))
        diastolic_bp = int(payload.get('diastolic_bp', 0))
        proteinuria = str(payload.get('proteinuria', 'None'))
        facility = payload.get('user_profile', {}).get('nearest_health', None)
        
        # Process symptoms
        if isinstance(symptoms_input, list):
            symptoms_list = [str(s).strip().lower() for s in symptoms_input if s]
        else:
            symptoms_list = [s.strip().lower() for s in str(symptoms_input).split(',') if s.strip()]
        
        print(f"\n📋 Input:", file=sys.stderr)
        print(f"   Symptoms: {symptoms_list}", file=sys.stderr)
        print(f"   BP: {systolic_bp}/{diastolic_bp}", file=sys.stderr)
        print(f"   Proteinuria: {proteinuria}", file=sys.stderr)
        
        # Load model
        assets = load_model_assets()
        
        # Get final risk
        if assets['model_loaded'] and assets['checklist_model']:
            level, risk_score = get_final_risk(
                symptoms_list,
                systolic_bp,
                diastolic_bp,
                proteinuria,
                assets['checklist_model'],
                assets['feature_columns']
            )
            ml_used = True
        else:
            # Fallback to clinical only
            level, risk_score, _ = calculate_clinical_risk(
                systolic_bp, diastolic_bp, proteinuria, symptoms_list
            )
            ml_used = False
            print(f"⚠️ Using clinical-only assessment", file=sys.stderr)
        
        # Generate advice
        advice = get_advice(risk_score, level, facility)
        
        print(f"\n📤 Final Result:", file=sys.stderr)
        print(f"   Level: {level}", file=sys.stderr)
        print(f"   Risk Score: {risk_score}%", file=sys.stderr)
        print("=" * 60, file=sys.stderr)
        
        output = {
            "status": "success",
            "success": True,
            "risk": risk_score,
            "level": level,
            "note": advice,
            "prediction": advice,
            "ml_used": ml_used
        }
        
        print(json.dumps(output))
        
    except Exception as e:
        print(f"❌ Error: {e}", file=sys.stderr)
        import traceback
        traceback.print_exc(file=sys.stderr)
        print(json.dumps({
            "status": "error",
            "success": False,
            "message": str(e),
            "risk": 20,
            "level": "Low",
            "note": "System error - using conservative estimate"
        }))

if __name__ == "__main__":
    main()