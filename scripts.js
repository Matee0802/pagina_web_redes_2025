document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.querySelector(".menu-toggle");
  const menus = document.querySelectorAll("nav ul");

  toggle.addEventListener("click", () => {
    menus.forEach(menu => menu.classList.toggle("show"));
  });
});

const slides = document.querySelectorAll('.slide');
let current = 0;

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.classList.toggle('active', i === index);
  });
}

document.querySelector('.arrow.left').onclick = () => {
  current = (current - 1 + slides.length) % slides.length;
  showSlide(current);
};
// 
document.querySelector('.arrow.right').onclick = () => {
  current = (current + 1) % slides.length;
  showSlide(current);
};

// Cambio automático cada 5 segundos
setInterval(() => {
  current = (current + 1) % slides.length;
  showSlide(current);
}, 3000);

// Inicializa el carrusel
showSlide(current);

