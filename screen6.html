<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>Pre-eclampsia Checker - MotherCare</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
    min-height: 100vh;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    max-width: 650px;
    width: 100%;
    background: white;
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

h2 {
    color: #d35400;
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 6px;
}

.subtitle {
    text-align: center;
    color: #b86f2c;
    font-size: 13px;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 1px solid #ffe0b5;
}

/* ========== ENTRY BUTTONS ========== */
.entry-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 0;
    transition: all 0.3s ease;
}

.entry-grid.hidden {
    display: none;
}

.entry-btn {
    padding: 30px 16px;
    border: 2px solid #ffe0b5;
    border-radius: 16px;
    background: #fffef7;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    font-family: inherit;
}

.entry-btn:hover {
    border-color: #e67e22;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(230,126,34,0.12);
}

.entry-btn.active {
    border-color: #e67e22;
    background: #fef5ed;
    box-shadow: 0 4px 16px rgba(230,126,34,0.15);
}

.entry-btn .icon {
    font-size: 40px;
    margin-bottom: 12px;
    display: block;
}

.entry-btn .title {
    font-size: 16px;
    font-weight: 700;
    color: #1a2e1a;
    margin-bottom: 6px;
}

.entry-btn .desc {
    font-size: 12px;
    color: #8a7a6a;
    line-height: 1.4;
    margin-bottom: 8px;
}

.entry-btn .badge {
    display: inline-block;
    background: #e67e22;
    color: white;
    font-size: 9px;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ========== MODE CONTENT ========== */
.mode-content {
    display: none;
    animation: fadeIn 0.4s ease;
    margin-top: 0;
    padding-top: 0;
    border-top: none;
}

.mode-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========== MODE HEADER ========== */
.mode-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #ffe0b5;
}

.mode-header .back-btn {
    background: none;
    border: none;
    color: #e67e22;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    transition: all 0.2s;
}

.mode-header .back-btn:hover {
    background: rgba(230, 126, 34, 0.08);
}

.mode-header .mode-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a2e1a;
}

.mode-header .mode-icon {
    font-size: 24px;
}

.form-section {
    margin-bottom: 16px;
}

.form-section label {
    display: block;
    font-weight: 600;
    color: #e67e22;
    margin-bottom: 6px;
    font-size: 13px;
    letter-spacing: 0.3px;
}

.form-section label .required {
    color: #e74c3c;
    margin-left: 3px;
}

.form-section label .optional {
    color: #95a5a6;
    font-weight: 400;
    font-size: 11px;
    margin-left: 4px;
}

.form-section .hint {
    font-size: 11px;
    color: #b86f2c;
    margin-top: 4px;
}

input, select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ffe0b5;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.2s;
    font-family: inherit;
    background: #fffef7;
}

input:focus, select:focus {
    outline: none;
    border-color: #e67e22;
    box-shadow: 0 0 0 2px rgba(230,126,34,0.1);
}

input:disabled, select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f5f0e6;
}

input.optional-field {
    border-color: #d4d4d4;
    background: #fafafa;
}

input.optional-field:focus {
    border-color: #e67e22;
    background: #fffef7;
}

select[multiple] {
    height: 100px;
}

select[multiple]:disabled {
    background: #f5f0e6;
}

/* Editable fields - changed from readonly-field to editable-field */
.editable-field {
    background: #fffef7;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
    border: 2px solid #ffe0b5;
    width: 100%;
    font-family: inherit;
    transition: all 0.2s;
}

.editable-field:focus {
    outline: none;
    border-color: #e67e22;
    box-shadow: 0 0 0 2px rgba(230,126,34,0.1);
}

.symptom-tags {
    background: #fffef7;
    border-radius: 12px;
    padding: 10px;
    margin: 8px 0;
    min-height: 50px;
    border: 1px solid #ffe0b5;
}

.symptom-tags p {
    font-size: 11px;
    color: #b86f2c;
    margin-bottom: 8px;
}

#symptom-list, #symptom-list-clinic {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    list-style: none;
}

#symptom-list li, #symptom-list-clinic li {
    background: #e67e22;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.button-group {
    display: flex;
    gap: 10px;
    margin: 8px 0;
}

