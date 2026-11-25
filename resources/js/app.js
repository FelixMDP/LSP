import './bootstrap';

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
