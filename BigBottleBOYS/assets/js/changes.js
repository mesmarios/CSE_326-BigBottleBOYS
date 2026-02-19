(function () {
  const MOBILE_BREAKPOINT = 992;

  function animateSidebarOnOpen() {
    if (typeof window.gsap === 'undefined') {
      return;
    }

    const sidebar = document.querySelector('.app-sidebar');
    if (!sidebar) {
      return;
    }

    const menuItems = sidebar.querySelectorAll('.nav-item, .brand-link');
    if (!menuItems.length) {
      return;
    }

    gsap.killTweensOf(menuItems);
    gsap.fromTo(
      menuItems,
      { opacity: 0, x: -12 },
      {
        opacity: 1,
        x: 0,
        duration: 0.35,
        ease: 'power3.out',
        stagger: 0.04,
        clearProps: 'opacity,transform',
      }
    );
  }

  window.toggleSidebar = function toggleSidebar(event) {
    if (event) {
      event.preventDefault();
    }

    const body = document.body;
    if (!body) {
      return;
    }

    const isMobile = window.innerWidth < MOBILE_BREAKPOINT;

    if (isMobile) {
      body.classList.toggle('sidebar-open');
      body.classList.remove('sidebar-collapse');
      if (body.classList.contains('sidebar-open')) {
        animateSidebarOnOpen();
      }
      return;
    }

    body.classList.toggle('sidebar-collapse');
    body.classList.remove('sidebar-open');
    if (!body.classList.contains('sidebar-collapse')) {
      animateSidebarOnOpen();
    }
  };

  function initWelcomeTextAnimation() {
    if (typeof window.gsap === 'undefined') {
      return;
    }

    const welcomeEl = document.getElementById('welcomeText');
    if (!welcomeEl || welcomeEl.dataset.animated === 'true') {
      return;
    }

    const text = (welcomeEl.textContent || '').trim();
    if (!text) {
      return;
    }

    welcomeEl.textContent = '';

    const fragment = document.createDocumentFragment();
    const chars = [];

    for (const ch of text) {
      const span = document.createElement('span');
      span.style.display = 'inline-block';
      span.style.willChange = 'transform, opacity';
      span.textContent = ch === ' ' ? '\u00A0' : ch;
      chars.push(span);
      fragment.appendChild(span);
    }

    welcomeEl.appendChild(fragment);
    welcomeEl.dataset.animated = 'true';

    gsap.fromTo(
      chars,
      { opacity: 0, y: 40 },
      {
        opacity: 1,
        y: 0,
        duration: 1.25,
        ease: 'power3.out',
        stagger: 0.05,
        clearProps: 'transform,opacity',
        onComplete: function () {
          console.log('All letters have animated!');
        },
      }
    );
  }

  function initCardNav() {
    const mount = document.getElementById('cardNavMount');
    if (!mount || mount.dataset.initialized === 'true') {
      return;
    }

    const items = [
      {
        label: 'About',
        bgColor: '#1f1f1f',
        textColor: '#fff',
        links: [
          { label: 'Company', ariaLabel: 'About Company', href: '#' },
          { label: 'Careers', ariaLabel: 'About Careers', href: '#' },
        ],
      },
      {
        label: 'Projects',
        bgColor: '#2f2f2f',
        textColor: '#fff',
        links: [
          { label: 'Featured', ariaLabel: 'Featured Projects', href: '#' },
          { label: 'Case Studies', ariaLabel: 'Project Case Studies', href: '#' },
        ],
      },
      {
        label: 'Contact',
        bgColor: '#3f3f3f',
        textColor: '#fff',
        links: [
          { label: 'Email', ariaLabel: 'Email us', href: '#' },
          { label: 'Twitter', ariaLabel: 'Twitter', href: '#' },
          { label: 'LinkedIn', ariaLabel: 'LinkedIn', href: '#' },
        ],
      },
    ];

    const cardsHtml = items
      .map(function (item) {
        const linksHtml = item.links
          .map(function (link) {
            return (
              '<a class="nav-card-link" href="' +
              link.href +
              '" aria-label="' +
              link.ariaLabel +
              '">' +
              '<i class="bi bi-arrow-up-right nav-card-link-icon" aria-hidden="true"></i>' +
              link.label +
              '</a>'
            );
          })
          .join('');

        return (
          '<div class="nav-card" style="background-color:' +
          item.bgColor +
          ';color:' +
          item.textColor +
          ';">' +
          '<div class="nav-card-label">' +
          item.label +
          '</div>' +
          '<div class="nav-card-links">' +
          linksHtml +
          '</div>' +
          '</div>'
        );
      })
      .join('');

    mount.innerHTML =
      '<div class="card-nav-container">' +
      '<nav class="card-nav" style="background-color:#fff;">' +
      '<div class="card-nav-top">' +
      '<div class="hamburger-menu" role="button" aria-label="Open menu" tabindex="0" style="color:#000;">' +
      '<div class="hamburger-line"></div>' +
      '<div class="hamburger-line"></div>' +
      '</div>' +
      '<div class="logo-container">' +
      '<img src="../assets/images/AdminLTELogo.png" alt="Company Logo" class="logo" />' +
      '</div>' +
      '<button type="button" class="card-nav-cta-button" style="background-color:#2b2b2b;color:#fff;">Get Started</button>' +
      '</div>' +
      '<div class="card-nav-content" aria-hidden="true">' +
      cardsHtml +
      '</div>' +
      '</nav>' +
      '</div>';

    mount.dataset.initialized = 'true';

    const navEl = mount.querySelector('.card-nav');
    const contentEl = mount.querySelector('.card-nav-content');
    const toggleEl = mount.querySelector('.hamburger-menu');
    const cardEls = mount.querySelectorAll('.nav-card');

    if (!navEl || !contentEl || !toggleEl || !cardEls.length) {
      return;
    }

    let isExpanded = false;
    let timeline = null;

    const calculateHeight = function () {
      const isMobile = window.matchMedia('(max-width: 768px)').matches;
      if (isMobile) {
        const topBar = 60;
        const padding = 16;
        return topBar + contentEl.scrollHeight + padding;
      }
      return 260;
    };

    const createTimeline = function () {
      if (!window.gsap) {
        return null;
      }

      gsap.set(navEl, { height: 60, overflow: 'hidden' });
      gsap.set(cardEls, { y: 50, opacity: 0 });

      const tl = gsap.timeline({ paused: true });
      tl.to(navEl, { height: calculateHeight, duration: 0.4, ease: 'power3.out' });
      tl.to(cardEls, { y: 0, opacity: 1, duration: 0.4, ease: 'power3.out', stagger: 0.08 }, '-=0.1');
      return tl;
    };

    const toggleMenu = function () {
      if (!timeline) {
        return;
      }

      if (!isExpanded) {
        isExpanded = true;
        navEl.classList.add('open');
        toggleEl.classList.add('open');
        toggleEl.setAttribute('aria-label', 'Close menu');
        contentEl.setAttribute('aria-hidden', 'false');
        timeline.play(0);
        return;
      }

      isExpanded = false;
      toggleEl.classList.remove('open');
      toggleEl.setAttribute('aria-label', 'Open menu');
      timeline.eventCallback('onReverseComplete', function () {
        navEl.classList.remove('open');
        contentEl.setAttribute('aria-hidden', 'true');
      });
      timeline.reverse();
    };

    toggleEl.addEventListener('click', toggleMenu);
    toggleEl.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleMenu();
      }
    });

    timeline = createTimeline();
    window.addEventListener('resize', function () {
      if (!timeline) {
        return;
      }
      timeline.kill();
      timeline = createTimeline();
      if (timeline && isExpanded) {
        navEl.classList.add('open');
        contentEl.setAttribute('aria-hidden', 'false');
        timeline.progress(1);
      }
    });
  }

  function initRotatingText() {
    if (typeof window.gsap === 'undefined') {
      return;
    }

    const root = document.getElementById('rotatingText');
    if (!root || root.dataset.initialized === 'true') {
      return;
    }

    const texts = ['React', 'Bits', 'Is', 'Cool!'];
    const staggerDuration = 0.025;
    const rotationInterval = 2000;
    let currentIndex = 0;

    const splitIntoCharacters = function (text) {
      if (typeof Intl !== 'undefined' && Intl.Segmenter) {
        const segmenter = new Intl.Segmenter('en', { granularity: 'grapheme' });
        return Array.from(segmenter.segment(text), function (segment) {
          return segment.segment;
        });
      }
      return Array.from(text);
    };

    const renderText = function (text) {
      root.innerHTML = '';

      const srOnly = document.createElement('span');
      srOnly.className = 'text-rotate-sr-only';
      srOnly.textContent = text;
      root.appendChild(srOnly);

      const words = text.split(' ');
      words.forEach(function (word, wordIndex) {
        const wordSpan = document.createElement('span');
        wordSpan.className = 'text-rotate-word';

        splitIntoCharacters(word).forEach(function (ch) {
          const charSpan = document.createElement('span');
          charSpan.className = 'text-rotate-element';
          charSpan.textContent = ch;
          wordSpan.appendChild(charSpan);
        });

        root.appendChild(wordSpan);

        if (wordIndex !== words.length - 1) {
          const space = document.createElement('span');
          space.className = 'text-rotate-space';
          space.textContent = ' ';
          root.appendChild(space);
        }
      });
    };

    const animateIn = function () {
      const chars = root.querySelectorAll('.text-rotate-element');
      gsap.fromTo(
        chars,
        { y: '100%', opacity: 0 },
        {
          y: 0,
          opacity: 1,
          duration: 0.6,
          ease: 'power3.out',
          stagger: { each: staggerDuration, from: 'end' },
          clearProps: 'transform,opacity',
        }
      );
    };

    const animateOutIn = function () {
      const currentChars = root.querySelectorAll('.text-rotate-element');
      gsap.to(currentChars, {
        y: '-120%',
        opacity: 0,
        duration: 0.45,
        ease: 'power3.in',
        stagger: { each: staggerDuration, from: 'end' },
        onComplete: function () {
          currentIndex = (currentIndex + 1) % texts.length;
          renderText(texts[currentIndex]);
          animateIn();
        },
      });
    };

    renderText(texts[currentIndex]);
    animateIn();

    root.dataset.initialized = 'true';
    window.setInterval(animateOutIn, rotationInterval);
  }

  function initDashboardWordSwap() {
    if (typeof window.gsap === 'undefined') {
      return;
    }

    const wordA = document.getElementById('dashWordA');
    const wordB = document.getElementById('dashWordB');
    if (!wordA || !wordB || wordA.dataset.initialized === 'true') {
      return;
    }

    const sequence = [
      { a: 'Admin', b: 'Dashboard' },
      { a: 'Welcome', b: 'Dashboard' },
      { a: 'Welcome', b: 'Admin' },
    ];

    let index = 0;

    const swapWord = function (el, nextText) {
      gsap
        .timeline()
        .to(el, {
          y: '-120%',
          opacity: 0,
          duration: 0.25,
          ease: 'power2.in',
          onComplete: function () {
            el.textContent = nextText;
          },
        })
        .fromTo(
          el,
          { y: '120%', opacity: 0 },
          {
            y: 0,
            opacity: 1,
            duration: 0.35,
            ease: 'power3.out',
          }
        );
    };

    wordA.dataset.initialized = 'true';

    window.setInterval(function () {
      const current = sequence[index];
      index = (index + 1) % sequence.length;
      const next = sequence[index];

      if (current.a !== next.a) {
        swapWord(wordA, next.a);
      }

      window.setTimeout(function () {
        if (current.b !== next.b) {
          swapWord(wordB, next.b);
        }
      }, 280);
    }, 1900);
  }

  document.addEventListener('DOMContentLoaded', initWelcomeTextAnimation);
  document.addEventListener('DOMContentLoaded', initCardNav);
  document.addEventListener('DOMContentLoaded', initRotatingText);
  document.addEventListener('DOMContentLoaded', initDashboardWordSwap);
})();
