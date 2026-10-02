import { formatFileSize } from "./file_upload_Utils.js";

const MAX_FILES = 10;
const MAX_SIZE = 5 * 1024 * 1024;
const MAX_TOTAL = 7 * 1024 * 1024;

/**
 * Dropzone de PDF multiples, état propre à chaque instance (pas de variable globale).
 * Retourne { reset, count }.
 */
export function createMultiPdfDropzone({
  dropzone,
  uploadBtn,
  input,
  list,
  summary,
  clearBtn,
  onChange = () => {},
}) {
  let files = [];
  const cards = new Map(); // File -> { el, url }
  input.multiple = true;
  input.accept = "application/pdf,.pdf";

  function validate(file, total) {
    if (!/\.pdf$/i.test(file.name) || file.type !== "application/pdf")
      return "n'est pas un PDF";
    if (file.size > MAX_SIZE) return "dépasse 5 Mo";
    if (files.some((f) => f.name === file.name && f.size === file.size))
      return "déjà ajouté";
    if (files.length >= MAX_FILES)
      return `limite de ${MAX_FILES} fichiers atteinte`;
    if (total + file.size > MAX_TOTAL)
      return `taille totale > ${MAX_TOTAL / 1024 / 1024} Mo`;
    return null;
  }

  function add(newFiles) {
    const rejected = [];
    let total = files.reduce((s, f) => s + f.size, 0);
    for (const file of newFiles) {
      const reason = validate(file, total);
      if (reason) {
        rejected.push(
          `<li><strong>${escapeHtml(file.name)}</strong> : ${reason}</li>`
        );
      } else {
        files.push(file);
        total += file.size;
      }
    }
    sync();
    if (rejected.length) {
      Swal.fire({
        icon: "warning",
        title: "Fichier(s) refusé(s)",
        html: `<ul class="text-start mb-0">${rejected.join("")}</ul>`,
        confirmButtonText: "OK",
      });
    }
  }

  function remove(file) {
    files = files.filter((f) => f !== file);
    sync();
  }

  function sync() {
    const dt = new DataTransfer();
    files.forEach((f) => dt.items.add(f));
    input.files = dt.files;
    render();
    onChange(files.length);
  }

  function render() {
    // retire les cartes supprimées
    for (const [file, card] of cards) {
      if (!files.includes(file)) {
        URL.revokeObjectURL(card.url);
        card.el.remove();
        cards.delete(file);
      }
    }
    // ajoute uniquement les nouvelles (pas de ré-animation des existantes)
    let delay = 0;
    for (const file of files) {
      if (cards.has(file)) continue;
      const url = URL.createObjectURL(file);
      const el = buildCard(file, url);
      el.style.animationDelay = `${delay}ms`;
      delay += 40;
      list.appendChild(el);
      cards.set(file, { el, url });
    }
    summary.textContent = files.length
      ? `${files.length} fichier(s) sélectionné(s) – voir les détails`
      : "";
    summary.hidden = !files.length;
    if (clearBtn) clearBtn.hidden = !files.length;
  }

  function buildCard(file, url) {
    const el = document.createElement("div");
    el.className = "pj-card border rounded p-2 mb-3";

    const head = document.createElement("div");
    head.className = "d-flex align-items-center gap-2";
    const name = document.createElement("strong");
    name.className = "text-truncate flex-grow-1";
    name.textContent = file.name;
    const size = document.createElement("span");
    size.className = "text-muted small";
    size.textContent = formatFileSize(file.size);
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "btn btn-sm btn-outline-danger";
    btn.title = "Supprimer ce fichier";
    btn.setAttribute("aria-label", `Supprimer ${file.name}`);
    btn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    btn.addEventListener("click", () => remove(file));
    const previewBtn = document.createElement("button");
    previewBtn.type = "button";
    previewBtn.className = "btn btn-sm btn-outline-secondary";
    previewBtn.innerHTML = '<i class="fa-solid fa-eye"></i> Aperçu';
    previewBtn.addEventListener("click", () =>
      togglePreview(el, previewBtn, url)
    );
    head.append(name, size, previewBtn, btn);

    el.append(head);
    return el;
  }

  // L'aperçu (visionneur PDF lourd) n'est créé qu'à la demande, un seul à la fois.
  function togglePreview(el, previewBtn, url) {
    const open = el.querySelector("embed");
    closePreviews();
    if (open) return;
    const embed = document.createElement("embed");
    embed.src = url;
    embed.type = "application/pdf";
    embed.className = "w-100 mt-2 border rounded";
    embed.style.height = "750px";
    el.append(embed);
    previewBtn.innerHTML = '<i class="fa-solid fa-eye-slash"></i> Masquer';
  }

  function closePreviews() {
    list.querySelectorAll("embed").forEach((e) => e.remove());
    list
      .querySelectorAll(".btn-outline-secondary")
      .forEach((b) => (b.innerHTML = '<i class="fa-solid fa-eye"></i> Aperçu'));
  }

  uploadBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    input.click();
  });
  dropzone.addEventListener("click", () => input.click());
  dropzone.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      input.click();
    }
  });
  input.addEventListener("change", () => add(Array.from(input.files)));

  ["dragenter", "dragover"].forEach((evt) =>
    dropzone.addEventListener(evt, (e) => {
      e.preventDefault();
      dropzone.classList.add("is-dragover");
    })
  );
  dropzone.addEventListener("dragleave", (e) => {
    if (!dropzone.contains(e.relatedTarget))
      dropzone.classList.remove("is-dragover");
  });
  dropzone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropzone.classList.remove("is-dragover");
    add(Array.from(e.dataTransfer.files));
  });

  if (clearBtn) clearBtn.addEventListener("click", reset);

  function reset() {
    files = [];
    sync();
  }

  render();
  return { reset, count: () => files.length };
}

function escapeHtml(str) {
  const d = document.createElement("div");
  d.textContent = str;
  return d.innerHTML;
}
