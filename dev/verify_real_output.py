#!/usr/bin/env python3
"""Vérifier le contenu du fichier Excel généré"""

from openpyxl import load_workbook

output_path = '/home/unknow/GDIZ/apps/backend_api/storage/app/test_real_output.xlsx'

wb = load_workbook(output_path)
sheet = wb.active

print("=== VÉRIFICATION DU FICHIER GÉNÉRÉ ===\n")

# Vérifier l'en-tête
print("--- EN-TÊTE ---")
print(f"B4 (Titre projet): {sheet['B4'].value}")
print(f"B5 (Identifiant BIP): {sheet['B5'].value}")
print(f"B6 (Coût total): {sheet['B6'].value}")
print(f"B7 (Date démarrage): {sheet['B7'].value}")
print(f"B8 (Date achèvement): {sheet['B8'].value}")

# Vérifier les premières lignes ajoutées (14-24)
print("\n--- CONTENU AJOUTÉ (lignes 14-24) ---")
for row in range(14, 25):
    a_val = sheet[f'A{row}'].value
    c_val = sheet[f'C{row}'].value
    if a_val:
        print(f"Ligne {row}: {a_val[:80] if len(str(a_val)) > 80 else a_val}")
        if c_val and 'Valider' in str(c_val):
            print(f"         → Validation: {c_val}")

# Compter les cellules fusionnées dans la zone ajoutée
print("\n--- CELLULES FUSIONNÉES (lignes 14-30) ---")
merged_ranges = list(sheet.merged_cells.ranges)
relevant_merges = [str(m) for m in merged_ranges if any(f'{r}' in str(m) for r in range(14, 31))]
print(f"Nombre de fusions: {len(relevant_merges)}")
for merge in relevant_merges[:10]:  # Afficher les 10 premières
    print(f"  - {merge}")

print("\n✓ Vérification terminée!")
