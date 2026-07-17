// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach((a) => {
  a.addEventListener('click', (event) => {
    const href = a.getAttribute('href');

    if (!href || href === '#') {
      return;
    }

    const target = document.querySelector(href);

    if (target) {
      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// Navbar shrink on scroll
window.addEventListener('scroll', () => {
  const nav = document.querySelector('.navbar');

  if (!nav) {
    return;
  }

  nav.style.boxShadow =
    window.scrollY > 40
      ? '0 4px 30px rgba(24,52,71,.15)'
      : '0 2px 20px rgba(24,52,71,.08)';
});

// Counter animation
const counters = document.querySelectorAll('.stat-num, .num');

if ('IntersectionObserver' in window && counters.length > 0) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        const element = entry.target;
        const originalText = element.textContent || '';
        const number = parseFloat(originalText.replace(/[^0-9.]/g, ''));
        const suffix = originalText.replace(/[0-9.]/g, '');

        if (Number.isNaN(number)) {
          observer.unobserve(element);
          return;
        }

        let current = 0;
        const step = number / 40;

        const timer = window.setInterval(() => {
          current += step;

          if (current >= number) {
            element.textContent = originalText;
            window.clearInterval(timer);
          } else {
            element.textContent =
              (Number.isInteger(number)
                ? Math.floor(current)
                : current.toFixed(1)) + suffix;
          }
        }, 30);

        observer.unobserve(element);
      });
    },
    { threshold: 0.5 }
  );

  counters.forEach((counter) => observer.observe(counter));
}
