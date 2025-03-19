export function resetDropdown(dropdown, defaultText) {
  while (dropdown.options.length > 0) {
    dropdown.remove(0);
  }
  const defaultOption = document.createElement('option');
  defaultOption.value = '';
  defaultOption.text = defaultText;
  dropdown.add(defaultOption);
}

export function populateDropdown(dropdown, options) {
  console.log(options);

  options.forEach((opt) => {
    const option = document.createElement('option');
    option.value = opt.value;
    option.text = opt.text;
    dropdown.add(option);
  });
}
