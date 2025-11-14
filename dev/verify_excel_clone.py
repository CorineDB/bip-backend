#!/usr/bin/env python3
"""
Script pour vérifier que le clonage Excel a fonctionné correctement
"""

from openpyxl import load_workbook

output_path = '/home/unknow/GDIZ/apps/backend_api/storage/app/test_excel_clone.xlsx'

wb = load_workbook(output_path)
sheet = wb.active

print("=== VÉRIFICATION DU CLONAGE ===\n")

# Vérifier les lignes 30-31 (notre question clonée)
print("--- LIGNE 30 (Titre) ---")
print(f"A30 valeur: '{sheet['A30'].value}'")
print(f"A30 font bold: {sheet['A30'].font.bold}")
print(f"A30 fill color: {sheet['A30'].fill.start_color.rgb if sheet['A30'].fill.start_color else 'None'}")

print(f"\nD30 valeur: '{sheet['D30'].value}'")
print(f"D30 fill color: {sheet['D30'].fill.start_color.rgb if sheet['D30'].fill.start_color else 'None'}")

print(f"\nE30 valeur: '{sheet['E30'].value}'")

print("\n--- LIGNE 31 (Validation) ---")
print(f"C31 valeur: '{sheet['C31'].value}'")
print(f"C31 font bold: {sheet['C31'].font.bold}")
print(f"C31 fill color: {sheet['C31'].fill.start_color.rgb if sheet['C31'].fill.start_color else 'None'}")

print("\n--- CELLULES FUSIONNÉES ---")
merged_ranges = list(sheet.merged_cells.ranges)
target_merges = [str(m) for m in merged_ranges if '30' in str(m) or '31' in str(m)]
print(f"Cellules fusionnées pour lignes 30-31:")
for merge in target_merges:
    print(f"  - {merge}")

print("\n--- COMPARAISON AVEC ORIGINAL (lignes 24-25) ---")
print(f"A24 valeur: '{sheet['A24'].value}'")
print(f"C25 valeur: '{sheet['C25'].value}'")

original_merges = [str(m) for m in merged_ranges if '24' in str(m) or '25' in str(m)]
print(f"\nCellules fusionnées originales (lignes 24-25):")
for merge in original_merges:
    print(f"  - {merge}")

print("\n✓ Vérification terminée!")
