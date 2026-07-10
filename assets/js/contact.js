/**
 * Portfolio OS — Contact Form
 * Client-side validation, AJAX submission, honeypot protection.
 */

'use strict';

class ContactForm {
  constructor() {
    this.form      = document.getElementById('contact-form');
    this.submitBtn = document.getElementById('contact-submit');
    this.feedback  = document.getElementById('contact-feedback');

    if (!this.form) return;
    this.init();
  }

  init() {
    this.form.addEventListener('submit', (e) => this.handleSubmit(e));

    // Live validation
    this.form.querySelectorAll('.neu-input').forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => this.clearError(input));
    });
  }

  validateField(field) {
    const value = field.value.trim();
    const name  = field.name;
    let error   = '';

    if (field.required && !value) {
      error = 'This field is required.';
    } else if (name === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      error = 'Please enter a valid email address.';
    } else if (name === 'message' && value.length < 10) {
      error = 'Message must be at least 10 characters.';
    } else if (value.length > (parseInt(field.maxLength) || 1000)) {
      error = `Maximum ${field.maxLength} characters.`;
    }

    this.showFieldError(field, error);
    return !error;
  }

  showFieldError(field, error) {
    const group = field.closest('.neu-input-group');
    if (!group) return;
    let errEl = group.querySelector('.field-error');
    if (!errEl) {
      errEl = document.createElement('p');
      errEl.className = 'field-error';
      group.appendChild(errEl);
    }
    errEl.textContent = error;
    field.style.borderColor = error ? 'var(--color-error)' : '';
  }

  clearError(field) {
    const group = field.closest('.neu-input-group');
    if (!group) return;
    const errEl = group.querySelector('.field-error');
    if (errEl) errEl.textContent = '';
    field.style.borderColor = '';
  }

  validateAll() {
    const fields = this.form.querySelectorAll('.neu-input');
    let valid = true;
    fields.forEach(field => {
      if (!this.validateField(field)) valid = false;
    });
    return valid;
  }

  async handleSubmit(e) {
    e.preventDefault();
    if (!this.validateAll()) return;

    // Check honeypot
    const honeypot = this.form.querySelector('[name="website"]');
    if (honeypot?.value) return; // Silent reject (bot)

    this.setLoading(true);

    try {
      const formData = new FormData(this.form);
      const res  = await fetch(this.form.action || '/portfolio/api/messages/create.php', {
        method: 'POST',
        body:   formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });

      const data = await res.json();

      if (data.success) {
        this.showFeedback(
          '✅ Message sent! I\'ll get back to you soon.',
          'success'
        );
        this.form.reset();
        showToast('Message sent successfully!', 'success');
      } else {
        this.showFeedback(
          data.error || 'Something went wrong. Please try again.',
          'error'
        );
      }
    } catch (err) {
      this.showFeedback('Network error. Please check your connection.', 'error');
    } finally {
      this.setLoading(false);
    }
  }

  setLoading(loading) {
    if (!this.submitBtn) return;
    this.submitBtn.disabled = loading;
    this.submitBtn.innerHTML = loading
      ? '<span class="spinner"></span> Sending…'
      : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send Message';
  }

  showFeedback(message, type) {
    if (!this.feedback) return;
    this.feedback.className = `neu-alert neu-alert--${type}`;
    this.feedback.textContent = message;
    this.feedback.style.display = 'flex';
    this.feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

document.addEventListener('DOMContentLoaded', () => new ContactForm());
