/**
 * Front-office template helpers.
 * Keep this file dependency-free: only optional integrations with vendor libs.
 */
(() => {
  "use strict";

  const select = (el, all = false) => {
    el = el.trim();
    if (all) return Array.from(document.querySelectorAll(el));
    return document.querySelector(el);
  };

  const on = (type, el, listener, all = false) => {
    const target = select(el, all);
    if (!target) return;
    if (all) target.forEach((t) => t.addEventListener(type, listener));
    else target.addEventListener(type, listener);
  };

  const onscroll = (el, listener) => el.addEventListener("scroll", listener);

  const initPreloader = () => {
    const preloader = select("#preloader");
    if (!preloader) return;
    window.addEventListener("load", () => {
      preloader.remove();
    });
  };

  const initScrollTop = () => {
    const scrollTop = select("#scroll-top");
    if (!scrollTop) return;

    const toggle = () => {
      scrollTop.classList.toggle("active", window.scrollY > 100);
    };

    window.addEventListener("load", toggle);
    onscroll(document, toggle);

    on("click", "#scroll-top", (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  };

  const initMobileNav = () => {
    const navmenu = select("#navmenu");
    const toggle = select(".mobile-nav-toggle");
    if (!navmenu || !toggle) return;

    on("click", ".mobile-nav-toggle", () => {
      navmenu.classList.toggle("mobile-nav-active");
      toggle.classList.toggle("bi-list");
      toggle.classList.toggle("bi-x");
    });

    // Close mobile nav when clicking a link
    on(
      "click",
      "#navmenu a",
      () => {
        if (!navmenu.classList.contains("mobile-nav-active")) return;
        navmenu.classList.remove("mobile-nav-active");
        toggle.classList.add("bi-list");
        toggle.classList.remove("bi-x");
      },
      true
    );
  };

  const initAOS = () => {
    if (!window.AOS) return;
    window.addEventListener("load", () => {
      window.AOS.init({
        duration: 600,
        easing: "ease-in-out",
        once: true,
        mirror: false,
      });
    });
  };

  const initGlightbox = () => {
    if (!window.GLightbox) return;
    window.addEventListener("load", () => {
      window.GLightbox({
        selector: ".glightbox",
      });
    });
  };

  const initSwiper = () => {
    if (!window.Swiper) return;
    // Only initialize if a swiper container is present.
    const swiperEls = select(".swiper", true);
    if (!swiperEls.length) return;

    window.addEventListener("load", () => {
      swiperEls.forEach((el) => {
        // eslint-disable-next-line no-new
        new window.Swiper(el, {
          speed: 600,
          loop: true,
          autoplay: { delay: 5000, disableOnInteraction: false },
          pagination: { el: ".swiper-pagination", clickable: true },
          navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        });
      });
    });
  };

  initPreloader();
  initScrollTop();
  initMobileNav();
  initAOS();
  initGlightbox();
  initSwiper();
})();

