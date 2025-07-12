// Vertical tabs JS for Meteo admin panel

document.addEventListener('DOMContentLoaded', function() {
  const navButtons = document.querySelectorAll('.meteo-tabs-nav button');
  const tabPanels = document.querySelectorAll('.meteo-tabs-content > div');
  navButtons.forEach((btn, idx) => {
    btn.addEventListener('click', function() {
      navButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      tabPanels.forEach((panel, i) => {
        panel.style.display = (i === idx) ? 'block' : 'none';
      });
    });
  });
  // Show first tab by default
  if (navButtons.length) navButtons[0].click();
});
