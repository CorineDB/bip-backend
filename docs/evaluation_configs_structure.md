# Structure de evaluation_configs pour les canevas

Cette documentation décrit la structure JSON stockée dans la colonne `evaluation_configs` du modèle `Document`.

## Structure complète

```json
{
  "appreciations": [
    {
      "value": "passe",
      "label": "Passé",
      "description": "Le critère est satisfaisant",
      "score": 1.0,
      "color": "success",
      "icon": "check-circle",
      "metadata": {}
    },
    {
      "value": "retour",
      "label": "Retour",
      "description": "Nécessite des améliorations",
      "score": 0.5,
      "color": "warning",
      "icon": "arrow-left-circle",
      "metadata": {}
    },
    {
      "value": "non_accepte",
      "label": "Non accepté",
      "description": "Le critère n'est pas acceptable",
      "score": 0.0,
      "color": "danger",
      "icon": "x-circle",
      "metadata": {}
    }
  ],

  "results": [
    {
      "value": "passe",
      "label": "Passé",
      "statut_suivant": "VALIDATION_PROFIL",
      "message": "La présélection a été un succès",
      "actions": ["enregistrer_workflow", "envoyer_notification"],
      "metadata": {
        "type_notification": "success",
        "destinataires": ["redacteur", "dgpd"]
      }
    },
    {
      "value": "retour",
      "label": "Retour",
      "statut_suivant": "R_VALIDATION_NOTE_AMELIORER",
      "message": "Retour pour un travail supplémentaire",
      "actions": ["dupliquer_document", "copier_champs_passes", "enregistrer_workflow"],
      "metadata": {
        "type_notification": "warning",
        "destinataires": ["redacteur"]
      }
    },
    {
      "value": "non_accepte",
      "label": "Non accepté",
      "statut_suivant": "NOTE_CONCEPTUEL",
      "message": "Non accepté - Révision complète nécessaire",
      "actions": ["dupliquer_document", "enregistrer_workflow"],
      "metadata": {
        "type_notification": "error",
        "destinataires": ["redacteur", "superviseur"]
      }
    }
  ],

  "rules": {
    "reference": "Règles internes / SFD-011 / SFD-015",
    "decision_algorithm": "rule_based",
    "evaluation_required_fields": ["champs_obligatoires"],

    "conditions": [
      {
        "priority": 1,
        "name": "Champs obligatoires non évalués",
        "appreciations_concernees": [],
        "condition": {
          "type": "comparison",
          "field": "champs_obligatoires_non_evalues",
          "operator": ">",
          "value": 0
        },
        "result": "non_accepte",
        "message": "Des champs obligatoires n'ont pas été évalués",
        "recommandations": ["Compléter tous les champs obligatoires"]
      },
      {
        "priority": 2,
        "name": "Questions non complétées",
        "appreciations_concernees": [],
        "condition": {
          "type": "comparison",
          "field": "non_evalues",
          "operator": ">",
          "value": 0
        },
        "result": "non_accepte",
        "message": "Des questions n'ont pas été complétées",
        "recommandations": ["Évaluer toutes les questions"]
      },
      {
        "priority": 3,
        "name": "Présence de non accepté",
        "appreciations_concernees": ["non_accepte"],
        "condition": {
          "type": "comparison",
          "field": "count.non_accepte",
          "operator": ">=",
          "value": 1
        },
        "result": "non_accepte",
        "message": "Une ou plusieurs réponses évaluées comme 'Non accepté'",
        "recommandations": ["Revoir complètement les sections marquées comme 'Non accepté'"]
      },
      {
        "priority": 4,
        "name": "Seuil de retours dépassé",
        "appreciations_concernees": ["retour"],
        "condition": {
          "type": "comparison",
          "field": "count.retour",
          "operator": ">=",
          "value": 6
        },
        "result": "non_accepte",
        "message": "Seuil de retours dépassé (6 ou plus)",
        "recommandations": ["Réviser en profondeur le document"]
      },
      {
        "priority": 5,
        "name": "Tous passés",
        "appreciations_concernees": ["passe", "retour"],
        "condition": {
          "type": "and",
          "conditions": [
            {
              "type": "comparison",
              "field": "count.passe",
              "operator": "==",
              "value_field": "total"
            },
            {
              "type": "comparison",
              "field": "count.retour",
              "operator": "==",
              "value": 0
            }
          ]
        },
        "result": "passe",
        "message": "Toutes les questions ont été approuvées",
        "recommandations": []
      },
      {
        "priority": 99,
        "name": "Par défaut - Retour",
        "appreciations_concernees": ["passe", "retour", "non_accepte"],
        "condition": {
          "type": "default"
        },
        "result": "retour",
        "message": "Retour pour un travail supplémentaire",
        "recommandations": ["Améliorer les points marqués comme 'Retour'"]
      }
    ]
  }
}
```

## Types de conditions supportées

### 1. Comparison (Comparaison simple)
```json
{
  "type": "comparison",
  "field": "count.retour",
  "operator": ">=",
  "value": 6
}
```

Opérateurs supportés : `>`, `>=`, `<`, `<=`, `==`, `!=`

### 2. Comparison avec champ dynamique
```json
{
  "type": "comparison",
  "field": "count.passe",
  "operator": "==",
  "value_field": "total"
}
```

### 3. AND (ET logique)
```json
{
  "type": "and",
  "conditions": [
    {"type": "comparison", "field": "...", "operator": "...", "value": ...},
    {"type": "comparison", "field": "...", "operator": "...", "value": ...}
  ]
}
```

### 4. OR (OU logique)
```json
{
  "type": "or",
  "conditions": [
    {"type": "comparison", "field": "...", "operator": "...", "value": ...},
    {"type": "comparison", "field": "...", "operator": "...", "value": ...}
  ]
}
```

