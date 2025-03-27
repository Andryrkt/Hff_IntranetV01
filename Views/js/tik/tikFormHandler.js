import {
  toggleRequiredFields,
  disableForm,
  disableErrorElements,
} from '../utils/formUtils.js';

const tikCategorie = document.querySelector('#detail_tik_categorie');
const tikSousCategorie = document.querySelector('#detail_tik_sousCategorie');
const tikAutreCategorie = document.querySelector('#detail_tik_autresCategorie');
const tikNiveauUrgence = document.querySelector('#detail_tik_niveauUrgence');
const tikIntervenant = document.querySelector('#detail_tik_intervenant');
const tikCommentaires = document.querySelector('#detail_tik_commentaires');
const tikPartOfDay = document.querySelector('#detail_tik_partOfDay');
const dateDebutPlanning = document.querySelector(
  '#detail_tik_dateDebutPlanning'
);
const dateFinPlanning = document.querySelector('#detail_tik_dateFinPlanning');

export function handleActionClick(buttonName, formId) {
  disableForm(formId);
  disableErrorElements(
    document.querySelector('.error-message-intervenant'),
    document.querySelector('.error-message-date')
  );
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
      optionalFields: [
        tikCategorie,
        tikSousCategorie,
        tikNiveauUrgence,
        tikIntervenant,
      ],
    },
    refuser: {
      enableFields: [tikCommentaires],
      requiredFields: [tikCommentaires],
      optionalFields: [
        tikCategorie,
        tikSousCategorie,
        tikNiveauUrgence,
        tikIntervenant,
      ],
    },
    resoudre: {
      enableFields: [tikCommentaires],
      requiredFields: [tikCommentaires],
      optionalFields: [
        tikIntervenant,
        tikPartOfDay,
        dateDebutPlanning,
        dateFinPlanning,
      ],
    },
    transferer: {
      enableFields: [tikIntervenant],
      requiredFields: [tikIntervenant],
      optionalFields: [
        tikCommentaires,
        tikPartOfDay,
        dateDebutPlanning,
        dateFinPlanning,
      ],
    },
    planifier: {
      enableFields: [tikPartOfDay, dateDebutPlanning, dateFinPlanning],
      requiredFields: [tikPartOfDay, dateDebutPlanning, dateFinPlanning],
      optionalFields: [tikCommentaires, tikIntervenant],
    },
    cloturer: {
      enableFields: [],
      requiredFields: [],
      optionalFields: [],
    },
    debut: {
      enableFields: [
        tikIntervenant,
        tikPartOfDay,
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