.btn {
    flex: 1;
    padding: 8px 12px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.btn-primary {
    background: #e67e22;
    color: white;
}

.btn-primary:hover {
    background: #d35400;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-success {
    background: #2e7d32;
    color: white;
}

.btn-success:hover {
    background: #1a5a1a;
}

.bp-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.two-col {
    display: flex;
    gap: 15px;
}

.two-col > * {
    flex: 1;
}

/* ========== NLP TEXT INPUT ========== */
.nlp-input {
    display: none;
}

.nlp-input.show {
    display: block;
    animation: fadeIn 0.3s ease;
}

.checkbox-input {
    display: block;
}

.checkbox-input.hidden {
    display: none;
}

textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ffe0b5;
    border-radius: 10px;
    font-size: 12px;
    font-family: inherit;
    resize: none;
    margin: 8px 0;
    background: #fffef7;
    height: 100px;
}

textarea:focus {
    outline: none;
    border-color: #e67e22;
}

/* ========== INPUT TOGGLE ========== */
.input-toggle {
    display: flex;
    gap: 6px;
    margin-bottom: 14px;
    padding: 4px;
    background: #f5f0e6;
    border-radius: 12px;
    border: 1px solid #ffe0b5;
}

.input-btn {
    flex: 1;
    padding: 8px 12px;
    border: none;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    background: transparent;
    color: #8a7a6a;
    font-family: inherit;
    text-align: center;
}

.input-btn i {
    margin-right: 6px;
    font-size: 13px;
}

.input-btn.active {
    background: linear-gradient(135deg, #e67e22, #f39c12);
    color: white;
    box-shadow: 0 2px 8px rgba(230, 126, 34, 0.25);
}

.input-btn:hover:not(.active) {
    color: #d35400;
}

/* ========== SHORT BUTTONS ========== */
.short-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 15px 0;
}

.short-btn {
    flex: 0 1 auto;
    padding: 8px 20px;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.short-btn.reset {
    background: #e74c3c;
    color: white;
}

.short-btn.reset:hover {
    background: #c0392b;
}

.short-btn.back {
    background: #3949ab;
    color: white;
}

.short-btn.back:hover {
    background: #303f9f;
}

.short-btn.logout {
    background: #7f8c8d;
    color: white;
}

.short-btn.logout:hover {
    background: #636e72;
}

/* ========== DISCLAIMER ========== */
.disclaimer {
    font-size: 10px;
    color: #b86f2c;
    text-align: center;
    margin-top: 16px;
    padding: 10px;
    background: #fffef7;
    border-radius: 12px;
    border: 1px solid #ffe0b5;
    line-height: 1.4;
}

/* ========== AI STATUS INDICATOR ========== */
#aiStatus {
    display: none;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 12px;
    font-size: 13px;
    font-weight: 500;
    border: 1px solid;
}

#aiStatus.ai-success {
    display: block;
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

#aiStatus.ai-fallback {
    display: block;
    background: #fff3cd;
    color: #856404;
    border-color: #ffc107;
}

#aiStatus.ai-error {
    display: block;
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

/* ========== RESULTS ========== */
#clinical-note {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e67e22;
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    resize: none;
    margin: 16px 0;
    background: #fffef7;
    height: 200px;
    display: block;
    color: #1a2e1a;
    line-height: 1.6;
}

#clinical-note:focus {
    outline: none;
    border-color: #2e7d32;
    box-shadow: 0 0 0 3px rgba(46,125,50,0.1);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 550px) {
    .entry-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .two-col {
        flex-direction: column;
        gap: 10px;
    }
    
    .short-buttons {
        flex-wrap: wrap;
    }
    
    .short-btn {
        flex: 1;
        text-align: center;
    }
    
    .container {
        padding: 20px;
    }
    
    h2 {
        font-size: 20px;
    }

    .bp-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .entry-btn {
        padding: 24px 16px;
    }

    .entry-btn .icon {
        font-size: 32px;
    }
}

select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23e67e22' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    opacity: 0.5;
}
input[type="number"] {
    -moz-appearance: textfield;
}
</style>
</head>

<body>
<div class="container">

<h2>Pre-eclampsia Checker</h2>
<p class="subtitle">Early detection saves lives</p>

