export function changeTab(direction) {
  let currentTab = localStorage.getItem('currentTab');
  showTab(false);
  if (direction === 'next') {
    currentTab++;
  } else if (direction === 'prev') {
    currentTab--;
  }
  localStorage.setItem('currentTab', currentTab);
  showTab();
}

export function showTab(bool = true) {
  let currentTab = localStorage.getItem('currentTab');
  console.log(currentTab);

  let tab = document.getElementById(`tab_${currentTab}`);
  if (bool) {
    tab.classList.add('show', 'active');
  } else {
    tab.classList.remove('show', 'active');
  }
}
