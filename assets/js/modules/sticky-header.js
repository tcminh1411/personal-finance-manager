/**
 * Sticky Header Module
 * Hides header when scrolling down, shows when scrolling up.
 * Uses requestAnimationFrame to avoid layout thrashing.
 */
const StickyHeader = {
  header: null,
  lastScrollY: 0,
  ticking: false,
  SCROLL_THRESHOLD: 80, // px — không ẩn header khi còn gần top

  init() {
    this.header = document.querySelector("header");
    if (!this.header) return;

    // Gắn transition một lần duy nhất qua JS (không cần thêm class Tailwind)
    this.header.style.transition = "transform 0.3s ease";

    window.addEventListener("scroll", () => this.onScroll(), { passive: true });
  },

  onScroll() {
    if (this.ticking) return;

    // Dùng rAF để batch các scroll event, tránh layout thrashing
    requestAnimationFrame(() => {
      this.update();
      this.ticking = false;
    });

    this.ticking = true;
  },

  update() {
    const currentScrollY = window.scrollY;

    if (currentScrollY < this.SCROLL_THRESHOLD) {
      // Luôn hiện header khi gần top trang
      this.show();
    } else if (currentScrollY > this.lastScrollY) {
      // Scroll xuống → ẩn
      this.hide();
    } else {
      // Scroll lên → hiện
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