<!-- ENTRY BUTTONS -->
<div class="entry-grid" id="entryGrid">
    <button class="entry-btn" id="selfCheckBtn" onclick="selectMode('self')">
        <span class="icon">🏠</span>
        <div class="title">Self-Assessment</div>
        <div class="desc">Check symptoms from home using what you observe</div>
        <span class="badge">No lab tests needed</span>
    </button>
    <button class="entry-btn" id="facilityCheckBtn" onclick="selectMode('facility')">
        <span class="icon">🏥</span>
        <div class="title">Clinical Assessment</div>
        <div class="desc">Full evaluation with blood pressure and lab results</div>
        <span class="badge">Includes lab tests</span>
    </button>
</div>

<!-- SELF-ASSESSMENT MODE -->
<div class="mode-content" id="selfMode">
    <div class="mode-header">
        <button class="back-btn" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <span class="mode-title">🏠 Self-Assessment</span>
        <span class="mode-icon"></span>
    </div>

    <div class="two-col" style="margin-bottom: 8px;">
        <div class="form-section">
            <label>Weeks pregnant <span class="required">*</span></label>
            <input type="number" class="editable-field" id="gestationalAgeInput" placeholder="Enter weeks" min="4" max="42" step="0.5" value="">
        </div>
        <div class="form-section">
            <label>Your age <span class="required">*</span></label>
            <input type="number" class="editable-field" id="maternalAgeInput" placeholder="Enter age" min="15" max="55" value="">
        </div>
    </div>

    <div class="input-toggle">
        <button class="input-btn active" id="checkboxInput" onclick="switchInput('checkbox')">
            <i class="fas fa-check-square"></i> Select Symptoms
        </button>
        <button class="input-btn" id="nlpInput" onclick="switchInput('nlp')">
            <i class="fas fa-comment-dots"></i> Describe How You Feel
        </button>
    </div>

    <div class="checkbox-input" id="checkboxSection">
        <div class="form-section">
            <label>Select symptoms <span class="optional">(Optional)</span></label>
            <select id="dropdown" multiple size="4">
                <option>Headache</option>
                <option>Swelling</option>
                <option>Blurred vision</option>
                <option>Abdominal pain</option>
                <option>Nausea</option>
            </select>
            <div class="hint">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</div>
        </div>

        <div class="symptom-tags">
            <p>Added symptoms:</p>
            <ul id="symptom-list"></ul>
        </div>

        <div class="button-group">
            <button type="button" id="add" class="btn btn-primary">Add</button>
            <button type="button" id="clear" class="btn btn-secondary">Clear</button>
        </div>
    </div>

    <div class="nlp-input" id="nlpSection">
        <div class="form-section">
            <label>Describe how you feel <span class="optional">(Optional)</span></label>
            <textarea id="nlpText" placeholder="Example: I have a severe headache, my vision is blurry, and I feel very tired..."></textarea>
            <div class="hint">Write in your own words how you are feeling</div>
        </div>
    </div>

    <div class="form-section">
        <label>Blood Pressure <span class="optional">(Optional - improves accuracy)</span></label>
        <div class="bp-row">
            <input type="number" id="systolic_self" placeholder="Systolic (optional)" min="80" max="250" class="optional-field">
            <input type="number" id="diastolic_self" placeholder="Diastolic (optional)" min="50" max="150" class="optional-field">
        </div>
        <div class="hint">Enter if you have a blood pressure reading</div>
    </div>

    <button type="button" id="submitSelf" class="btn btn-success" style="width:100%; margin: 15px 0; padding: 14px; font-size: 16px;">
        <i class="fas fa-heartbeat"></i> Check My Risk
    </button>
</div>

