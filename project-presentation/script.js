const reveals = document.querySelectorAll('.reveal');
const sections = document.querySelectorAll('main section[id]');
const navLinks = document.querySelectorAll('.topnav a');
const progressBar = document.getElementById('progressBar');
const scrollTopBtn = document.getElementById('scrollTopBtn');
const menuBtn = document.getElementById('menuBtn');
const topNav = document.getElementById('topNav');
const mobileNavBreakpoint = window.matchMedia('(max-width: 860px)');

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      const counters = entry.target.querySelectorAll('[data-counter]');
      counters.forEach(animateCounter);
    }
  });
}, { threshold: 0.16 });

reveals.forEach(el => revealObserver.observe(el));

const sectionObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const id = entry.target.getAttribute('id');
      navLinks.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === `#${id}`);
      });
    }
  });
}, { threshold: 0.45 });

sections.forEach(section => sectionObserver.observe(section));

const updateScrollUI = () => {
  const scrollTop = window.scrollY;
  const docHeight = document.documentElement.scrollHeight - window.innerHeight;
  const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
  progressBar.style.width = `${progress}%`;

  if (scrollTopBtn && scrollTop > 500) {
    scrollTopBtn.classList.add('visible');
  } else if (scrollTopBtn) {
    scrollTopBtn.classList.remove('visible');
  }
};

const closeMenu = () => {
  topNav.classList.remove('open');
  menuBtn.classList.remove('is-open');
  menuBtn.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('nav-open');
};

const toggleMenu = () => {
  const isOpen = topNav.classList.toggle('open');
  menuBtn.classList.toggle('is-open', isOpen);
  menuBtn.setAttribute('aria-expanded', String(isOpen));
  document.body.classList.toggle('nav-open', isOpen);
};

window.addEventListener('scroll', updateScrollUI, { passive: true });
window.addEventListener('load', updateScrollUI);

if (scrollTopBtn) {
  scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

menuBtn.addEventListener('click', toggleMenu);

navLinks.forEach(link => {
  link.addEventListener('click', () => {
    closeMenu();
  });
});

window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    closeMenu();
  }
});

mobileNavBreakpoint.addEventListener('change', (event) => {
  if (!event.matches) {
    closeMenu();
  }
});

function animateCounter(el) {
  if (el.dataset.counted === '1') {
    return;
  }

  const target = Number(el.getAttribute('data-counter') || '0');
  const duration = 1100;
  const start = performance.now();

  const format = (value) => value.toLocaleString('en-US');

  const step = (now) => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.floor(target * eased);
    el.textContent = format(current);

    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      el.textContent = format(target);
      el.dataset.counted = '1';
    }
  };

  requestAnimationFrame(step);
}
