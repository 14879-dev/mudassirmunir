/**
 * Portfolio OS — Projects Tag Filter
 * Client-side tag filtering with smooth show/hide animations.
 */

'use strict';

class ProjectFilter {
  constructor() {
    this.filterBtns   = document.querySelectorAll('.filter-btn');
    this.projectCards = document.querySelectorAll('.project-card-wrapper');
    this.currentTag   = 'all';
    this.noResults    = document.getElementById('no-results');

    if (!this.filterBtns.length) return;

    this.init();
  }

  init() {
    this.filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const tag = btn.dataset.tag || 'all';
        this.setActive(btn);
        this.filter(tag);
      });
    });

    // Read tag from URL hash on load
    const hash = window.location.hash.replace('#', '');
    if (hash) {
      const btn = document.querySelector(`.filter-btn[data-tag="${hash}"]`);
      if (btn) {
        this.setActive(btn);
        this.filter(hash);
      }
    }
  }

  setActive(activeBtn) {
    this.filterBtns.forEach(b => b.classList.remove('active'));
    activeBtn.classList.add('active');
    // Update URL hash without scroll
    history.replaceState(null, '', `#${activeBtn.dataset.tag || 'all'}`);
  }

  filter(tag) {
    this.currentTag = tag;
    let visibleCount = 0;

    this.projectCards.forEach((card, i) => {
      const tags = JSON.parse(card.dataset.tags || '[]');
      const show = tag === 'all' || tags.includes(tag);

      if (show) {
        card.style.display = '';
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0) scale(1)';
        }, visibleCount * 50);
        visibleCount++;
      } else {
        card.style.opacity = '0';
        card.style.transform = 'translateY(12px) scale(0.97)';
        setTimeout(() => {
          if (this.currentTag === tag) {
            card.style.display = 'none';
          }
        }, 300);
      }
    });

    if (this.noResults) {
      this.noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }
}

document.addEventListener('DOMContentLoaded', () => new ProjectFilter());