<!-- CLINICAL ASSESSMENT MODE -->
<div class="mode-content" id="facilityMode">
    <div class="mode-header">
        <button class="back-btn" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </button>
        <span class="mode-title">🏥 Clinical Assessment</span>
        <span class="mode-icon"></span>
    </div>

    <div class="two-col" style="margin-bottom: 8px;">
        <div class="form-section">
            <label>Weeks pregnant <span class="required">*</span></label>
            <input type="number" class="editable-field" id="gestationalAgeInput2" placeholder="Enter weeks" min="4" max="42" step="0.5" value="">
        </div>
        <div class="form-section">
            <label>Your age <span class="required">*</span></label>
            <input type="number" class="editable-field" id="maternalAgeInput2" placeholder="Enter age" min="15" max="55" value="">
        </div>
    </div>

    <div class="form-section">
        <label>Blood Pressure <span class="required">*</span></label>
        <div class="bp-row">
            <input type="number" id="systolic_clinic" placeholder="Systolic" min="80" max="250">
            <input type="number" id="diastolic_clinic" placeholder="Diastolic" min="50" max="150">
        </div>
        <div class="hint">mmHg</div>
    </div>

    <div class="form-section">
        <label>Select symptoms <span class="optional">(Optional)</span></label>
        <select id="dropdown_clinic" multiple size="4">
            <option>Headache</option>
            <option>Swelling</option>
            <option>Blurred vision</option>
            <option>Abdominal pain</option>
            <option>Nausea</option>
        </select>
        <div class="hint">Hold Ctrl (Windows) or Cmd (Mac) to select multiple</div>
    </div>

    <div class="symptom-tags">
        <p>Added symptoms:</p>
        <ul id="symptom-list-clinic"></ul>
    </div>

    <div class="button-group">
        <button type="button" id="add_clinic" class="btn btn-primary">Add</button>
        <button type="button" id="clear_clinic" class="btn btn-secondary">Clear</button>
    </div>

    <div class="form-section">
        <label>Proteinuria <span class="required">*</span></label>
        <select id="proteinuria">
            <option value="None">None</option>
            <option value="Trace">Trace</option>
            <option value="Yes">Positive</option>
        </select>
    </div>

    <div class="two-col">
        <div class="form-section">
            <label>Diabetes</label>
            <select id="diabetes">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div class="form-section">
            <label>Previous Pre-eclampsia</label>
            <select id="previousPE">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>

    <div class="two-col">
        <div class="form-section">
            <label>Multiple Pregnancy</label>
            <select id="multiplePregnancy">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div class="form-section">
            <label>Hypertension History</label>
            <select id="hypertension">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
    </div>

    <button type="button" id="submitClinic" class="btn btn-success" style="width:100%; margin: 15px 0; padding: 14px; font-size: 16px;">
        <i class="fas fa-heartbeat"></i> Check My Risk
    </button>
</div>

<!-- AI STATUS INDICATOR -->
<div id="aiStatus"></div>

<!-- RESULTS -->
<div style="margin-top: 10px;">
    <label style="font-weight: 600; color: #e67e22; font-size: 14px; display: block; margin-bottom: 6px;">
        <i class="fas fa-file-medical-alt"></i> Risk Assessment Results
    </label>
    <textarea id="clinical-note" placeholder="Your risk assessment results will appear here after checking your symptoms..."></textarea>
</div>

<!-- SHORT BUTTONS -->
<div class="short-buttons">
    <button type="button" id="reset" class="short-btn reset">Reset</button>
    <button type="button" id="back" class="short-btn back" onclick="window.location.href='dashboard.html'">Back</button>
    <button type="button" id="logout" class="short-btn logout" onclick="window.location.href='screen1.html'">Exit</button>
</div>

<div class="disclaimer">
    ⚠️ This tool is for informational purposes only and does not replace professional medical advice, diagnosis, or treatment. Always consult a qualified healthcare provider.
</div>

</div>

<script>
// ============================================
// STATE
// ============================================
let currentMode = '';
let currentInput = 'checkbox';
let profileData = null;

// ============================================
// GO BACK
// ============================================
function goBack() {
    document.getElementById('selfMode').classList.remove('active');
    document.getElementById('facilityMode').classList.remove('active');
    document.getElementById('entryGrid').classList.remove('hidden');
    document.getElementById('selfCheckBtn').classList.remove('active');
    document.getElementById('facilityCheckBtn').classList.remove('active');
    document.getElementById('clinical-note').value = '';
    document.getElementById('aiStatus').style.display = 'none';
    document.getElementById('aiStatus').className = '';
    currentMode = '';
}

