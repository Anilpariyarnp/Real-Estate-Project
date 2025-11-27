// let header = document.querySelector('.header');

// document.querySelector('#menu-btn').onclick = () => {
//   header.classList.add('active');
// }

// document.querySelector('#close-btn').onclick = () => {
//   header.classList.remove('active');
// }

// window.onscroll = () => {
//   header.classList.remove('active');
// }

// document.querySelectorAll('input[type="number"]').forEach(inputNumbmer => {
//   inputNumbmer.oninput = () => {
//     if (inputNumbmer.value.length > inputNumbmer.maxLength) inputNumbmer.value = inputNumbmer.value.slice(0, inputNumbmer.maxLength);
//   }
// });


document.getElementById('menu-btn').addEventListener('click', function () {
  const mobileMenu = document.querySelector('.header .navbar');
  mobileMenu.classList.toggle('active');
  this.classList.toggle('active');

  // Toggle visibility of buttons when menu is active
  const buttons = document.querySelectorAll('.header .btn, .header .flex-btn, .header .delete-btn');
  buttons.forEach(button => {
    button.style.display = mobileMenu.classList.contains('active') ? 'flex' : 'none';
  });
});