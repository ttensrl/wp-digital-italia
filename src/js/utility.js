(function () {
    const links  = document.querySelectorAll('#page-index .nav-link');
    const ids    = [...links].map(l => l.getAttribute('href').replace('#', ''));
    const sections = ids.map(id => document.getElementById(id)).filter(Boolean);

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                links.forEach(l => l.classList.remove('active'));
                const active = [...links].find(l => l.getAttribute('href') === '#' + entry.target.id);
                if (active) active.classList.add('active');
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px' });

    sections.forEach(s => observer.observe(s));
})();