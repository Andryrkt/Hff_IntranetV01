export function resetDropdown(dropdown, defaultText) {
  while (dropdown.options.length > 0) {
    dropdown.remove(0);
  }
  const defaultOption = new Option(defaultText, '');
  dropdown.add(defaultOption);
}

export function populateDropdown(dropdown, options) {
  options.forEach((opt) => {
    const option = new Option(opt.text, opt.value);
    dropdown.add(option);
  });
}