// ============================================
// SELECT MODE
// ============================================
function selectMode(mode) {
    currentMode = mode;
    const selfBtn = document.getElementById('selfCheckBtn');
    const facilityBtn = document.getElementById('facilityCheckBtn');
    const selfMode = document.getElementById('selfMode');
    const facilityMode = document.getElementById('facilityMode');
    const entryGrid = document.getElementById('entryGrid');
    
    entryGrid.classList.add('hidden');
    selfBtn.classList.toggle('active', mode === 'self');
    facilityBtn.classList.toggle('active', mode === 'facility');
    
    if (mode === 'self') {
        selfMode.classList.add('active');
        facilityMode.classList.remove('active');
        switchInput('checkbox');
    } else {
        facilityMode.classList.add('active');
        selfMode.classList.remove('active');
    }
    
    loadUserProfile();
    document.getElementById('clinical-note').value = '';
    document.getElementById('aiStatus').style.display = 'none';
    document.getElementById('aiStatus').className = '';
}

// ============================================
// SWITCH INPUT METHOD
// ============================================
function switchInput(input) {
    currentInput = input;
    
    const checkboxBtn = document.getElementById('checkboxInput');
    const nlpBtn = document.getElementById('nlpInput');
    const checkboxSection = document.getElementById('checkboxSection');
    const nlpSection = document.getElementById('nlpSection');
    const dropdown = document.getElementById('dropdown');
    const addBtn = document.getElementById('add');
    const clearBtn = document.getElementById('clear');
    
    checkboxBtn.classList.remove('active');
    nlpBtn.classList.remove('active');
    checkboxBtn.style.background = 'transparent';
    checkboxBtn.style.color = '#8a7a6a';
    nlpBtn.style.background = 'transparent';
    nlpBtn.style.color = '#8a7a6a';
    
    if (input === 'checkbox') {
        checkboxBtn.classList.add('active');
        checkboxBtn.style.background = 'linear-gradient(135deg, #e67e22, #f39c12)';
        checkboxBtn.style.color = 'white';
        checkboxSection.classList.remove('hidden');
        checkboxSection.classList.add('checkbox-input');
        nlpSection.classList.remove('show');
        nlpSection.classList.add('nlp-input');
        dropdown.disabled = false;
        addBtn.disabled = false;
        clearBtn.disabled = false;
        document.getElementById('nlpText').required = false;
    } else {
        nlpBtn.classList.add('active');
        nlpBtn.style.background = 'linear-gradient(135deg, #e67e22, #f39c12)';
        nlpBtn.style.color = 'white';
        checkboxSection.classList.add('hidden');
        checkboxSection.classList.remove('checkbox-input');
        nlpSection.classList.add('show');
        nlpSection.classList.remove('nlp-input');
        dropdown.disabled = true;
        addBtn.disabled = true;
        clearBtn.disabled = true;
        document.getElementById('nlpText').required = true;
        document.getElementById('symptom-list').innerHTML = '';
        [...dropdown.options].forEach(o => o.selected = false);
    }
    
    document.getElementById('clinical-note').value = '';
    document.getElementById('aiStatus').style.display = 'none';
    document.getElementById('aiStatus').className = '';
}

// ============================================
// GET ELEMENTS
// ============================================
// Self mode - editable fields
const gestationalAgeInput = document.getElementById('gestationalAgeInput');
const maternalAgeInput = document.getElementById('maternalAgeInput');
const dropdown = document.getElementById('dropdown');
const list = document.getElementById('symptom-list');
const addBtn = document.getElementById('add');
const clearBtn = document.getElementById('clear');
const nlpText = document.getElementById('nlpText');

// Clinic mode - editable fields
const gestationalAgeInput2 = document.getElementById('gestationalAgeInput2');
const maternalAgeInput2 = document.getElementById('maternalAgeInput2');
const dropdown_clinic = document.getElementById('dropdown_clinic');
const list_clinic = document.getElementById('symptom-list-clinic');
const addBtn_clinic = document.getElementById('add_clinic');
const clearBtn_clinic = document.getElementById('clear_clinic');

const resetBtn = document.getElementById('reset');
const clinicalNote = document.getElementById('clinical-note');
const aiStatus = document.getElementById('aiStatus');

