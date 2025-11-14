#!/usr/bin/env python3
"""Vérifier le fichier généré pour la section Planification"""

from openpyxl import load_workbook

file_path = '/home/unknow/GDIZ/apps/backend_api/storage/app/appreciation_planification.xlsx'

wb = load_workbook(file_path)
sheet = wb.active

print("=== FICHIER PLANIFICATION ===\n")

# En-tête
print("--- EN-TÊTE ---")
print(f"B4: {sheet['B4'].value}")
print(f"B5: {sheet['B5'].value}")
print(f"B6: {sheet['B6'].value}")
print(f"B7: {sheet['B7'].value}")
print(f"B8: {sheet['B8'].value}")

# Contenu (lignes 14-25)
print("\n--- CONTENU (lignes 14-25) ---")
for row in range(14, 26):
    a_val = sheet[f'A{row}'].value
    c_val = sheet[f'C{row}'].value
    d_val = sheet[f'D{row}'].value
    e_val = sheet[f'E{row}'].value

    if a_val:
        # Afficher la cellule A
        title = str(a_val)[:60] + "..." if len(str(a_val)) > 60 else str(a_val)
        print(f"\nLigne {row}:")
        print(f"  A{row}: {title}")

        # Vérifier les styles
        a_cell = sheet[f'A{row}']
        fill_color = a_cell.fill.start_color.rgb if a_cell.fill.start_color else 'None'
        bold = a_cell.font.bold if a_cell.font else False
        print(f"  Style A: Bold={bold}, Fill={fill_color}")

    if c_val:
        print(f"  C{row}: {c_val}")
        c_cell = sheet[f'C{row}']
        fill_color = c_cell.fill.start_color.rgb if c_cell.fill.start_color else 'None'
        bold = c_cell.font.bold if c_cell.font else False
        print(f"  Style C: Bold={bold}, Fill={fill_color}")

    if d_val:
        print(f"  D{row}: {str(d_val)[:40]}...")

    if e_val:
        guide_preview = str(e_val)[:80].replace('\n', ' | ')
        print(f"  E{row}: {guide_preview}...")

# Cellules fusionnées
print("\n--- CELLULES FUSIONNÉES (lignes 14-25) ---")
merged_ranges = list(sheet.merged_cells.ranges)
relevant_merges = [str(m) for m in merged_ranges if any(f'{r}' in str(m) for r in range(14, 26))]
for merge in relevant_merges:
    print(f"  - {merge}")

print(f"\nTotal fusions: {len(relevant_merges)}")
print("\n✓ Vérification terminée!")
