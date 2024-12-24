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

export function clearChildren(parent) {
  while (parent.firstChild) {
    parent.removeChild(parent.firstChild);
  }
}
