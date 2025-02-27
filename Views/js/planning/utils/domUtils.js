/**
 * Fonction permet de crée une element HTML
 * @param {string} tag
 * @param {Object} attributes
 * @param {array} children
 * @returns
 */
export function createElement(tag, attributes = {}, children = []) {
  const element = document.createElement(tag);
  Object.keys(attributes).forEach((key) => {
    if (key === "className") {
      element.className = attributes[key];
    } else {
      element.setAttribute(key, attributes[key]);
    }
  });

  children.forEach((child) => {
    if (typeof child === "string") {
      element.appendChild(document.createTextNode(child));
    } else {
      element.appendChild(child);
    }
  });

  return element;
}

/**
 * Fonction qui permet d'effacher les enfant d'une select
 * @param {HTMLElement} parent
 */
export function clearChildren(parent) {
  while (parent.firstChild) {
    parent.removeChild(parent.firstChild);
  }
}
