/**====================================================================
 * Utilitaire réutilisable pour limiter et compter les caractères
 * d'un textarea, avec pondération spéciale pour les sauts de ligne.
 *====================================================================*/

/**
 * Initialise un compteur de caractères sur un textarea.
 *
 * @param {Object} options
 * @param {string|HTMLTextAreaElement} options.textareaSelector              - Sélecteur CSS ou élément textarea.
 * @param {string|HTMLElement} options.charCountSelector                     - Sélecteur CSS ou élément affichant le compteur.
 * @param {number} [options.maxCharacters=1800]                              - Nombre maximal de caractères autorisés.
 * @param {number} [options.lineBreakWeight=130]                             - Poids (en caractères) attribué à chaque saut de ligne.
 * @param {string} [options.normalColor="black"]                             - Couleur du texte quand il reste des caractères.
 * @param {string} [options.limitColor="red"]                                - Couleur du texte quand la limite est atteinte.
 * @param {(remaining:number, max:number) => string} [options.formatMessage] - Fonction de formatage du message affiché.
 *
 * @returns {{ destroy: () => void, reset: () => void }} - Fonctions de contrôle de l'instance.
 */
export function initCharCounter({
  textareaSelector,
  charCountSelector,
  maxCharacters = 1800,
  lineBreakWeight = 2,
  normalColor = "black",
  limitColor = "red",
  formatMessage = (remaining) => `Il vous reste ${remaining} caractères.`,
} = {}) {
  const textarea =
    typeof textareaSelector === "string"
      ? document.querySelector(textareaSelector)
      : textareaSelector;

  const charCount =
    typeof charCountSelector === "string"
      ? document.querySelector(charCountSelector)
      : charCountSelector;

  if (!textarea || !charCount) {
    console.warn("initCharCounter: textarea ou charCount introuvable.", {
      textarea,
      charCount,
    });
    return { destroy() {}, reset() {} };
  }

  function computeAdjustedLength(text) {
    const lineBreaks = (text.match(/\n/g) || []).length;
    return text.length + lineBreaks * lineBreakWeight;
  }

  function updateDisplay(remaining) {
    charCount.textContent = formatMessage(
      remaining >= 0 ? remaining : 0,
      maxCharacters
    );
    charCount.style.color = remaining <= 0 ? limitColor : normalColor;
  }

  function handleInput() {
    let text = textarea.value;
    let adjustedLength = computeAdjustedLength(text);

    // Bloquer l'ajout de texte si la limite est atteinte
    if (adjustedLength > maxCharacters) {
      let excessCharacters = adjustedLength - maxCharacters;

      while (excessCharacters > 0 && text.length > 0) {
        const lastChar = text[text.length - 1];

        if (lastChar === "\n") {
          excessCharacters -= lineBreakWeight;
        } else {
          excessCharacters -= 1;
        }

        text = text.substring(0, text.length - 1);
      }

      textarea.value = text;
      adjustedLength = maxCharacters;
    }

    const remainingCharacters = maxCharacters - adjustedLength;
    updateDisplay(remainingCharacters);
  }

  // État initial
  updateDisplay(maxCharacters);

  textarea.addEventListener("input", handleInput);

  return {
    /** Détache l'écouteur d'événement */
    destroy() {
      textarea.removeEventListener("input", handleInput);
    },
    /** Réinitialise l'affichage du compteur (ex: après un submit) */
    reset() {
      updateDisplay(maxCharacters);
    },
  };
}
