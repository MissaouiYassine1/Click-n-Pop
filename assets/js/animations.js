/* window.addEventListener('load', () => {
    const bubbles = document.querySelectorAll('.bubble');

    bubbles.forEach(bubble => {
        bubble.classList.add('animate-bubble');
    }); 
}); */
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        document.querySelector('header').classList.add('scrolled');
    } else {
        document.querySelector('header').classList.remove('scrolled');
    }
    /* const animatedElements = document.querySelectorAll('.animate-on-scroll');

    animatedElements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;    
        if (elementPosition < windowHeight - 100) {
            element.classList.add('animated');
        }
    }); */
});

