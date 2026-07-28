const dateParts = (value) => String(value).slice(0, 10).split('-');

export const formatDate = (value) => {
  if (!value) return '—';
  const [year, month, day] = dateParts(value);
  return `${day}-${month}-${year.slice(-2)}`;
};

export const formatDateTime = (value) => value
  ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false }).format(new Date(value))
  : '—';

export const formatPeriodDate = (value) => {
  if (!value) return '—';
  const date = new Date(value);
  return `${new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short' }).format(date)}, ${date.getFullYear()}`;
};

export const formatPublicDate = (value, year = true) => {
  if (!value) return '—';
  const [dateYear, month, day] = dateParts(value);
  const monthName = new Intl.DateTimeFormat('id-ID', { month: 'short', timeZone: 'UTC' }).format(new Date(`${dateYear}-${month}-${day}T00:00:00Z`)).replace('.', '');
  return [day, monthName, year && dateYear].filter(Boolean).join(' ');
};

export const formatTime = (value) => value ? String(value).slice(0, 5) : '—';
