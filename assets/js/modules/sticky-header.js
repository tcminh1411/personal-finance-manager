/**
 * Sticky Header Module
 * Hides header when scrolling down, shows when scrolling up.
 * Uses requestAnimationFrame to avoid layout thrashing.
 */
const StickyHeader = {
  header: null,
  lastScrollY: 0,
  ticking: false,
  SCROLL_THRESHOLD: 80,
  DELTA_THRESHOLD: 8,

  init() {
    this.header = document.querySelector("header");
    if (!this.header) return;

    this.header.style.transition = "transform 0.3s ease";

    window.addEventListener("scroll", () => this.onScroll(), { passive: true });
  },

  onScroll() {
    if (this.ticking) return;

    requestAnimationFrame(() => {
      this.update();
      this.ticking = false;
    });

    this.ticking = true;
  },

  update() {
    const currentScrollY = window.scrollY;
    const delta = currentScrollY - this.lastScrollY;

    if (currentScrollY < this.SCROLL_THRESHOLD) {
      this.show();
    } else if (delta > this.DELTA_THRESHOLD) {
      this.hide();
    } else if (delta < -this.DELTA_THRESHOLD) {
      this.show();
    }

    this.lastScrollY = currentScrollY;
  },

  hide() {
    this.header.style.transform = "translateY(-100%)";
  },

  show() {
    this.header.style.transform = "translateY(0)";
  },
};