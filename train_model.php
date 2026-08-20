import pandas as pd
import numpy as np
import pickle
import re
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler
from sklearn.feature_extraction.text import TfidfVectorizer
import warnings
warnings.filterwarnings('ignore')

print("="*50)
print("TRAINING PRE-ECLAMPSIA RISK MODEL")
print("="*50)

# Create training data with realistic risk levels
data = {
    'systolic_bp': [110, 115, 118, 120, 122, 125, 128, 130, 132, 135, 138, 140, 142, 145, 148, 150, 155, 160],
    'diastolic_bp': [70, 72, 75, 78, 80, 82, 84, 85, 86, 88, 89, 90, 92, 95, 98, 100, 105, 110],
    'gestational_age_weeks': [20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 20, 24, 28, 32, 36, 20, 28, 32],
    'maternal_age_yrs': [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42],
    'diabetes': [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 1, 1, 0, 1, 1],
    'proteinuria': [0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, 1, 1, 2, 0, 1, 2],
    'symptoms': [
        'no symptoms',
        'no symptoms',
        'mild headache',
        'mild headache',
        'headache',
        'headache',
        'headache and swelling',
        'headache and swelling',
        'headache, swelling',
        'headache, swelling, blurred vision',
        'headache',
        'headache and swelling',
        'severe headache, blurred vision',
        'severe headache, blurred vision, abdominal pain',
        'blurred vision, severe headache',
        'mild swelling',
        'severe headache, vision changes',
        'critical symptoms, severe headache'
    ],
    'risk_level': [
        'Low', 'Low', 'Low', 'Low', 'Moderate', 'Moderate', 
        'Moderate', 'Moderate', 'High', 'High', 'Low', 'Moderate', 
        'High', 'High', 'High', 'Low', 'High', 'Critical'
    ]
}

df = pd.DataFrame(data)

print("\n📊 Data loaded:")
print(f"   Total records: {len(df)}")
print(f"\nRisk Level Distribution:")
print(df['risk_level'].value_counts())

# Create target (1 = High/Critical risk, 0 = Low/Moderate)
df['target'] = df['risk_level'].apply(lambda x: 1 if x in ['High', 'Critical'] else 0)

print(f"\nTarget Distribution (1 = Needs Attention):")
print(df['target'].value_counts())

# Clean symptoms text
def clean_text(text):
    text = str(text).lower()
    text = re.sub(r'[^a-zA-Z\s]', '', text)
    return text

df['cleaned_symptoms'] = df['symptoms'].apply(clean_text)

# Prepare features
numerical_features = ['systolic_bp', 'diastolic_bp', 'gestational_age_weeks', 'maternal_age_yrs', 'diabetes']
X_num = df[numerical_features].values
X_protein = df['proteinuria'].values.reshape(-1, 1)

# Convert symptoms text to features (NLP)
vectorizer = TfidfVectorizer(max_features=50, stop_words='english')
X_text = vectorizer.fit_transform(df['cleaned_symptoms']).toarray()

# Combine all features
X = np.hstack([X_num, X_protein, X_text])
y = df['target'].values

print(f"\n📐 Feature matrix: {X.shape[0]} rows, {X.shape[1]} columns")

# Split data
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Scale features
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

# Train model
model = LogisticRegression(max_iter=1000, random_state=42)
model.fit(X_train_scaled, y_train)

# Evaluate
from sklearn.metrics import accuracy_score, classification_report
y_pred = model.predict(X_test_scaled)
accuracy = accuracy_score(y_test, y_pred)

print(f"\n✅ Model Training Complete!")
print(f"   Accuracy: {accuracy:.2%}")

# Save files
pickle.dump(model, open('risk_model.pkl', 'wb'))
pickle.dump(scaler, open('scaler.pkl', 'wb'))
pickle.dump(vectorizer, open('vectorizer.pkl', 'wb'))

print("\n💾 Files saved successfully:")
print("   - risk_model.pkl")
print("   - scaler.pkl")
print("   - vectorizer.pkl")

print("\n" + "="*50)
print("✅ TRAINING COMPLETE! You can now use the symptom checker.")
print("="*50)