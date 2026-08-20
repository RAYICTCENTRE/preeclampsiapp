#!/usr/bin/env python3
"""
Train Pre-eclampsia AI Models
Trains on synthetic data with proper feature extraction
"""

import os
import json
import pickle
import pandas as pd
import numpy as np
from sklearn.linear_model import LogisticRegression
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import StandardScaler
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, accuracy_score
import warnings
warnings.filterwarnings('ignore')

print("=" * 60)
print("PRE-ECLAMPSIA AI MODEL TRAINING")
print("=" * 60)

# Load your synthetic dataset
df = pd.read_csv('synthetic_data.csv')
print(f"✅ Loaded {len(df)} records")

# ============================================
# FEATURE ENGINEERING FROM SYMPTOMS TEXT
# ============================================

def extract_symptom_features(symptoms_text):
    """Convert symptom text to binary features"""
    if pd.isna(symptoms_text) or symptoms_text == 'None':
        return {
            'headache': 0,
            'blurred_vision': 0,
            'visual_disturbance': 0,
            'swelling': 0,
            'abdominal_pain': 0,
            'nausea': 0,
            'shortness_of_breath': 0
        }
    
    text = str(symptoms_text).lower()
    
    features = {
        'headache': 1 if 'headache' in text else 0,
        'blurred_vision': 1 if 'blurred' in text or 'vision' in text else 0,
        'visual_disturbance': 1 if 'blurred' in text or 'vision' in text or 'flashing' in text else 0,
        'swelling': 1 if 'swelling' in text or 'swollen' in text else 0,
        'abdominal_pain': 1 if 'abdominal' in text or 'pain' in text else 0,
        'nausea': 1 if 'nausea' in text else 0,
        'shortness_of_breath': 1 if 'shortness' in text or 'breath' in text else 0,
    }
    return features

# Apply feature extraction
symptom_features = df['symptoms'].apply(extract_symptom_features)
symptom_df = pd.DataFrame(symptom_features.tolist())

# Add clinical features
clinical_features = ['systolic_bp', 'diastolic_bp', 'gestational_age_weeks', 
                    'maternal_age_yrs', 'diabetes', 'previous_pe', 
                    'multiple_pregnancy', 'hypertension']

# Handle proteinuria as categorical
proteinuria_map = {
    'None': 0,
    'Trace': 1,
    'Yes': 2,
    'Positive': 2,
    '1+': 2,
    '2+': 2,
    '3+': 3,
    '4+': 3
}
df['proteinuria_encoded'] = df['proteinuria'].map(proteinuria_map).fillna(0)

# Combine all features
feature_cols = list(symptom_df.columns) + clinical_features + ['proteinuria_encoded']
X = pd.concat([symptom_df, df[clinical_features], df['proteinuria_encoded']], axis=1)

# Target variable
y = df['risk_level']

print(f"✅ Feature engineering complete: {len(feature_cols)} features")
print(f"   Features: {', '.join(feature_cols[:5])}...")

# ============================================
# TRAIN ML MODELS
# ============================================

# Split data
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)

print(f"\n📊 Training set: {len(X_train)} records")
print(f"📊 Test set: {len(X_test)} records")

# 1. Logistic Regression (Checklist Model)
print("\n1️⃣ Training Checklist Logistic Regression Model...")
checklist_model = LogisticRegression(max_iter=1000, class_weight='balanced', random_state=42)
checklist_model.fit(X_train, y_train)

y_pred = checklist_model.predict(X_test)
accuracy = accuracy_score(y_test, y_pred)
print(f"   ✅ Accuracy: {accuracy:.3f}")
print(f"\n   Classification Report:\n{classification_report(y_test, y_pred)}")

# 2. Random Forest (Ensemble Model)
print("\n2️⃣ Training Random Forest Model...")
from sklearn.ensemble import RandomForestClassifier
rf_model = RandomForestClassifier(n_estimators=100, max_depth=10, random_state=42, class_weight='balanced')
rf_model.fit(X_train, y_train)

y_pred_rf = rf_model.predict(X_test)
accuracy_rf = accuracy_score(y_test, y_pred_rf)
print(f"   ✅ Accuracy: {accuracy_rf:.3f}")

# 3. NLP Model (Text-based)
print("\n3️⃣ Training NLP Text Model...")
tfidf = TfidfVectorizer(stop_words='english', max_features=100, min_df=2)
X_text = tfidf.fit_transform(df['symptoms'].fillna('').astype(str))

# Split text data
X_text_train, X_text_test, y_text_train, y_text_test = train_test_split(
    X_text, y, test_size=0.2, random_state=42, stratify=y
)

nlp_model = LogisticRegression(max_iter=1000, class_weight='balanced', random_state=42)
nlp_model.fit(X_text_train, y_text_train)

y_text_pred = nlp_model.predict(X_text_test)
accuracy_text = accuracy_score(y_text_test, y_text_pred)
print(f"   ✅ Accuracy: {accuracy_text:.3f}")

# ============================================
# SAVE MODELS AND ASSETS
# ============================================

print("\n💾 Saving model assets...")
models_dir = 'models'
os.makedirs(models_dir, exist_ok=True)

# Save models
with open(os.path.join(models_dir, 'checklist_model.pkl'), 'wb') as f:
    pickle.dump(checklist_model, f)
print(f"   ✅ Saved checklist_model.pkl")

with open(os.path.join(models_dir, 'rf_model.pkl'), 'wb') as f:
    pickle.dump(rf_model, f)
print(f"   ✅ Saved rf_model.pkl")

with open(os.path.join(models_dir, 'nlp_model.pkl'), 'wb') as f:
    pickle.dump(nlp_model, f)
print(f"   ✅ Saved nlp_model.pkl")

with open(os.path.join(models_dir, 'tfidf_vectorizer.pkl'), 'wb') as f:
    pickle.dump(tfidf, f)
print(f"   ✅ Saved tfidf_vectorizer.pkl")

# Save feature columns
with open(os.path.join(models_dir, 'features.json'), 'w') as f:
    json.dump(feature_cols, f)
print(f"   ✅ Saved features.json ({len(feature_cols)} features)")

# Save scaler for clinical features
scaler = StandardScaler()
scaler.fit(X_train[clinical_features + ['proteinuria_encoded']])
with open(os.path.join(models_dir, 'scaler.pkl'), 'wb') as f:
    pickle.dump(scaler, f)
print(f"   ✅ Saved scaler.pkl")

# ============================================
# MODEL EVALUATION SUMMARY
# ============================================

print("\n" + "=" * 60)
print("📊 MODEL PERFORMANCE SUMMARY")
print("=" * 60)

# Combine models predictions for ensemble
ensemble_preds = []
for i in range(len(y_test)):
    preds = [y_pred[i], y_pred_rf[i]]
    # Simple voting
    from collections import Counter
    ensemble_pred = Counter(preds).most_common(1)[0][0]
    ensemble_preds.append(ensemble_pred)

ensemble_accuracy = accuracy_score(y_test, ensemble_preds)
print(f"🤖 Ensemble Accuracy: {ensemble_accuracy:.3f}")

# Risk score mapping
risk_mapping = {'Low': 10, 'Moderate': 45, 'High': 75, 'Critical': 95}
print(f"\n📈 Risk Score Mapping: {risk_mapping}")

print("\n✅ Training complete! Models are ready for deployment.")
print(f"📁 Models saved to: {os.path.abspath(models_dir)}")