### 5. Default (Condition par défaut)
```json
{
  "type": "default"
}
```

## Champ `appreciations_concernees`

Le champ `appreciations_concernees` est **obligatoire** dans chaque condition. Il indique explicitement quelles appréciations (définies dans le tableau `appreciations`) sont concernées par cette condition.

**Objectifs** :
1. **Validation** : Permet de vérifier que les appréciations référencées dans la condition existent bien dans la configuration
2. **Documentation** : Rend la configuration auto-documentée et plus facile à comprendre
3. **Maintenance** : Facilite la détection des incohérences lors de modifications

**Valeurs** :
- Tableau vide `[]` : Si la condition ne porte pas sur des appréciations spécifiques (ex: champs obligatoires, questions non complétées)
- Tableau avec valeurs : Liste des `value` des appréciations concernées (ex: `["passe"]`, `["retour", "non_accepte"]`)

**Exemple** :
```json
{
  "priority": 3,
  "name": "Présence de non accepté",
  "appreciations_concernees": ["non_accepte"],  // Cette condition utilise l'appréciation "non_accepte"
  "condition": {
    "type": "comparison",
    "field": "count.non_accepte",  // Référence explicite à "non_accepte"
    "operator": ">=",
    "value": 1
  },
  "result": "non_accepte"
}
```

**Important** : Les valeurs dans `appreciations_concernees` doivent correspondre exactement aux valeurs `value` définies dans le tableau `appreciations` du canevas.

## Champs disponibles dans les conditions

- `total` : Nombre total de champs
- `non_evalues` : Nombre de champs non évalués
- `champs_obligatoires_non_evalues` : Nombre de champs obligatoires non évalués
- `count.{appreciation}` : Nombre d'appréciations de type {appreciation} (ex: `count.passe`, `count.retour`)
- `percentage.{appreciation}` : Pourcentage d'appréciations de type {appreciation}
- `progression_globale` : Score de progression pondéré (0-100)

## Exemples de configurations

### Exemple 1 : Note Conceptuelle
```json
{
  "appreciations": [
    {"value": "passe", "label": "Passé"},
    {"value": "retour", "label": "Retour"},
    {"value": "non_accepte", "label": "Non accepté"}
  ],
  "rules": {
    "conditions": [
      {
        "priority": 1,
        "name": "Présence de non accepté",
        "appreciations_concernees": ["non_accepte"],
        "condition": {"type": "comparison", "field": "count.non_accepte", "operator": ">=", "value": 1},
        "result": "non_accepte",
        "message": "Une ou plusieurs réponses évaluées comme 'Non accepté'"
      },
      {
        "priority": 2,
        "name": "Seuil de retours dépassé",
        "appreciations_concernees": ["retour"],
        "condition": {"type": "comparison", "field": "count.retour", "operator": ">=", "value": 6},
        "result": "non_accepte",
        "message": "Trop de retours (6 ou plus)"
      },
      {
        "priority": 3,
        "name": "Tous passés",
        "appreciations_concernees": ["passe"],
        "condition": {"type": "comparison", "field": "count.passe", "operator": "==", "value_field": "total"},
        "result": "passe",
        "message": "Toutes les questions approuvées"
      },
      {
        "priority": 99,
        "name": "Par défaut",
        "appreciations_concernees": ["passe", "retour", "non_accepte"],
        "condition": {"type": "default"},
        "result": "retour",
        "message": "Retour pour travail supplémentaire"
      }
    ]
  }
}
```

### Exemple 2 : Contrôle Qualité
```json
{
  "appreciations": [
    {"value": "passable", "label": "Passable"},
    {"value": "renvoyer", "label": "Renvoyer"},
    {"value": "non_accepte", "label": "Non accepté"},
    {"value": "non_applicable", "label": "Non applicable"}
  ],
  "rules": {
    "conditions": [
      {
        "priority": 1,
        "name": "Questions non complétées",
        "appreciations_concernees": [],
        "condition": {"type": "comparison", "field": "non_evalues", "operator": ">", "value": 0},
        "result": "non_accepte",
        "message": "Des questions n'ont pas été complétées"
      },
      {
        "priority": 2,
        "name": "Trop de non accepté",
        "appreciations_concernees": ["non_accepte"],
        "condition": {"type": "comparison", "field": "count.non_accepte", "operator": ">", "value": 2},
        "result": "non_accepte",
        "message": "Plus de 2 critères non acceptés"
      },
      {
        "priority": 3,
        "name": "Trop de renvoyer",
        "appreciations_concernees": ["renvoyer"],
        "condition": {"type": "comparison", "field": "count.renvoyer", "operator": ">", "value": 4},
        "result": "renvoyer",
        "message": "Plus de 4 critères à renvoyer"
      },
      {
        "priority": 4,
        "name": "Tous passables",
        "appreciations_concernees": ["passable"],
        "condition": {"type": "comparison", "field": "count.passable", "operator": "==", "value_field": "total"},
        "result": "passe",
        "message": "Tous les critères sont passables"
      },
      {
        "priority": 99,
        "name": "Par défaut",
        "appreciations_concernees": ["passable", "renvoyer", "non_accepte", "non_applicable"],
        "condition": {"type": "default"},
        "result": "retour",
        "message": "Amélioration nécessaire"
      }
    ]
  }
}
```

## Actions disponibles

- `enregistrer_workflow` : Enregistre dans l'historique du workflow
- `envoyer_notification` : Envoie une notification selon metadata.destinataires
- `dupliquer_document` : Crée une copie du document en brouillon
- `copier_champs_passes` : Copie les champs avec appréciation "passe" dans le nouveau document
- `valider_document` : Marque le document comme validé
- `rejeter_document` : Marque le document comme rejeté
