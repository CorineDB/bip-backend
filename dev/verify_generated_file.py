#!/usr/bin/env python3
"""Vérifier le fichier Excel généré pour le projet 112"""

from openpyxl import load_workbook

file_path = '/home/unknow/GDIZ/apps/backend_api/storage/app/private/projets/90ec386312360c44510d5f8e4a7175837a20731745a253c35adfb3e1d642232d/evaluation_ex_ante/etude_profil/note_conceptuelle/appreciation_note_conceptuelle_BIP-2025-D8YMWS.xlsx'

wb = load_workbook(file_path)
sheet = wb.active

print("=== FICHIER GÉNÉRÉ POUR PROJET 112 ===\n")

# Vérifier l'en-tête
print("--- EN-TÊTE ---")
print(f"B4 (Titre): {sheet['B4'].value}")
print(f"B5 (BIP): {sheet['B5'].value}")
print(f"B6 (Coût): {sheet['B6'].value}")

# Vérifier le contenu ajouté (lignes 14-50)
print("\n--- CONTENU GÉNÉRÉ (lignes 14-30) ---")
for row in range(14, 31):
    a_val = sheet[f'A{row}'].value
    c_val = sheet[f'C{row}'].value
    if a_val:
        # Tronquer les longs titres
        title = str(a_val)[:70] + "..." if len(str(a_val)) > 70 else str(a_val)
        print(f"Ligne {row}: {title}")
        if c_val and 'Valider' in str(c_val):
            print(f"          → {c_val}")

# Compter les sections et questions
print("\n--- STATISTIQUES ---")
sections = 0
questions = 0

for row in range(14, 100):
    a_val = sheet[f'A{row}'].value
    c_val = sheet[f'C{row}'].value

    if a_val and not c_val:
        # Probablement une section (pas de validation)
        next_c = sheet[f'C{row+1}'].value
        if not next_c or 'Valider' not in str(next_c):
            sections += 1

    if c_val and 'Valider' in str(c_val):
        questions += 1

print(f"Sections détectées: {sections}")
print(f"Questions détectées: {questions}")

# Vérifier les fusions
merged_ranges = list(sheet.merged_cells.ranges)
relevant_merges = [str(m) for m in merged_ranges if any(f'{r}' in str(m) for r in range(14, 50))]
print(f"\nCellules fusionnées (lignes 14-50): {len(relevant_merges)}")

print("\n✓ Vérification terminée!")
