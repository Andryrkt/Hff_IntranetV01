document.addEventListener("DOMContentLoaded", (event) => {
  /** LIMITER LES CARTERES DU CHAMP */
  const champInput = document.querySelectorAll(".limiteCarater");

  champInput.forEach((element) => {
    element.addEventListener("input", limiteCaracter);
  });

  function limiteCaracter() {
    this.value = this.value.toUpperCase();

    const maxLength = 8;
    let currentLength = this.value.length;

    if (currentLength > maxLength) {
      this.value = this.value.substring(0, maxLength);
      currentLength = maxLength;
    }

    const charCountId = `charCount${this.id.slice(-1)}`;
    const charCount = document.getElementById(charCountId);
    if (charCount) {
      charCount.textContent = `${currentLength}/${maxLength}`;
    }
  }

  /** RENDRE MAJUSCULE LE DONNER ECRIT */
  const motifInput = document.querySelector("#casier_form2_motif");

  motifInput.addEventListener("input", majuscule);

  function majuscule() {
    this.value = this.value.toUpperCase();

    const maxLength = 100;
    let currentLength = this.value.length;

    if (currentLength > maxLength) {
      this.value = this.value.substring(0, maxLength);
      currentLength = maxLength;
    }

    const charCountId = `charCount${this.id.slice(-1)}`;
    const charCount = document.getElementById(charCountId);
    if (charCount) {
      charCount.textContent = `${currentLength}/${maxLength}`;
    }
  }
});