// ============================================
// LOAD PROFILE - UPDATED FOR EDITABLE FIELDS
// ============================================
async function loadUserProfile() {
    try {
        console.log("Loading user profile...");
        
        const res = await fetch('get_user_profile.php');
        const data = await res.json();
        
        console.log("Profile data received:", data);
        
        if (data.success) {
            profileData = data;
            const age = data.profile?.age;
            const last_period = data.profile?.last_period;
            const nearest_health = data.profile?.nearest_health;
            
            // Update age - editable
            if (age && age > 0) {
                maternalAgeInput.value = age;
                maternalAgeInput2.value = age;
            } else {
                maternalAgeInput.placeholder = 'Enter age';
                maternalAgeInput2.placeholder = 'Enter age';
            }
            
            // Update gestational age - editable
            if (last_period) {
                const lastPeriodDate = new Date(last_period);
                const today = new Date();
                const diffDays = Math.floor((today - lastPeriodDate) / (1000 * 60 * 60 * 24));
                const weeks = Math.floor(diffDays / 7);
                
                if (weeks >= 4 && weeks <= 42) {
                    gestationalAgeInput.value = weeks;
                    gestationalAgeInput2.value = weeks;
                } else {
                    gestationalAgeInput.placeholder = 'Enter weeks (4-42)';
                    gestationalAgeInput2.placeholder = 'Enter weeks (4-42)';
                }
            } else {
                gestationalAgeInput.placeholder = 'Enter weeks (4-42)';
                gestationalAgeInput2.placeholder = 'Enter weeks (4-42)';
            }
            
            // Store facility
            if (nearest_health) {
                window.userFacility = nearest_health;
            }
            
        } else {
            console.error("Error loading profile:", data.error);
            gestationalAgeInput.placeholder = 'Error loading';
            maternalAgeInput.placeholder = 'Error loading';
            gestationalAgeInput2.placeholder = 'Error loading';
            maternalAgeInput2.placeholder = 'Error loading';
        }
    } catch (e) {
        console.error('Error loading profile:', e);
        gestationalAgeInput.placeholder = 'Connection error';
        maternalAgeInput.placeholder = 'Connection error';
        gestationalAgeInput2.placeholder = 'Connection error';
        maternalAgeInput2.placeholder = 'Connection error';
    }
}

// ============================================
// SYMPTOMS - Self Mode
// ============================================
addBtn.onclick = () => {
    const selected = [...dropdown.selectedOptions];
    if (selected.length === 0) {
        // If no symptoms selected, allow empty
        return;
    }
    selected.forEach(o => {
        if (![...list.children].some(li => li.textContent === o.value)) {
            let li = document.createElement("li");
            li.textContent = o.value;
            list.appendChild(li);
        }
    });
    [...dropdown.options].forEach(o => o.selected = false);
};

clearBtn.onclick = () => list.innerHTML = "";

// ============================================
// SYMPTOMS - Clinic Mode
// ============================================
addBtn_clinic.onclick = () => {
    const selected = [...dropdown_clinic.selectedOptions];
    if (selected.length === 0) {
        return;
    }
    selected.forEach(o => {
        if (![...list_clinic.children].some(li => li.textContent === o.value)) {
            let li = document.createElement("li");
            li.textContent = o.value;
            list_clinic.appendChild(li);
        }
    });
    [...dropdown_clinic.options].forEach(o => o.selected = false);
};

clearBtn_clinic.onclick = () => list_clinic.innerHTML = "";

// ============================================
// GET SYMPTOMS
// ============================================
function getSymptoms(mode) {
    if (mode === 'self') {
        if (currentInput === 'checkbox') {
            return [...list.children].map(li => li.textContent);
        } else {
            const text = nlpText.value.trim();
            return text ? [text] : [];
        }
    } else {
        return [...list_clinic.children].map(li => li.textContent);
    }
}

// ============================================
// GET PROFILE VALUES - UPDATED FOR EDITABLE FIELDS
// ============================================
function getProfileValues() {
    const gest = document.getElementById('gestationalAgeInput').value || document.getElementById('gestationalAgeInput2').value;
    const age = document.getElementById('maternalAgeInput').value || document.getElementById('maternalAgeInput2').value;
    
    return {
        gestational_age_weeks: parseFloat(gest) || 0,
        maternal_age_yrs: parseInt(age) || 0
    };
}

