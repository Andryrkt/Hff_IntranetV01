import { handleActionClick } from './tikFormHandler.js';

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.querySelector('#btn_cloturer');
  btn?.addEventListener('click', () =>
    handleActionClick('cloturer', 'formTikCloture')
  );

  const myForm = document.getElementById('formTikCloture');
  myForm.querySelectorAll('div').forEach((element) => {
    element.classList.add('d-none');
  });
});
