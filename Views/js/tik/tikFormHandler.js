import { toggleRequiredFields, disableForm } from '../utils/formUtils.js';

const tikCategorie = document.querySelector('#detail_tik_categorie');
const tikSousCategorie = document.querySelector('#detail_tik_sousCategorie');
const tikAutreCategorie = document.querySelector('#detail_tik_autresCategorie');
const tikNiveauUrgence = document.querySelector('#detail_tik_niveauUrgence');
const tikIntervenant = document.querySelector('#detail_tik_intervenant');
const tikCommentaires = document.querySelector('#detail_tik_commentaires');
const dateDebutPlanning = document.querySelector(
  '#detail_tik_dateDebutPlanning'
);
const dateFinPlanning = document.querySelector('#detail_tik_dateFinPlanning');

export function handleActionClick(buttonName, formId) {
  disableForm(formId);
  const actions = {
    valider: {
      enableFields: [
        tikCategorie,
        tikSousCategorie,
        tikAutreCategorie,
        tikNiveauUrgence,
        tikIntervenant,
        tikCommentaires,
      ],
      requiredFields: [
        tikCategorie,
        tikNiveauUrgence,
        tikSousCategorie,
        tikIntervenant,
      ],
      optionalFields: [tikCommentaires],
    },
    commenter: {
      enableFields: [tikCommentaires],
      requiredFields: [tikCommentaires],
      optionalFields: [],
    },
    refuser: {
      enableFields: [tikCommentaires],
      requiredFields: [tikCommentaires],
      optionalFields: [],
    },
    resoudre: {
      enableFields: [tikCommentaires],
      requiredFields: [tikCommentaires],
      optionalFields: [],
    },
    transferer: {
      enableFields: [tikIntervenant],
      requiredFields: [tikIntervenant],
      optionalFields: [],
    },
    planifier: {
      enableFields: [dateDebutPlanning, dateFinPlanning],
      requiredFields: [dateDebutPlanning, dateFinPlanning],
      optionalFields: [],
    },
    cloturer: {
      enableFields: [],
      requiredFields: [],
      optionalFields: [],
    },
    debut: {
      enableFields: [
        tikIntervenant,
        dateDebutPlanning,
        dateFinPlanning,
        tikCommentaires,
      ],
      requiredFields: [],
      optionalFields: [],
    },
  };

  const action = actions[buttonName];
  toggleRequiredFields(
    action.enableFields,
    action.requiredFields,
    action.optionalFields
  );
}
