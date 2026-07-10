/**
 * Portfolio OS — Dock Navigation
 * macOS-style magnification, active state detection, keyboard nav.
 */

'use strict';

class PortfolioDock {
  constructor() {
    this.dock = document.getElementById('main-dock');
    this.dockItems = document.querySelectorAll('.dock-item');
    this.scrollTopBtn = document.getElementById('scroll-top');
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    this.init();
  }

  init() {
    if (!this.dock) return;

    this.setupMagnification();
    this.setupActiveState();
    this.setupKeyboard();
    this.setupScrollTop();
    this.setupPageLoader();

    // Re-check active on scroll (single-page scroll mode)
    window.addEventListener('scroll', () => this.detectActiveSection(), { passive: true });
  }

  // ── Magnification (Desktop only) ──────────────────────
  setupMagnification() {
    if (this.prefersReducedMotion) return;

    const isTouchDevice = !window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (isTouchDevice) return;

    this.dockItems.forEach((item, index) => {
      const icon = item.querySelector('.dock-icon');
      if (!icon) return;

      this.dock.addEventListener('mousemove', (e) => {
        this.dockItems.forEach((dockItem, i) => {
          const dockIcon = dockItem.querySelector('.dock-icon');
          if (!dockIcon) return;

          const rect = dockIcon.getBoundingClientRect();
          const iconCx = rect.left + rect.width / 2;
          const dist = Math.abs(e.clientX - iconCx);
          const maxDist = 130;
          const maxScale = 1.5;
          const maxLift = 18;

          if (dist < maxDist) {
            const factor = 1 - (dist / maxDist);
            const scale = 1 + (maxScale - 1) * Math.pow(factor, 1.5);
            const lift = maxLift * Math.pow(factor, 1.8);
            dockIcon.style.transform = `translateY(-${lift}px) scale(${scale})`;
            dockIcon.style.zIndex = Math.round(factor * 10);
          } else {
            dockIcon.style.transform = '';
            dockIcon.style.zIndex = '';
          }
        });
      });
    });

    this.dock.addEventListener('mouseleave', () => {
      this.dockItems.forEach(item => {
        const icon = item.querySelector('.dock-icon');
        if (icon) {
          icon.style.transform = '';
          icon.style.zIndex = '';
        }
      });
    });
  }


  // ── Active Section Detection ───────────────────────────
  detectActiveSection() {
    const sections = document.querySelectorAll('section[id]');
    if (!sections.length) return;

    let currentId = sections[0].id;

    // Activate the section that's closest to the middle of the screen
    sections.forEach(section => {
      const rect = section.getBoundingClientRect();

      if (
        rect.top <= window.innerHeight * 0.35 &&
        rect.bottom >= window.innerHeight * 0.35
      ) {
        currentId = section.id;
      }
    });

    this.dockItems.forEach(item => {
      const href = item.getAttribute('href') || '';

      item.classList.toggle(
        'active',
        href.endsWith(`#${currentId}`)
      );
    });
  }
  setupActiveState() {
    this.detectActiveSection();
  }

  // ── Keyboard Navigation ────────────────────────────────
  setupKeyboard() {
    const items = Array.from(this.dockItems);

    items.forEach((item, index) => {
      item.setAttribute('tabindex', '0');

      item.addEventListener('keydown', (e) => {
        switch (e.key) {
          case 'ArrowRight':
          case 'ArrowUp':
            e.preventDefault();
            items[(index + 1) % items.length].focus();
            break;
          case 'ArrowLeft':
          case 'ArrowDown':
            e.preventDefault();
            items[(index - 1 + items.length) % items.length].focus();
            break;
          case 'Enter':
          case ' ':
            e.preventDefault();
            item.click();
            break;
          case 'Home':
            e.preventDefault();
            items[0].focus();
            break;
          case 'End':
            e.preventDefault();
            items[items.length - 1].focus();
            break;
        }
      });

      // Keyboard focus = show label
      item.addEventListener('focus', () => {
        const label = item.querySelector('.dock-label');
        if (label) {
          label.style.opacity = '1';
          label.style.transform = 'translateX(-50%) translateY(0)';
        }
      });
      item.addEventListener('blur', () => {
        const label = item.querySelector('.dock-label');
        if (label) {
          label.style.opacity = '';
          label.style.transform = '';
        }
      });
    });
  }

  // ── Scroll-to-top button ──────────────────────────────
  setupScrollTop() {
    if (!this.scrollTopBtn) return;

    window.addEventListener('scroll', () => {
      this.scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });

    this.scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── Page Loader ───────────────────────────────────────
  setupPageLoader() {
    const loader = document.getElementById('page-loader');
    if (!loader) return;

    window.addEventListener('load', () => {
      setTimeout(() => loader.classList.add('hidden'), 300);
    });
  }
}

// ── Scroll Reveal ─────────────────────────────────────
class ScrollReveal {
  constructor() {
    this.elements = document.querySelectorAll('.reveal');
    if (!this.elements.length) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
      this.elements.forEach(el => el.classList.add('revealed'));
      return;
    }

    this.observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            this.observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );

    this.elements.forEach(el => this.observer.observe(el));
  }
}

// ── Typewriter Effect ─────────────────────────────────
class Typewriter {
  constructor(el, words, opts = {}) {
    this.el = el;
    this.words = words;
    this.typeSpeed = opts.typeSpeed || 80;
    this.deleteSpeed = opts.deleteSpeed || 40;
    this.pauseTime = opts.pauseTime || 2000;
    this.wordIndex = 0;
    this.charIndex = 0;
    this.isDeleting = false;
    this.prefersRM = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!el || !words.length) return;

    if (this.prefersRM) {
      el.textContent = words[0];
      return;
    }

    this.type();
  }

  type() {
    const word = this.words[this.wordIndex % this.words.length];
    const current = this.isDeleting
      ? word.slice(0, --this.charIndex)
      : word.slice(0, ++this.charIndex);

    this.el.textContent = current;

    let delay = this.isDeleting ? this.deleteSpeed : this.typeSpeed;

    if (!this.isDeleting && this.charIndex === word.length) {
      delay = this.pauseTime;
      this.isDeleting = true;
    } else if (this.isDeleting && this.charIndex === 0) {
      this.isDeleting = false;
      this.wordIndex++;
      delay = 500;
    }

    setTimeout(() => this.type(), delay);
  }
}

// ── Toast notifications ───────────────────────────────
function showToast(message, type = 'info', duration = 3500) {
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-50%) translateY(10px)';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// ── Lightbox ──────────────────────────────────────────
class Lightbox {
  constructor() {
    this.overlay = document.getElementById('lightbox');
    this.img = document.getElementById('lightbox-img');
    this.close = document.getElementById('lightbox-close');
    if (!this.overlay) return;

    document.querySelectorAll('[data-lightbox]').forEach(el => {
      el.addEventListener('click', () => this.open(el.src || el.dataset.src));
    });

    this.close?.addEventListener('click', () => this.closeBox());
    this.overlay.addEventListener('click', (e) => {
      if (e.target === this.overlay) this.closeBox();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.closeBox();
    });
  }

  open(src) {
    if (!this.img || !src) return;
    this.img.src = src;
    this.overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  closeBox() {
    this.overlay.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// ── Init on DOM ready ─────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  new PortfolioDock();
  new ScrollReveal();
  new Lightbox();

  // Typewriter (if element exists)
  const twEl = document.getElementById('typewriter');
  const twWords = JSON.parse(twEl?.dataset.words || '[]');
  if (twEl && twWords.length) new Typewriter(twEl, twWords);
});