// ============================================
// RESET
// ============================================
resetBtn.onclick = () => {
    list.innerHTML = "";
    [...dropdown.options].forEach(o => o.selected = false);
    document.getElementById('systolic_self').value = '';
    document.getElementById('diastolic_self').value = '';
    nlpText.value = '';
    list_clinic.innerHTML = "";
    [...dropdown_clinic.options].forEach(o => o.selected = false);
    document.getElementById('systolic_clinic').value = '';
    document.getElementById('diastolic_clinic').value = '';
    document.getElementById('proteinuria').value = 'None';
    document.getElementById('diabetes').value = '0';
    document.getElementById('previousPE').value = '0';
    document.getElementById('multiplePregnancy').value = '0';
    document.getElementById('hypertension').value = '0';
    clinicalNote.value = '';
    aiStatus.style.display = 'none';
    aiStatus.className = '';
    switchInput('checkbox');
    goBack();
};

// ============================================
// SUBMIT RISK
// ============================================
async function submitRisk(payload) {
    try {
        console.log("Sending payload:", JSON.stringify(payload, null, 2));
        
        const response = await fetch("post_symptom_data.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify(payload)
        });
        
        if (!response.ok) {
            throw new Error("Server returned " + response.status);
        }
        
        const data = await response.json();
        console.log("Response data:", data);
        
        // Check for errors
        if (data.error) {
            clinicalNote.value = "❌ Error: " + data.error;
            aiStatus.className = 'ai-error';
            aiStatus.innerHTML = '⚠️ Error occurred during prediction';
            aiStatus.style.display = 'block';
            return;
        }
        
        // Display AI status
        if (data.ai_used === true) {
            aiStatus.className = 'ai-success';
            aiStatus.innerHTML = '🤖 AI Model Used (86% accuracy)';
            aiStatus.style.display = 'block';
        } else if (data.ai_used === false) {
            aiStatus.className = 'ai-fallback';
            aiStatus.innerHTML = '📋 Rule-Based Prediction (Fallback)';
            aiStatus.style.display = 'block';
        } else if (data.engine === 'AI') {
            aiStatus.className = 'ai-success';
            aiStatus.innerHTML = '🤖 AI Model Used';
            aiStatus.style.display = 'block';
        } else if (data.engine === 'PHP Fallback') {
            aiStatus.className = 'ai-fallback';
            aiStatus.innerHTML = '📋 Rule-Based Prediction (Fallback)';
            aiStatus.style.display = 'block';
        }
        
        // Display results
        if (data.risk !== undefined && data.level !== undefined) {
            let resultText = "";
            resultText += "═══════════════════════════════════\n";
            resultText += "  RISK ASSESSMENT RESULTS\n";
            resultText += "═══════════════════════════════════\n\n";
            
            if (data.ai_used === true || data.engine === 'AI') {
                resultText += "🤖 AI Prediction (86% accuracy)\n\n";
            } else {
                resultText += "📋 Rule-Based Prediction (Fallback)\n\n";
            }
            
            resultText += "📊 Risk Score: " + data.risk + "%\n";
            resultText += "📈 Risk Level: " + data.level + "\n";
            resultText += "🔄 Mode: " + (data.mode || 'N/A') + "\n";
            if (data.bp_reading && data.bp_reading !== "Not measured") {
                resultText += "❤️ Blood Pressure: " + data.bp_reading + "\n";
            }
            resultText += "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            resultText += data.note || "No additional advice available.";
            
            clinicalNote.value = resultText;
            clinicalNote.style.borderColor = 
                data.level === 'Low' ? '#2e7d32' :
                data.level === 'Moderate' ? '#f59e0b' :
                data.level === 'High' ? '#f97316' : '#dc2626';
        } else if (data.status === "success" && data.result) {
            let resultText = "";
            resultText += "═══════════════════════════════════\n";
            resultText += "  RISK ASSESSMENT RESULTS\n";
            resultText += "═══════════════════════════════════\n\n";
            
            resultText += data.engine === "AI" ? "🤖 AI Prediction\n\n" : "📋 Rule-Based Prediction\n\n";
            resultText += data.result;
            
            clinicalNote.value = resultText;
            clinicalNote.style.borderColor = '#2e7d32';
        } else if (data.success === true) {
            let resultText = "";
            resultText += "═══════════════════════════════════\n";
            resultText += "  RISK ASSESSMENT RESULTS\n";
            resultText += "═══════════════════════════════════\n\n";
            resultText += data.note || data.message || data.result || "Assessment completed successfully.";
            clinicalNote.value = resultText;
            clinicalNote.style.borderColor = '#2e7d32';
        } else {
            clinicalNote.value = "⚠️ Received response from server but couldn't parse results.\n\n" + JSON.stringify(data, null, 2);
        }
        
    } catch (error) {
        console.error("Error:", error);
        clinicalNote.value = "❌ Server error. Please try again.\n\n" + error.message;
        aiStatus.className = 'ai-error';
        aiStatus.innerHTML = '❌ Connection error';
        aiStatus.style.display = 'block';
        alert("Server error. Please try again.");
    }
}

