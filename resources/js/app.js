import './bootstrap';

const navigationToggle = document.querySelector('[data-nav-toggle]');
const siteNavigation = document.querySelector('#site-navigation');

if (navigationToggle instanceof HTMLButtonElement && siteNavigation instanceof HTMLElement) {
    const closeNavigation = () => {
        navigationToggle.setAttribute('aria-expanded', 'false');
        navigationToggle.setAttribute('aria-label', 'Buka navigasi utama');
        siteNavigation.classList.remove('is-open');
    };

    navigationToggle.addEventListener('click', () => {
        const isOpening = navigationToggle.getAttribute('aria-expanded') !== 'true';

        navigationToggle.setAttribute('aria-expanded', String(isOpening));
        navigationToggle.setAttribute(
            'aria-label',
            isOpening ? 'Tutup navigasi utama' : 'Buka navigasi utama'
        );
        siteNavigation.classList.toggle('is-open', isOpening);
    });

    siteNavigation.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a, button')) {
            closeNavigation();
        }
    });

    document.addEventListener('click', (event) => {
        if (
            event.target instanceof Element
            && !siteNavigation.contains(event.target)
            && !navigationToggle.contains(event.target)
        ) {
            closeNavigation();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && siteNavigation.classList.contains('is-open')) {
            closeNavigation();
            navigationToggle.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 800) {
            closeNavigation();
        }
    });
}

const dashboardGreeting = document.querySelector('[data-dashboard-greeting]');
const dashboardDate = document.querySelector('[data-dashboard-date]');

if (dashboardGreeting instanceof HTMLElement) {
    const hour = new Date().getHours();

    dashboardGreeting.textContent = hour < 11
        ? 'Selamat pagi'
        : hour < 15
            ? 'Selamat siang'
            : hour < 18
                ? 'Selamat sore'
                : 'Selamat malam';
}

if (dashboardDate instanceof HTMLTimeElement) {
    dashboardDate.textContent = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }).format(new Date());
}

document.querySelectorAll('time[data-local-time]').forEach((timeElement) => {
    const deadline = new Date(timeElement.dateTime);

    if (Number.isNaN(deadline.getTime())) {
        return;
    }

    const dateParts = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).formatToParts(deadline);
    const values = {};

    dateParts.forEach(({ type, value }) => {
        values[type] = value;
    });

    timeElement.textContent = `${values.day}-${values.month}-${values.year} ${values.hour}:${values.minute}`;
});

document.querySelectorAll('[data-slide-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-slide-track]');

    if (!(track instanceof HTMLElement)) {
        return;
    }

    carousel.querySelector('[data-slide-prev]')?.addEventListener('click', () => {
        track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' });
    });

    carousel.querySelector('[data-slide-next]')?.addEventListener('click', () => {
        track.scrollBy({ left: track.clientWidth, behavior: 'smooth' });
    });
});

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const backLink = event.target.closest('a[data-history-back]');

    if (!(backLink instanceof HTMLAnchorElement) || !document.referrer) {
        return;
    }

    const previousPage = new URL(document.referrer);

    if (previousPage.origin === window.location.origin) {
        event.preventDefault();
        window.history.back();
    }
});
