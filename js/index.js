// Carousel functionality for index.php
document.addEventListener('DOMContentLoaded', () => {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-item');

    if (slides.length === 0) return;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
    }

    function changeSlide(direction) {
        currentSlide = (currentSlide + direction + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    // Auto-play the carousel
    setInterval(() => {
        changeSlide(1);
    }, 5000);

    // Attach event listeners to buttons
    const prevButton = document.querySelector('.carousel-control.prev');
    const nextButton = document.querySelector('.carousel-control.next');

    if(prevButton) {
        prevButton.addEventListener('click', () => changeSlide(-1));
    }
    if(nextButton) {
        nextButton.addEventListener('click', () => changeSlide(1));
    }
    
    // Initialize first slide
    showSlide(currentSlide);
});