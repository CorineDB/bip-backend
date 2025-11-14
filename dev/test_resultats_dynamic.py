#!/usr/bin/env python3
"""
Test pour vérifier que les sections de résultats créent le bon nombre de lignes
selon le resultat_global
"""
import sys
sys.path.insert(0, '/home/unknow/GDIZ/apps/backend_api/scripts')

from generate_appreciation_excel import create_resultats_section
from openpyxl import Workbook

def test_resultat(resultat_global, test_name, expected_lines):
    print(f"\n{'='*60}")
    print(f"Test: {test_name}")
    print(f"resultat_global = '{resultat_global}'")
    print(f"Lignes attendues: {expected_lines}")
    print(f"{'='*60}")

    wb = Workbook()
    sheet = wb.active
    sheet.title = "Test"

    lines_created = create_resultats_section(sheet, row=1, last_question_row=50, resultat_global=resultat_global)
    print(f"Lignes créées: {lines_created}")

    # Compter les lignes qui ont du contenu
    content_rows = 0
    for row_idx in range(1, sheet.max_row + 1):
        cell_value = sheet[f'A{row_idx}'].value
        if cell_value:
            content_rows += 1
            print(f"  Ligne {row_idx}: {str(cell_value)[:80]}...")

    if lines_created == expected_lines:
        print(f"\n  ✅ TEST RÉUSSI - {lines_created} lignes créées")
        return True
    else:
        print(f"\n  ❌ TEST ÉCHOUÉ - Attendu: {expected_lines}, Obtenu: {lines_created}")
        return False

# Tester les 3 cas
tests_passed = 0
tests_total = 3

# Cas 1: passe - 6 lignes (Titre + 3 compteurs + texte résultat + Note conforme)
# Lignes: 1-Titre, 2-Validées, 3-Réservées, 4-Rejetées, 5-6-Résultat, 7-8-Conforme = 8 lignes
tests_passed += test_resultat('passe', "Note conceptuelle conforme (passe)", 8)

# Cas 2: retour - 8 lignes (Titre + 3 compteurs + texte résultat + Avis réservé + Raisons)
# Lignes: 1-Titre, 2-Validées, 3-Réservées, 4-Rejetées, 5-6-Résultat, 7-Avis, 8-9-Raisons = 9 lignes
tests_passed += test_resultat('retour', "Avis réservé (retour)", 9)

# Cas 3: non_accepte - 13 lignes (Titre + 3 compteurs + texte + Non conforme + 3 raisons + Raison(s))
# Lignes: 1-Titre, 2-Val, 3-Rés, 4-Rej, 5-6-Résultat, 7-NonConf, 8-Seules, 9-R1, 10-R2, 11-R3, 12-13-Raisons = 13 lignes
tests_passed += test_resultat('non_accepte', "Note non conforme (non_accepte)", 13)

print(f"\n{'='*60}")
print(f"RÉSUMÉ: {tests_passed}/{tests_total} tests réussis")
print(f"{'='*60}\n")

sys.exit(0 if tests_passed == tests_total else 1)
