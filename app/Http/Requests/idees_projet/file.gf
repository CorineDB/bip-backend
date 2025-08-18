{
    "nom": "Fiche idée de projet",
    "description": "Formulaire de création d'une idée de projet",
    "type": "formulaire",
    "sections": [
        {
            "intitule": "Informations Générales",
            "description": "Informations Générales",
            "ordre_affichage": 1,
            "type": "formulaire",
            "champs": [
                {
                    "label": "Titre du projet",
                    "info": "",
                    "attribut": "titre_projet",
                    "placeholder": "Saisissez le titre de votre projet",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "text",
                    "meta_options": {
                        "configs": {
                            "max_length": 255,
                            "min_length": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 255,
                            "min": 1
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Sigle du projet",
                    "info": "",
                    "attribut": "sigle",
                    "placeholder": "Acronyme du projet",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 2,
                    "type_champ": "text",
                    "meta_options": {
                        "configs": {
                            "max_length": 50,
                            "min_length": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 255,
                            "min": 1
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Categorie de projet",
                    "info": "",
                    "attribut": "categorieId",
                    "placeholder": "Nom du ministère de rattachement",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 3,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "max_length": 255,
                            "min_length": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Durée",
                    "info": "",
                    "attribut": "duree",
                    "placeholder": "Ex: 24 mois",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "number",
                    "meta_options": {
                        "configs": {
                            "max": null,
                            "min": 0,
                            "max_length": 100,
                            "min_length": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "integer": true,
                            "max": null,
                            "min": 1
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Coût en euro",
                    "info": "",
                    "attribut": "cout_euro",
                    "placeholder": "0",
                    "is_required": true,
                    "default_value": "0",
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "number",
                    "meta_options": {
                        "configs": {
                            "max": null,
                            "min": 0,
                            "step": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "numeric": true,
                            "max": null,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Coût en dollar canadien",
                    "info": "",
                    "attribut": "cout_dollar_canadien",
                    "placeholder": "0",
                    "is_required": true,
                    "default_value": "0",
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "number",
                    "meta_options": {
                        "configs": {
                            "max": null,
                            "min": 0,
                            "step": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "numeric": true,
                            "max": null,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Coût estimatif du projet",
                    "info": "",
                    "attribut": "cout_estimatif_projet",
                    "placeholder": "0",
                    "is_required": true,
                    "default_value": "0",
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "number",
                    "meta_options": {
                        "configs": {
                            "max": null,
                            "min": 0,
                            "step": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "array": true,
                            "max": 2,
                            "min": 2
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Coût en dollar americain",
                    "info": "",
                    "attribut": "cout_dollar_americain",
                    "placeholder": "0",
                    "is_required": true,
                    "default_value": "0",
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "number",
                    "sectionId": 38,
                    "meta_options": {
                        "configs": {
                            "max": null,
                            "min": 0,
                            "step": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "numeric": true,
                            "max": null,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                }
            ]
        },
        {
            "intitule": "Secteur d'activité et Localisation",
            "description": "Secteur d'activité et Localisation",
            "ordre_affichage": 2,
            "type": "formulaire",
            "champs": [
                {
                    "label": "Grand Secteur",
                    "info": "",
                    "attribut": "grand_secteur",
                    "placeholder": "Choisissez un grand secteur",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": []
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Secteur",
                    "info": "",
                    "attribut": "secteur",
                    "placeholder": "Choisissez un secteur",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 2,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": []
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Sous Secteur",
                    "info": "",
                    "attribut": "secteurId",
                    "placeholder": "Choisissez un sous secteur",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 3,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": []
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Départements",
                    "info": "",
                    "attribut": "departements",
                    "placeholder": "Choisissez un département",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Communes",
                    "info": "",
                    "attribut": "communes",
                    "placeholder": "Choisissez une commune",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Arrondissements",
                    "info": "",
                    "attribut": "arrondissements",
                    "placeholder": "Choisissez un arrondissement",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 6,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": []
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Villages",
                    "info": "",
                    "attribut": "villages",
                    "placeholder": "Selectionnez les villages",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 7,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "multiple": true,
                            "max_length": 255,
                            "min_length": 1
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                }
            ]
        },
        {
            "intitule": "Cadres stratégiques",
            "description": "Cadres stratégiques",
            "ordre_affichage": 3,
            "type": "formulaire",
            "champs": [
                {
                    "label": "Odds",
                    "info": "",
                    "attribut": "odds",
                    "placeholder": "Sélectionnez un ODD",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Cibles",
                    "info": "",
                    "attribut": "cibles",
                    "placeholder": "Sélectionnez les cibles",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 2,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Orientations stratégique",
                    "info": "",
                    "attribut": "orientations_strategiques",
                    "placeholder": "Choisissez une orientation",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 3,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Objectifs stratégique",
                    "info": "",
                    "attribut": "objectifs_strategiques",
                    "placeholder": "Choisissez un objectif",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Résultats stratégique",
                    "info": "",
                    "attribut": "resultats_strategiques",
                    "placeholder": "Choisissez un résultat",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Piliers du pag",
                    "info": "",
                    "attribut": "piliers_pag",
                    "placeholder": "Choisissez les piliers",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 7,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Axes du pag",
                    "info": "",
                    "attribut": "axes_pag",
                    "placeholder": "Choisissez les axes du pags",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 8,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Actions du pag",
                    "info": "",
                    "attribut": "actions_pag",
                    "placeholder": "Choisissez une action",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 9,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                }
            ]
        },
        {
            "intitule": "Financement et Bénéficiaires",
            "description": "Financement et Bénéficiaires",
            "ordre_affichage": 4,
            "type": "formulaire",
            "champs": [
                {
                    "label": "Types de financement",
                    "info": "",
                    "attribut": "types_financement",
                    "placeholder": "Choisissez un type",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Natures du financement",
                    "info": "",
                    "attribut": "natures_financement",
                    "placeholder": "Choisissez une nature",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 2,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Sources de financement",
                    "info": "",
                    "attribut": "sources_financement",
                    "placeholder": "Choisissez une source",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 3,
                    "type_champ": "select",
                    "meta_options": {
                        "configs": {
                            "options": [],
                            "multiple": true
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Public cible",
                    "info": "",
                    "attribut": "public_cible",
                    "placeholder": "Décrivez le public cible du projet",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1000,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Constats majeurs",
                    "info": "",
                    "attribut": "constats_majeurs",
                    "placeholder": "",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1000,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 1000,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Parties prenantes",
                    "info": "",
                    "attribut": "parties_prenantes",
                    "placeholder": "Identifiez les parties prenantes impliquées",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 6,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1000,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "array": true,
                            "max": null,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                }
            ]
        },
        {
            "intitule": "Contexte et Analyse",
            "description": "Contexte et Analyse",
            "ordre_affichage": 5,
            "type": "formulaire",
            "champs": [
                {
                    "label": "Objectif du projet",
                    "info": "",
                    "attribut": "objectif_general",
                    "placeholder": "Décrivez l'objectif principal du projet",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 2000,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 2000,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Objectif Specifiques",
                    "info": "",
                    "attribut": "objectifs_specifiques",
                    "placeholder": "Décrivez l'objectif principal du projet",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 2000,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "array": true,
                            "max": null,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Résultats attendus",
                    "info": "",
                    "attribut": "resultats_attendus",
                    "placeholder": "Décrivez les résultats attendus",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 2,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 2000,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "array": true,
                            "max": null,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Origine du projet",
                    "info": "",
                    "attribut": "origine",
                    "placeholder": "D'où vient l'idée de ce projet ?",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 3,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 1500,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Fondement du projet",
                    "info": "Action de la stratégie/Plan/Programme",
                    "attribut": "fondement",
                    "placeholder": "Sur quoi se base ce projet ?",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 1500,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Situation actuelle",
                    "info": "Problématique et/ou besoins",
                    "attribut": "situation_actuelle",
                    "placeholder": "Décrivez la situation actuelle",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 2000,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 2000,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Situation désirée",
                    "info": "Finalité, Buts",
                    "attribut": "situation_desiree",
                    "placeholder": "Décrivez la situation visée",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 6,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 2000,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 2000,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Contraintes",
                    "info": "",
                    "attribut": "contraintes",
                    "placeholder": "Identifiez les principales contraintes",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 7,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1000,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1000,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                }
            ]
        },
        {
            "intitule": "Description technique et Impacts",
            "description": "Description technique et Impacts",
            "ordre_affichage": 6,
            "type": "formulaire",
            "champs": [
                {
                    "label": "Description générale du projet",
                    "info": "Contexte & objectifs",
                    "attribut": "description_projet",
                    "placeholder": "Description détaillée du projet",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 3000,
                            "min_length": 50
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "max": 3000,
                            "min": 50
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Description des principaux extrants",
                    "info": "Spécifications techniques",
                    "attribut": "description_extrants",
                    "placeholder": "Description des spécifications techniques du projet",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 3000,
                            "min_length": 50
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 5000,
                            "min": 50
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Échéancier des principaux extrants",
                    "info": "Indicateurs de réalisations physiques",
                    "attribut": "echeancier",
                    "placeholder": "Decrivez les indicateurs de réalisations physique",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 1,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 3000,
                            "min_length": 50
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": true,
                            "string": true,
                            "max": 3000,
                            "min": 50
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Caractéristiques techniques",
                    "info": "",
                    "attribut": "caracteristiques_techniques",
                    "placeholder": "Caractéristiques techniques",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 2,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 2000,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 2000,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Impact environnemental",
                    "info": "",
                    "attribut": "impact_environnement",
                    "placeholder": "Impact sur l'environnement",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 3,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Estimation des coûts et benefices",
                    "info": "",
                    "attribut": "estimation_couts",
                    "placeholder": "",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 0
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Aspects organisationnels du projet",
                    "info": "",
                    "attribut": "aspect_organisationnel",
                    "placeholder": "",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 4,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Risques immédiats",
                    "info": "",
                    "attribut": "risques_immediats",
                    "placeholder": "Risques identifiés",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 5,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Conclusions et recommandations",
                    "info": "",
                    "attribut": "conclusions",
                    "placeholder": "Saisissez la conclusion et recommandations",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 6,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Description sommaire",
                    "info": "",
                    "attribut": "sommaire",
                    "placeholder": "Description sommaire",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 6,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                },
                {
                    "label": "Autre solutions alternatives considere et non retenues",
                    "info": "",
                    "attribut": "description",
                    "placeholder": "Autre solutions alternatives",
                    "is_required": false,
                    "default_value": null,
                    "isEvaluated": false,
                    "ordre_affichage": 6,
                    "type_champ": "textarea",
                    "meta_options": {
                        "configs": {
                            "max_length": 1500,
                            "min_length": 10
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "required": false,
                            "string": true,
                            "max": 1500,
                            "min": 10
                        }
                    },
                    "champ_standard": true,
                    "startWithNewLine": null
                }
            ]
        }
    ]

    /*
        "sections": [
            {
                "intitule": "Informations Générales",
                "ordre_affichage": 1,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Titre du projet",
                        "info": "",
                        "attribut": "titre_projet",
                        "placeholder": "Saisissez le titre de votre projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "text",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Sigle du projet",
                        "info": "",
                        "attribut": "sigle",
                        "placeholder": "Acronyme du projet",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "text",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max_length": 50,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Categorie de projet",
                        "info": "",
                        "attribut": "categorieId",
                        "placeholder": "Nom du ministère de rattachement",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "select",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Durée",
                        "info": "",
                        "attribut": "duree",
                        "placeholder": "Ex: 24 mois",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "number",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max_length": 100,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Coût estimatif du projet",
                        "info": "",
                        "attribut": "cout_estimatif_projet",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Coût en dollar americain",
                        "info": "",
                        "attribut": "cout_dollar_americain",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Coût en euro",
                        "info": "",
                        "attribut": "cout_euro",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Coût en dollar canadien",
                        "info": "",
                        "attribut": "cout_dollar_canadien",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 38,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Secteur d 'activité et Localisation",
                "ordre_affichage": 2,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Arrondissement",
                        "info": "",
                        "attribut": "arrondissements",
                        "placeholder": "Choisissez un arrondissement",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "villages",
                        "info": "",
                        "attribut": "villages",
                        "placeholder": "Selectionnez les villages",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 7,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "multiple": true,
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Grand Secteur",
                        "info": "",
                        "attribut": "grand_secteur",
                        "placeholder": "Choisissez un grand secteur",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Secteur",
                        "info": "",
                        "attribut": "secteur",
                        "placeholder": "Choisissez un secteur",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Sous-Secteur",
                        "info": "",
                        "attribut": "secteurId",
                        "placeholder": "Choisissez un sous-secteur",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Département",
                        "info": "",
                        "attribut": "departements",
                        "placeholder": "Choisissez un département",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Commune",
                        "info": "",
                        "attribut": "communes",
                        "placeholder": "Choisissez une commune",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "select",
                        "sectionId": 39,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Cadres stratégiques",
                "ordre_affichage": 3,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Objectif stratégique",
                        "info": "",
                        "attribut": "objectifs_strategiques",
                        "placeholder": "Choisissez un objectif",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Résultat stratégique",
                        "info": "",
                        "attribut": "resultats_strategiques",
                        "placeholder": "Choisissez un résultat",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Axes du pag",
                        "info": "",
                        "attribut": "axes_pag",
                        "placeholder": "Choisissez les axes du pags",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 8,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Actions du pag",
                        "info": "",
                        "attribut": "actions_pag",
                        "placeholder": "Choisissez une action",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 9,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "ODD",
                        "info": "",
                        "attribut": "odds",
                        "placeholder": "Sélectionnez un ODD",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Piliers du pag",
                        "info": "",
                        "attribut": "piliers_pag",
                        "placeholder": "Choisissez les piliers",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 7,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Orientation stratégique",
                        "info": "",
                        "attribut": "orientations_strategiques",
                        "placeholder": "Choisissez une orientation",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Cibles",
                        "info": "",
                        "attribut": "cibles",
                        "placeholder": "Sélectionnez les cibles",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "select",
                        "sectionId": 40,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Financement et Bénéficiaires",
                "ordre_affichage": 4,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Source de financement",
                        "info": "",
                        "attribut": "sources_financement",
                        "placeholder": "Choisissez une source",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "select",
                        "sectionId": 41,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Public cible",
                        "info": "",
                        "attribut": "public_cible",
                        "placeholder": "Décrivez le public cible du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 41,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Parties prenantes",
                        "info": "",
                        "attribut": "parties_prenantes",
                        "placeholder": "Identifiez les parties prenantes impliquées",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 41,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Constats majeurs",
                        "info": "",
                        "attribut": "constats_majeurs",
                        "placeholder": "",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "textarea",
                        "sectionId": 41,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Types de financement",
                        "info": "",
                        "attribut": "types_financement",
                        "placeholder": "Choisissez un type",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "select",
                        "sectionId": 41,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Nature du financement",
                        "info": "",
                        "attribut": "natures_financement",
                        "placeholder": "Choisissez une nature",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "select",
                        "sectionId": 41,
                        "meta_options": {
                            "configs": {
                                "options": [],
                                "multiple": true
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Contexte et Analyse",
                "ordre_affichage": 5,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Fondement du projet",
                        "info": "",
                        "attribut": "fondement",
                        "placeholder": "Sur quoi se base ce projet ?",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Situation actuelle",
                        "info": "",
                        "attribut": "situation_actuelle",
                        "placeholder": "Décrivez la situation actuelle",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Situation désirée",
                        "info": "",
                        "attribut": "situation_desiree",
                        "placeholder": "Décrivez la situation visée",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Contraintes",
                        "info": "",
                        "attribut": "contraintes",
                        "placeholder": "Identifiez les principales contraintes",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 7,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Objectif du projet",
                        "info": "",
                        "attribut": "objectif_general",
                        "placeholder": "Décrivez l'objectif principal du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Objectif Specifiques",
                        "info": "",
                        "attribut": "objectifs_specifiques",
                        "placeholder": "Décrivez l'objectif principal du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Résultats attendus",
                        "info": "",
                        "attribut": "resultats_attendus",
                        "placeholder": "Décrivez les résultats attendus",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Origine du projet",
                        "info": "",
                        "attribut": "origine",
                        "placeholder": "D'où vient l'idée de ce projet ?",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "textarea",
                        "sectionId": 42,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Description technique et Impacts",
                "ordre_affichage": 6,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Estimation des coûts et benefices",
                        "info": "",
                        "attribut": "estimation_couts",
                        "placeholder": "",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Caractéristiques techniques",
                        "info": "",
                        "attribut": "caracteristiques_techniques",
                        "placeholder": "Caractéristiques techniques",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Risques immédiats",
                        "info": "",
                        "attribut": "risques_immediats",
                        "placeholder": "Risques identifiés",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Conclusions",
                        "info": "",
                        "attribut": "conclusions",
                        "placeholder": "Conclusions générales",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Autre solutions alternatives considere et non retenues",
                        "info": "",
                        "attribut": "description",
                        "placeholder": "Autre solutions alternatives",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Description sommaire",
                        "info": "",
                        "attribut": "sommaire",
                        "placeholder": "Description sommaire",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Impact environnemental",
                        "info": "",
                        "attribut": "impact_environnement",
                        "placeholder": "Impact sur l'environnement",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Aspects organisationnels",
                        "info": "",
                        "attribut": "aspect_organisationnel",
                        "placeholder": "",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Description du projet",
                        "info": "",
                        "attribut": "description_projet",
                        "placeholder": "Description détaillée du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 3000,
                                "min_length": 50
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Echeancier du projet",
                        "info": "https://docs.google.com/document/d/1U9p3N557lwFIt-mkkg3KOF_VMZ6IwIpx/edit#heading=h.17dp8vu",
                        "attribut": "echeancier",
                        "placeholder": "Description détaillée du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 3000,
                                "min_length": 50
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Description du projet",
                        "info": "",
                        "attribut": "description_extrants",
                        "placeholder": "Description détaillée du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 43,
                        "meta_options": {
                            "configs": {
                                "max_length": 3000,
                                "min_length": 50
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            }
        ]
    */
    /*
        "sections": [
            {
                "intitule": "Informations Générales",
                "description": "Informations Générales",
                "ordre_affichage": 1,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Titre du projet",
                        "info": "",
                        "attribut": "titre_projet",
                        "placeholder": "Saisissez le titre de votre projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "text",
                        "meta_options": {
                            "configs": {
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Sigle du projet",
                        "info": "",
                        "attribut": "sigle",
                        "placeholder": "Acronyme du projet",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "text",
                        "meta_options": {
                            "configs": {
                                "max_length": 50,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Categorie de projet",
                        "info": "",
                        "attribut": "categorieId",
                        "placeholder": "Nom du ministère de rattachement",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "select",
                        "meta_options": {
                            "configs": {
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Ministère de tutelle",
                        "info": "",
                        "attribut": "ministereId",
                        "placeholder": "Nom du ministère de rattachement",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "text",
                        "sectionId": 22,
                        "meta_options": {
                            "configs": {
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Durée",
                        "info": "",
                        "attribut": "duree",
                        "placeholder": "Ex: 24 mois",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "text",
                        "sectionId": 22,
                        "meta_options": {
                            "configs": {
                                "max_length": 100,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Cout estimatig du projet",
                        "info": "",
                        "attribut": "cout_estimatif_projet",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Devise",
                        "info": "",
                        "attribut": "cout_devise",
                        "placeholder": "Sélectionnez une devise",
                        "is_required": true,
                        "default_value": "FCFA",
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "select",
                        "sectionId": 22,
                        "meta_options": {
                            "configs": {
                                "options": [
                                    {
                                        "label": "FCFA",
                                        "value": "FCFA"
                                    },
                                    {
                                        "label": "USD",
                                        "value": "USD"
                                    },
                                    {
                                        "label": "EUR",
                                        "value": "EUR"
                                    }
                                ]
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Montant",
                        "info": "",
                        "attribut": "cout_dollar_americain",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 22,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Montant",
                        "info": "",
                        "attribut": "cout_euro",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 22,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Montant",
                        "info": "",
                        "attribut": "cout_dollar_canadien",
                        "placeholder": "0",
                        "is_required": true,
                        "default_value": "0",
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "number",
                        "sectionId": 22,
                        "meta_options": {
                            "configs": {
                                "max": null,
                                "min": 0,
                                "step": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Secteur d 'activité et Localisation",
                "description": "Secteur d 'activité et Localisation",
                "ordre_affichage": 2,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Grand Secteur",
                        "info": "",
                        "attribut": "grand_secteur",
                        "placeholder": "Choisissez un grand secteur",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "select",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Secteur",
                        "info": "",
                        "attribut": "secteur",
                        "placeholder": "Choisissez un secteur",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "select",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Sous-Secteur",
                        "info": "",
                        "attribut": "secteurId",
                        "placeholder": "Choisissez un sous-secteur",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "select",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Département",
                        "info": "",
                        "attribut": "departements",
                        "placeholder": "Choisissez un département",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "multiselect",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Commune",
                        "info": "",
                        "attribut": "communes",
                        "placeholder": "Choisissez une commune",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "multiselect",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Arrondissement",
                        "info": "",
                        "attribut": "arrondissements",
                        "placeholder": "Choisissez un arrondissement",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "multiselect",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Quartier",
                        "info": "",
                        "attribut": "quartiers",
                        "placeholder": "Nom du quartier",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 7,
                        "type_champ": "multiselect",
                        "sectionId": 23,
                        "meta_options": {
                            "configs": {
                                "max_length": 255,
                                "min_length": 1
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Cadres stratégiques",
                "description": "Cadres stratégiques",
                "ordre_affichage": 3,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "ODD",
                        "info": "",
                        "attribut": "odds",
                        "placeholder": "Sélectionnez un ODD",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Cibles",
                        "info": "",
                        "attribut": "cibles",
                        "placeholder": "Sélectionnez les cibles",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Orientation stratégique",
                        "info": "",
                        "attribut": "orientations_strategiques",
                        "placeholder": "Choisissez une orientation",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Objectif stratégique",
                        "info": "",
                        "attribut": "objectifs_strategiques",
                        "placeholder": "Choisissez un objectif",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Résultat stratégique",
                        "info": "",
                        "attribut": "resultats_strategiques",
                        "placeholder": "Choisissez un résultat",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Axe du pag",
                        "info": "",
                        "attribut": "axes_pag",
                        "placeholder": "Choisissez un axe",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 7,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Action",
                        "info": "",
                        "attribut": "actions_pag",
                        "placeholder": "Choisissez une action",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 8,
                        "type_champ": "multiselect",
                        "sectionId": 24,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Financement et Bénéficiaires",
                "description": "Financement et Bénéficiaires",
                "ordre_affichage": 4,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Types de financement",
                        "info": "",
                        "attribut": "types_financement",
                        "placeholder": "Choisissez un type",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "multiselect",
                        "sectionId": 25,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Nature du financement",
                        "info": "",
                        "attribut": "natures_financement",
                        "placeholder": "Choisissez une nature",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "multiselect",
                        "sectionId": 25,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Source de financement",
                        "info": "",
                        "attribut": "sources_financement",
                        "placeholder": "Choisissez une source",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "multiselect",
                        "sectionId": 25,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Public cible",
                        "info": "",
                        "attribut": "public_cible",
                        "placeholder": "Décrivez le public cible du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 25,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Parties prenantes",
                        "info": "",
                        "attribut": "parties_prenantes",
                        "placeholder": "Identifiez les parties prenantes impliquées",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "textarea",
                        "sectionId": 25,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Contexte et Analyse",
                "description": "Contexte et Analyse",
                "ordre_affichage": 5,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Objectif du projet",
                        "info": "",
                        "attribut": "objectif_general",
                        "placeholder": "Décrivez l'objectif principal du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Objectif Specifiques",
                        "info": "",
                        "attribut": "objectifs_specifiques",
                        "placeholder": "Décrivez l'objectif principal du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Résultats attendus",
                        "info": "",
                        "attribut": "resultats_attendus",
                        "placeholder": "Décrivez les résultats attendus",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Origine du projet",
                        "info": "",
                        "attribut": "origine",
                        "placeholder": "D'où vient l'idée de ce projet ?",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Fondement du projet",
                        "info": "",
                        "attribut": "fondement",
                        "placeholder": "Sur quoi se base ce projet ?",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Situation actuelle",
                        "info": "",
                        "attribut": "situation_actuelle",
                        "placeholder": "Décrivez la situation actuelle",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Situation désirée",
                        "info": "",
                        "attribut": "situation_desiree",
                        "placeholder": "Décrivez la situation visée",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 20
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Contraintes",
                        "info": "",
                        "attribut": "contraintes",
                        "placeholder": "Identifiez les principales contraintes",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 7,
                        "type_champ": "textarea",
                        "sectionId": 26,
                        "meta_options": {
                            "configs": {
                                "max_length": 1000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Description technique et Impacts",
                "description": "Description technique et Impacts",
                "ordre_affichage": 6,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Description du projet",
                        "info": "",
                        "attribut": "description_projet",
                        "placeholder": "Description détaillée du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 3000,
                                "min_length": 50
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Description du projet",
                        "info": "",
                        "attribut": "description_extrants",
                        "placeholder": "Description détaillée du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 3000,
                                "min_length": 50
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Echeancier du projet",
                        "info": "https://docs.google.com/document/d/1U9p3N557lwFIt-mkkg3KOF_VMZ6IwIpx/edit#heading=h.17dp8vu",
                        "attribut": "echeancier",
                        "placeholder": "Description détaillée du projet",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 3000,
                                "min_length": 50
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Caractéristiques techniques",
                        "info": "",
                        "attribut": "caracteristiques_techniques",
                        "placeholder": "Caractéristiques techniques",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 2000,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Impact environnemental",
                        "info": "",
                        "attribut": "impact_environnement",
                        "placeholder": "Impact sur l'environnement",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 3,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Aspects organisationnels",
                        "info": "",
                        "attribut": "aspect_organisationnel",
                        "placeholder": "",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Estimation des couts et benefices",
                        "info": "",
                        "attribut": "estimation_couts",
                        "placeholder": "",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 4,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Risques immédiats",
                        "info": "",
                        "attribut": "risques_immediats",
                        "placeholder": "Risques identifiés",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 5,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Conclusions",
                        "info": "",
                        "attribut": "conclusions",
                        "placeholder": "Conclusions générales",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Autre solutions alternatives considere et non retenues",
                        "info": "",
                        "attribut": "description",
                        "placeholder": "Autre solutions alternatives",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Description sommaire",
                        "info": "",
                        "attribut": "sommaire",
                        "placeholder": "Description sommaire",
                        "is_required": false,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 6,
                        "type_champ": "textarea",
                        "sectionId": 27,
                        "meta_options": {
                            "configs": {
                                "max_length": 1500,
                                "min_length": 10
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": false
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            },
            {
                "intitule": "Responsabilités",
                "description": "Responsabilités",
                "ordre_affichage": 7,
                "type": "formulaire",
                "champs": [
                    {
                        "label": "Responsable du projet",
                        "info": "",
                        "attribut": "responsableId",
                        "placeholder": "Sélectionnez le responsable",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 1,
                        "type_champ": "select",
                        "sectionId": 28,
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    },
                    {
                        "label": "Demandeur",
                        "info": "",
                        "attribut": "demandeurId",
                        "placeholder": "Sélectionnez le demandeur",
                        "is_required": true,
                        "default_value": null,
                        "isEvaluated": false,
                        "ordre_affichage": 2,
                        "type_champ": "select",
                        "meta_options": {
                            "configs": {
                                "options": []
                            },
                            "conditions": {
                                "disable": false,
                                "visible": true,
                                "conditions": []
                            },
                            "validations_rules": {
                                "required": true
                            }
                        },
                        "champ_standard": true,
                        "startWithNewLine": null
                    }
                ]
            }
        ]
    */
}