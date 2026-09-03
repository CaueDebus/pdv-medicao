document.querySelectorAll('.nav-link').forEach((link) => {
  link.addEventListener('click', () => {
    document.querySelectorAll('.nav-link.is-active').forEach((active) => active.classList.remove('is-active'));
    if (!link.classList.contains('is-locked')) {
      link.classList.add('is-active');
    }
  });
});