// ============================================
// SUBMIT - Self Mode
// ============================================
document.getElementById('submitSelf').onclick = async () => {
    const symptoms = getSymptoms('self');
    // Remove the check that requires symptoms - now optional
    
    // Get values from editable fields
    const gestAge = document.getElementById('gestationalAgeInput').value;
    const matAge = document.getElementById('maternalAgeInput').value;
    
    const gest = parseFloat(gestAge) || 0;
    const age = parseInt(matAge) || 0;
    
    if (!gest || gest <= 0 || gest > 42) {
        alert("Please enter a valid gestational age (4-42 weeks)");
        return;
    }
    if (!age || age <= 0 || age > 55) {
        alert("Please enter a valid maternal age (15-55 years)");
        return;
    }
    
    const systolic = parseInt(document.getElementById('systolic_self').value) || 0;
    const diastolic = parseInt(document.getElementById('diastolic_self').value) || 0;
    
    const payload = { 
        mode: 'home', 
        input_type: currentInput, 
        symptoms: symptoms.length > 0 ? symptoms : ['No symptoms reported'],
        gestational_age_weeks: gest, 
        maternal_age_yrs: age 
    };
    
    if (systolic > 0 && diastolic > 0) {
        if (systolic >= 80 && systolic <= 250 && diastolic >= 50 && diastolic <= 150) {
            payload.systolic_bp = systolic;
            payload.diastolic_bp = diastolic;
        }
    }
    
    await submitRisk(payload);
};

// ============================================
// SUBMIT - Clinic Mode
// ============================================
document.getElementById('submitClinic').onclick = async () => {
    const symptoms = getSymptoms('clinic');
    // Symptoms are optional now
    
    const gestAge = document.getElementById('gestationalAgeInput2').value;
    const matAge = document.getElementById('maternalAgeInput2').value;
    
    const gest = parseFloat(gestAge) || 0;
    const age = parseInt(matAge) || 0;
    
    if (!gest || gest <= 0 || gest > 42) {
        alert("Please enter a valid gestational age (4-42 weeks)");
        return;
    }
    if (!age || age <= 0 || age > 55) {
        alert("Please enter a valid maternal age (15-55 years)");
        return;
    }
    
    const systolic = parseInt(document.getElementById('systolic_clinic').value);
    const diastolic = parseInt(document.getElementById('diastolic_clinic').value);
    
    if (!systolic || systolic < 80 || systolic > 250) {
        alert("Please enter a valid systolic BP (80-250 mmHg)");
        return;
    }
    if (!diastolic || diastolic < 50 || diastolic > 150) {
        alert("Please enter a valid diastolic BP (50-150 mmHg)");
        return;
    }
    
    const payload = {
        mode: 'clinical',
        input_type: 'checkbox',
        symptoms: symptoms.length > 0 ? symptoms : ['No symptoms reported'],
        systolic_bp: systolic,
        diastolic_bp: diastolic,
        proteinuria: document.getElementById('proteinuria').value,
        diabetes: parseInt(document.getElementById('diabetes').value),
        previous_pe: parseInt(document.getElementById('previousPE').value),
        multiple_pregnancy: parseInt(document.getElementById('multiplePregnancy').value),
        hypertension: parseInt(document.getElementById('hypertension').value),
        gestational_age_weeks: gest,
        maternal_age_yrs: age
    };
    
    await submitRisk(payload);
};

// ============================================
// INITIALIZE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    loadUserProfile();
    console.log('Pre-eclampsia Checker loaded.');
    console.log('Symptoms are now optional. Age and Gestational Age are editable.');
});
</script>

</body>
</html>