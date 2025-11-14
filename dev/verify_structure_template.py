#!/usr/bin/env python3
"""Vérifier la structure exacte du template (lignes 23-27)"""

from openpyxl import load_workbook

template_path = '/home/unknow/GDIZ/apps/backend_api/canevas/O-5_Outil_evaluation_de_la_note_conceptuelle.xlsx'

wb = load_workbook(template_path)
sheet = wb.active

print("=== STRUCTURE DU TEMPLATE (lignes 23-27) ===\n")

# Analyser chaque ligne
for row in range(23, 28):
    print(f"--- LIGNE {row} ---")

    # Afficher les valeurs
    for col in ['A', 'B', 'C', 'D', 'E', 'F']:
        cell = sheet[f'{col}{row}']
        val = cell.value if cell.value else "(vide)"
        if len(str(val)) > 40:
            val = str(val)[:40] + "..."
        print(f"  {col}{row}: {val}")

    # Afficher les styles de certaines cellules
    a_cell = sheet[f'A{row}']
    c_cell = sheet[f'C{row}']
    d_cell = sheet[f'D{row}']
    e_cell = sheet[f'E{row}']

    if a_cell.font:
        print(f"  Style A{row}: Bold={a_cell.font.bold}, Fill={a_cell.fill.start_color.rgb if a_cell.fill.start_color else 'None'}")
    if c_cell.font and c_cell.value:
        print(f"  Style C{row}: Bold={c_cell.font.bold}, Fill={c_cell.fill.start_color.rgb if c_cell.fill.start_color else 'None'}")
    if d_cell.fill.start_color:
        print(f"  Style D{row}: Fill={d_cell.fill.start_color.rgb}")

    print()

# Afficher les cellules fusionnées pour lignes 23-27
print("--- CELLULES FUSIONNÉES (lignes 23-27) ---")
merged_ranges = list(sheet.merged_cells.ranges)
relevant_merges = [str(m) for m in merged_ranges if any(f'{r}' in str(m) for r in range(23, 28))]
for merge in relevant_merges:
    print(f"  - {merge}")
