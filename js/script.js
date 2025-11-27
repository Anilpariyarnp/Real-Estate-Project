// let menu = document.querySelector('.header .menu');

// document.querySelector('#menu-btn').onclick = () => {
//   menu.classList.toggle('active');
// }

// window.onscroll = () => {
//   menu.classList.remove('active');
// }

// document.querySelectorAll('input[type="number"]').forEach(inputNumber => {
//   inputNumber.oninput = () => {
//     if (inputNumber.value.length > inputNumber.maxLength) inputNumber.value = inputNumber.value.slice(0, inputNumber.maxLength);
//   };
// });

// document.querySelectorAll('.faq .box-container .box h3').forEach(headings => {
//   headings.onclick = () => {
//     headings.parentElement.classList.toggle('active');
//   }
// });



// Toggle mobile menu and rotate button
const menuBtn = document.getElementById('menu-btn');
const menu = document.querySelector('.navbar.nav-2 .menu ul');

menuBtn.addEventListener('click', function () {
  menu.classList.toggle('active');
  menuBtn.classList.toggle('active'); // Toggle rotation class
});

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
  if (!menu.contains(event.target) && !menuBtn.contains(event.target)) {
    menu.classList.remove('active');
    menuBtn.classList.remove('active'); // Remove rotation class
  }
});

// Ensure dropdowns work on hover (desktop)
const dropdowns = document.querySelectorAll('.navbar.nav-2 .menu ul li');

dropdowns.forEach(dropdown => {
  dropdown.addEventListener('mouseenter', () => {
    const submenu = dropdown.querySelector('ul');
    if (submenu) {
      submenu.style.display = 'block';
    }
  });

  dropdown.addEventListener('mouseleave', () => {
    const submenu = dropdown.querySelector('ul');
    if (submenu) {
      submenu.style.display = 'none';
    }
  });
});
