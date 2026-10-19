import json
import sys

data = sys.stdin.read()
form = json.loads(data)

# Define options for each dropdown key
category_options = [
    "Serology",
    "Molecular Microbiology",
    "Immunology",
    "General Chemistry",
    "Hematology &Coagulation",
    "Microbiology",
    "Urine Culture & Analysis",
    "Stool Culture & Exam",
    "Tumor Markers",
    "HLA"
]

investigation_options = [
    "Hepatitis B Surface Antigen Screen",
    "Hepatitis B Core Antibody",
    "Hepatitis B Surface Antibody",
    "Hepatitis C Antibody Screen",
    "Hepatitis A Antibody IgG",
    "HIV Ag/Ab Screen",
    "HSV 1/2 IgG/IgM Antibodies",
    "Epstein Barr Virus Antibodies",
    "CMV Antibody IgG",
    "Varicella Zoster Antibody IgG",
    "Rubella IgG",
    "Mumps IgG",
    "Measles IgG",
    "Toxoplasma Ab Screen (IgM)",
    "Toxoplasma Ab Screen (IgG)",
    "Brucella Ab Screen (IgM)",
    "Brucella Ab Screen (IgG)",
    "Strongyloides Antibody IgG",
    "HTLV Ab Screen",
    "Syphilis TP Screen",
    "RPR Quant",
    "Beta 2 Glycoprotein IgM",
    "Beta 2 Glycoprotein IgG",
    "Quantiferon -TB",
    "MDX HBV Quantitative PCR",
    "MDX HCV Quantitative PCR",
    "MDX Cytomegalovirus Quantitative PCR",
    "MDX BK Quantitative PCR",
    "ANA Screen",
    "Antimitochondrial Antibody Screen",
    "Smooth Muscle Antibody Screen",
    "Cardiolipin Antibody IgG",
    "Cardiolipin Antibody IgM",
    "Beta 2 Glycoprotein IgG/IgM",
    "CH50 (Total Complement Activity)",
    "VDRL (Venereal Disease Research Laboratory)",
    "Creatinine Level",
    "Urea Lvl",
    "eGFR (CKD-EPI)",
    "Cystatin C",
    "Measured GFR",
    "Cr Clearance Corr",
    "AST",
    "ALT",
    "Albumin Lvl",
    "Alk Phos",
    "Bili Total",
    "Bili Direct",
    "Protein Total",
    "Gamma Glutamyl Transferase",
    "Glucose Fasting",
    "HbA1c",
    "Calcium Lvl",
    "Phosphate Lvl",
    "Magnesium Lvl",
    "Sodium Lvl",
    "Potassium Lvl",
    "Chloride Lvl",
    "CO2",
    "AGAP",
    "Glucose 1hour post Prandial",
    "Glucose 2 hour post Prandial",
    "Beta hCG Quantitative",
    "MDRD",
    "Cystatin C GFR",
    "Vit D 25 OH Lvl",
    "Protein Electrophoresis Serum",
    "CBC w/Diff",
    "Hemoglobin",
    "Hematocrit",
    "WBC",
    "Platelet Count",
    "Coagulation Screen",
    "Prothrombin Time (PT)",
    "INR",
    "aPTT",
    "Fibrinogen",
    "D‑Dimer",
    "Blood Culture",
    "Sputum's for AFB smear and culture",
    "Peritoneal Fluid Culture",
    "MRSA Culture",
    "CSF Culture",
    "Urine Analysis",
    "Urine Culture",
    "Urine Microscopy",
    "Protein/Creatinine Ratio Urine",
    "24-hour Urine collection for Protein",
    "24 urine oxalate",
    "Urine Cytology",
    "Urine Albumin/Creatinine Ratio",
    "Stool Culture",
    "Ova + Parasites",
    "Clostridium difficile Toxin",
    "Stool Occult Blood",
    "Stool WBC",
    "Fecal Immunochemical Test (FIT)",
    "AFP (Alpha Fetoprotein)",
    "PSA Total",
    "PSA Free",
    "CEA",
    "CA 19-9",
    "CA 125",
    "HLA Antibody Screening  Class I",
    "HLA Antibody Screening  Class II",
    "HLA Antibody Screening  Single Antigen Class I",
    "HLA Antibody Screening  Single Antigen Class II",
    "HLA - Lymphocyte Crossmatch T/B cell"
]

result_options = [
    "Reactive",
    "Non-Reactive",
    "Equivocal",
    "Positive",
    "Negative",
    "Indeterminate",
    "No Growth",
    "Numeric",
    "Negative/Negative",
    "Negative/Positive",
    "Positive/Negative",
    "Positive/Positive"
]

unit_options = [
    "mIU/mL",
    "IU/mL or copies/mL",
    "copies/mL",
    "GPL units",
    "MPL units",
    "U/mL",
    "",
    "mIU/mL",
    "IU/mL or copies/mL",
    "copies/mL",
    "",
    "µmol/L",
    "mmol/L",
    "mL/min",
    "mg/L",
    "",
    "IU/L",
    "",
    "g/L",
    "IU/L",
    "mL/min/1.73 m2",
    "g/dL",
    "x10^9/L",
    "ratio",
    "seconds",
    "g/L",
    "mg/L",
    "cells/HPF",
    "mg/g or mg/mmol",
    "mg/g",
    "ng/mL",
    "U/mL",
    "%"
]

# Helper to create options list
def make_options(labels):
    return [{"label": label, "value": label} for label if label]

# Traverse form to find dropdowns by key and update options
def update_dropdowns(node):
    if isinstance(node, dict):
        if node.get("type") == "dropdown":
            key = node.get("key")
            if key == "dropdown-20e41563-e214-4a82-9a90-7d5b617da3bb":
                node["options"] = make_options(category_options)
            elif key == "dropdown-96fabec4-7f6a-4360-9209-092157d602d8":
                node["options"] = make_options(investigation_options)
            elif key == "dropdown-f861cb2e-5ff1-4e30-9223-8d9e45f6265a":
                node["options"] = make_options(result_options)
            elif key == "dropdown-2d02228f-16c7-4500-b469-5c828d8f67b1":
                node["options"] = make_options(unit_options)
        # Recurse into children if present
        if "children" in node and isinstance(node["children"], list):
            for child in node["children"]:
                update_dropdowns(child)
    elif isinstance(node, list):
        for item in node:
            update_dropdowns(item)

update_dropdowns(form)

print(json.dumps(form, indent=2))
